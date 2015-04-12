<?php

namespace JimLind\TiVo\Tests;

use JimLind\TiVo\Decode;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Test the TiVo\Decode service.
 */
class DecodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var ProcessBuilder
     */
    private $builder;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $this->logger  = $this->getMock('\Psr\Log\LoggerInterface');
        $this->process = $this->getMockBuilder('\Symfony\Component\Process\Process')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->builder = $this->getMock('\Symfony\Component\Process\ProcessBuilder');
        $this->builder->method('getProcess')
                      ->willReturn($this->process);
    }

    /**
     * Test Symfony/ProcessBuilder prefix setup.
     */
    public function testBuilderSettingPrefix()
    {
        $this->builder->expects($this->once())
                      ->method('setPrefix')
                      ->with('tivodecode');

        $fixture = new Decode(null, $this->builder);
        $fixture->decode(null, null);
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

        $fixture = new Decode($mak, $this->builder);
        $fixture->decode($input, $output);
    }

    /**
     * Test Symfony/ProcessBuilder timeout setup.
     */
    public function testBuilderSettingTimeout()
    {
        $this->builder->expects($this->once())
                      ->method('setTimeout')
                      ->with(null);

        $fixture = new Decode(null, $this->builder);
        $fixture->decode(null, null);
    }

    /**
     * Test Symfony/Process run method.
     */
    public function testProcessRun()
    {
        $this->process->expects($this->once())
                      ->method('run');

        $fixture = new Decode(null, $this->builder);
        $fixture->decode(null, null);
    }

    /**
     * Test proper behavior if process is successful.
     */
    public function testProccessSuccess()
    {
        $this->process->method('isSuccessful')->willReturn(true);

        $fixture = new Decode(null, $this->builder);
        $output = $fixture->decode(null, null);

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

        $this->logger->expects($this->at(0))
                     ->method('warning')
                     ->with('Problem executing tivodecode. Tool may not be installed.');
        $this->logger->expects($this->at(1))
                     ->method('warning')
                     ->with('Command: ' . $command);

        $fixture = new Decode(null, $this->builder);
        $fixture->setLogger($this->logger);
        $actual = $fixture->decode(null, null);

        $this->assertFalse($actual);
    }
}