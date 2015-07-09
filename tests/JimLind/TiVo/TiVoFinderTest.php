<?php

namespace JimLind\TiVo\Tests;

use JimLind\TiVo\TiVoFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Test the TiVo\TiVoFinder service.
 */
class TiVoFinderTest extends \PHPUnit_Framework_TestCase
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
     * @var TiVoFinder
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

        $this->fixture = new TiVoFinder($this->builder);
    }

    /**
     * Test Symfony/ProcessBuilder prefix setup.
     */
    public function testBuilderSettingPrefix()
    {
        $this->builder->expects($this->once())
            ->method('setPrefix')
            ->with('avahi-browse');

        $this->fixture->find();
    }

    /**
     * Test Symfony/ProcessBuilder arguments setup.
     */
    public function testBuilderSettingArguments()
    {
        $arguments = [
            '--ignore-local',
            '--resolve',
            '--terminate',
            '_tivo-videos._tcp',
        ];

        $this->builder->expects($this->once())
            ->method('setArguments')
            ->with($arguments);

        $this->fixture->find();
    }

    /**
     * Test Symfony/ProcessBuilder timeout setup.
     */
    public function testBuilderSettingTimeout()
    {
        $this->builder->expects($this->once())
            ->method('setTimeout')
            ->with(60);

        $this->fixture->find();
    }

    /**
     * Test Symfony/Process run method.
     */
    public function testProcessRun()
    {
        $this->process->expects($this->once())->method('run');

        $this->fixture->find();
    }

    /**
     * Test proper behavior if process is successful.
     */
    public function testProccessSuccess()
    {
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->expects($this->once())->method('getOutput');

        $this->fixture->find();
    }

    /**
     * Test proper behavior if process is not successful.
     */
    public function testProcessFailure()
    {
        $command = rand();

        $this->process->method('isSuccessful')->willReturn(false);
        $this->process->method('getCommandLine')->willReturn($command);
        $this->process->expects($this->never())->method('getOutput');

        $logger = $this->getMock('\Psr\Log\LoggerInterface');

        $this->fixture->setLogger($logger);
        $logger->expects($this->at(0))
            ->method('warning')
            ->with('Problem executing avahi-browse. Tool may not be installed.');
        $logger->expects($this->at(1))
            ->method('warning')
            ->with('Command: ' . $command);

        $actual = $this->fixture->find();
        $this->assertEquals('', $actual);
    }

    /**
     * Test proper behavior if process is successfully empty.
     */
    public function testEmptyProcessSuccess()
    {
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn('');

        $logger = $this->getMock('\Psr\Log\LoggerInterface');

        $this->fixture->setLogger($logger);
        $logger->expects($this->at(0))
            ->method('warning')
            ->with('Unable to locate a TiVo device on the network.');

        $actual = $this->fixture->find();
        $this->assertEquals('', $actual);
    }

    /**
     * Test that non-empty return values are properly parsed.
     *
     * @param string $return   Simulated output from Avahi
     * @param string $info     Logged as info.
     * @param string $expected Expected result from find
     *
     * @dataProvider testParsingProvider
     */
    public function testParsing($return, $info, $expected)
    {
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn($return);

        $logger = $this->getMock('\Psr\Log\LoggerInterface');

        $this->fixture->setLogger($logger);

        if ($info) {
            $logger->expects($this->once())
                ->method('warning')
                ->with($info);
        }

        $actual = $this->fixture->find();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for the parsing test.
     *
     * @return mixed[]
     */
    public function testParsingProvider()
    {
        return array(
            array(
                'return' => ' ',
                'info' => 'Unable to parse IP from Avahi output.',
                'expected' => '',
            ),
            array(
                'return' => ' address = [192.168.1.187]',
                'info' => null,
                'expected' => '192.168.1.187',
            ),
            array(
                'return' => ' address = [192.168.1.X]',
                'info' => 'Unable to parse IP from Avahi output.',
                'expected' => '',
            ),
            array(
                'return' => '+ eth0 IPv4 Living Room _tivo-videos._tcp' . PHP_EOL .
                '= eth0 IPv4 Living Room _tivo-videos._tcp' . PHP_EOL .
                ' hostname = [DVR-F449.local]' . PHP_EOL .
                ' address = [192.168.0.42]' . PHP_EOL .
                ' port = [443]' . PHP_EOL .
                ' txt = ["TSN=65200118047F449" "platform=tcd/Series3"]',
                'info' => null,
                'expected' => '192.168.0.42',
            )
        );
    }
}
