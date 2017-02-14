<?php

namespace JimLind\TiVo\Tests;

use JimLind\TiVo\TiVoFinder;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Test the TiVoFinder service
 */
class TiVoFinderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @var ProcessBuilder
     */
    protected $builder;

    /**
     * @var TiVoFinder
     */
    protected $fixture;

    protected function setUp()
    {
        $this->process = $this->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = $this->createMock('\Symfony\Component\Process\ProcessBuilder');
        $this->builder->method('getProcess')
            ->willReturn($this->process);

        $this->fixture = new TiVoFinder($this->builder);
    }

    /**
     * Test ProcessBuilder prefix setup
     */
    public function testBuilderSettingPrefix()
    {
        $this->builder->expects($this->once())
            ->method('setPrefix')
            ->with('avahi-browse');

        $this->fixture->find();
    }

    /**
     * Test ProcessBuilder arguments setup
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
     * Test ProcessBuilder timeout setup
     */
    public function testBuilderSettingTimeout()
    {
        $this->builder->expects($this->once())
            ->method('setTimeout')
            ->with(60);

        $this->fixture->find();
    }

    /**
     * Test Process run method
     */
    public function testProcessRun()
    {
        $this->process->expects($this->once())->method('run');

        $this->fixture->find();
    }

    /**
     * Test proper behavior if Process is successful
     */
    public function testProccessSuccess()
    {
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->expects($this->once())->method('getOutput');

        $this->fixture->find();
    }

    /**
     * Test proper behavior if Process is not successful
     */
    public function testProcessFailure()
    {
        $command = rand();

        $this->process->method('isSuccessful')->willReturn(false);
        $this->process->method('getCommandLine')->willReturn($command);
        $this->process->expects($this->never())->method('getOutput');

        $logger = $this->createMock('\Psr\Log\LoggerInterface');

        $this->fixture->setLogger($logger);
        $logger->expects($this->at(0))
            ->method('warning')
            ->with('Problem executing command');
        $logger->expects($this->at(1))
            ->method('warning')
            ->with('Details: `'.$command.'`');

        $actual = $this->fixture->find();
        $this->assertEquals('', $actual);
    }

    /**
     * Test proper behavior if process is successfully empty
     */
    public function testEmptyProcessSuccess()
    {
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn('');

        $logger = $this->createMock('\Psr\Log\LoggerInterface');

        $this->fixture->setLogger($logger);
        $logger->expects($this->at(0))
            ->method('warning')
            ->with('Unable to locate a TiVo device on the network');

        $actual = $this->fixture->find();
        $this->assertEquals('', $actual);
    }

    /**
     * Test that non-empty return values are properly parsed
     *
     * @param string $return   Simulated output from Avahi
     * @param string $logList  List of strings logged as a warning
     * @param string $expected Expected result from find
     *
     * @dataProvider testParsingProvider
     */
    public function testParsing($return, $logList, $expected)
    {
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn($return);

        $logger = $this->createMock('\Psr\Log\LoggerInterface');

        $this->fixture->setLogger($logger);

        foreach ($logList as $index => $message) {
            $logger->expects($this->at($index))
                ->method('warning')
                ->with($message);
        }

        $actual = $this->fixture->find();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for the parsing test
     *
     * @return mixed[]
     */
    public function testParsingProvider()
    {
        $realResponseLineList = [
            '+ eth0 IPv4 Living Room _tivo-videos._tcp',
            '= eth0 IPv4 Living Room _tivo-videos._tcp',
            ' hostname = [DVR-F449.local]',
            ' address = [192.168.0.42]',
            ' port = [443]',
            ' txt = ["TSN=65200118047F449" "platform=tcd/Series3"]',
        ];

        return [
            [
                'return' => ' ',
                'log' => [
                    'Unable to parse IP',
                    'Input: ` `',
                ],
                'expected' => '',
            ],
            [
                'return' => ' address = [192.168.1.187]',
                'log' => [],
                'expected' => '192.168.1.187',
            ],
            [
                'return' => ' address = [192.168.1.X]',
                'log' => [
                    'Unable to parse IP',
                    'Input: ` address = [192.168.1.X]`',
                ],
                'expected' => '',
            ],
            [
                'return' => implode(PHP_EOL, $realResponseLineList),
                'log' => [],
                'expected' => '192.168.0.42',
            ],
        ];
    }
}
