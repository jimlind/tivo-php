<?php

namespace JimLind\TiVo\Tests;

use JimLind\TiVo;

/**
 * Test the TiVo\Location service.
 */
class LocationTest extends \PHPUnit_Framework_TestCase
{

    private $logger;
    private $process;
    private $fixture;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $this->process = $this->getMockBuilder('\Symfony\Component\Process\Process')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->logger = $this->getMock('\Psr\Log\LoggerInterface');

        $this->fixture = new TiVo\Location($this->process);
    }

    /**
     * Test Symfony/Process command setup.
     */
    public function testProcessSettingCommand()
    {
        $this->process->expects($this->once())
                      ->method('setCommandLine')
                      ->with('avahi-browse -l -r -t _tivo-videos._tcp');

        $this->fixture->find();
    }

    /**
     * Test Symfony/Process timeout setup.
     */
    public function testProcessSettingTimeout()
    {
        $this->process->expects($this->once())
                      ->method('setTimeout')
                      ->with(60);

        $this->fixture->find();
    }

    /**
     * Test Symfony/Process run method.
     */
    public function testProcessRun()
    {
        $this->process->expects($this->once())
                      ->method('run');

        $this->fixture->find();
    }

    /**
     * Test commands are run in the preferred order.
     */
    public function testProcessState()
    {
        $this->process->expects($this->at(0))->method('setCommandLine');
        $this->process->expects($this->at(1))->method('setTimeout');
        $this->process->expects($this->at(2))->method('run');

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
        $this->process->method('isSuccessful')->willReturn(false);
        $this->process->expects($this->never())->method('getOutput');

        $this->fixture->setLogger($this->logger);
        $this->logger->expects($this->at(0))
                     ->method('warning')
                     ->with('Problem executing avahi-browse. Tool may not be installed.');

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

        $this->fixture->setLogger($this->logger);
        $this->logger->expects($this->at(0))
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

        $this->fixture->setLogger($this->logger);

        if ($info) {
            $this->logger->expects($this->once())
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
