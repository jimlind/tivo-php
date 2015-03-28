<?php

namespace JimLind\TiVo\Tests;

use JimLind\TiVo;

/**
 * Test the TiVo\Decode service.
 */
class DecodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $mak;

    /**
     * @var Symfony\Component\Process\Process
     */
    private $process;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var JimLind\TiVo\Decode
     */
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
    }


    /**
     * Test Symfony/Process command setup.
     */
    public function testProcessSettingCommand()
    {
        $mak    = rand();
        $input  = rand();
        $output = rand();

        $this->process->expects($this->once())
                      ->method('setCommandLine')
                      ->with('tivodecode ' . $input . ' -m ' . $mak . ' -n -o ' . $output);

        $this->fixture = new TiVo\Decode($mak, $this->process);
        $this->fixture->decode($input, $output);
    }

    /**
     * Test Symfony/Process timeout setup.
     */
    public function testProcessSettingTimeout()
    {
        $this->process->expects($this->once())
                      ->method('setTimeout')
                      ->with(0);

        $this->fixture = new TiVo\Decode(null, $this->process);
        $this->fixture->decode(null, null);
    }

    /**
     * Test Symfony/Process run method.
     */
    public function testProcessRun()
    {
        $this->process->expects($this->once())
                      ->method('run');

        $this->fixture = new TiVo\Decode(null, $this->process);
        $this->fixture->decode(null, null);
    }

    /**
     * Test commands are run in the preferred order.
     */
    public function testProcessState()
    {
        $this->process->expects($this->at(0))->method('setCommandLine');
        $this->process->expects($this->at(1))->method('setTimeout');
        $this->process->expects($this->at(2))->method('run');

        $this->fixture = new TiVo\Decode(null, $this->process);
        $this->fixture->decode(null, null);
    }

    /**
     * Test proper behavior if process is successful.
     */
    public function testProccessSuccess()
    {
        $this->process->method('isSuccessful')->willReturn(true);

        $this->fixture = new TiVo\Decode(null, $this->process);
        $output = $this->fixture->decode(null, null);

        $this->assertTrue($output);
    }

    /**
     * Test proper behavior if process is not successful.
     */
    public function testProcessFailure()
    {
        $this->process->method('isSuccessful')->willReturn(false);

        $this->logger->expects($this->at(0))
                     ->method('warning')
                     ->with('Problem executing tivodecode. Tool may not be installed.');

        $this->fixture = new TiVo\Decode(null, $this->process);
        $this->fixture->setLogger($this->logger);
        $output = $this->fixture->decode(null, null);

        $this->assertFalse($output);
    }
}