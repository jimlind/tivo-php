<?php

namespace JimLind\TiVo\Tests;

use JimLind\TiVo\VideoDecoder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Test the TiVo\VideoDecoder service.
 */
class VideoDecoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Process
     */
    private $process = null;

    /**
     * @var ProcessBuilder
     */
    private $builder = null;

    /**
     * @var VideoDecoder
     */
    private $fixture = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $this->process = $this->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = $this->getMock('\Symfony\Component\Process\ProcessBuilder');
        $this->builder->method('getProcess')
            ->willReturn($this->process);

        $this->fixture = new VideoDecoder(null, $this->builder);
    }

    /**
     * Test Symfony/ProcessBuilder prefix setup.
     */
    public function testBuilderSettingPrefix()
    {
        $this->builder->expects($this->once())
            ->method('setPrefix')
            ->with('/usr/local/bin/tivodecode');

        $this->fixture->decode(null, null);
    }

    /**
     * Test Symfony/ProcessBuilder arguments setup.
     */
    public function testBuilderSettingArguments()
    {
        $mak    = rand();
        $input  = rand();
        $output = rand();

        $arguments = [
            $input,
            '--mak=' . $mak,
            '--no-verify',
            '--out=' . $output,
        ];

        $this->builder->expects($this->once())
            ->method('setArguments')
            ->with($arguments);

        $this->fixture = new VideoDecoder($mak, $this->builder);
        $this->fixture->decode($input, $output);
    }

    /**
     * Test Symfony/ProcessBuilder timeout setup.
     */
    public function testBuilderSettingTimeout()
    {
        $this->builder->expects($this->once())
            ->method('setTimeout')
            ->with(null);

        $this->fixture->decode(null, null);
    }

    /**
     * Test Symfony/Process run method.
     */
    public function testProcessRun()
    {
        $this->process->expects($this->once())->method('run');

        $this->fixture->decode(null, null);
    }

    /**
     * Test proper behavior if process is successful.
     */
    public function testProccessSuccess()
    {
        $this->process->method('isSuccessful')->willReturn(true);

        $output = $this->fixture->decode(null, null);

        $this->assertTrue($output);
    }

    /**
     * Test proper behavior if process is not successful.
     */
    public function testProcessFailure()
    {
        $command = rand();

        $this->process->method('isSuccessful')->willReturn(false);
        $this->process->method('getCommandLine')->willReturn($command);

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->expects($this->at(0))
            ->method('warning')
            ->with('Problem executing tivodecode. Tool may not be installed.');
        $logger->expects($this->at(1))
            ->method('warning')
            ->with('Command: ' . $command);

        $this->fixture->setLogger($logger);
        $actual = $this->fixture->decode(null, null);

        $this->assertFalse($actual);
    }
}
