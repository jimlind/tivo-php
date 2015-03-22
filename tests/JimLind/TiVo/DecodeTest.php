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
        $this->mak = rand();

        $this->process = $this->getMockBuilder('\Symfony\Component\Process\Process')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->fixture = new TiVo\Decode(
            $this->mak,
            $this->process,
            $this->logger
        );
        $this->fixture->setLogger($this->logger);
    }

    /**
     * Test decode commands running normally.
     */
    public function testNormalDecode()
    {
        $input  = rand();
        $output    = rand();

        $this->process->expects($this->once())
                      ->method('getErrorOutput')
                      ->will($this->returnValue('Copyright (c) 2006-2007, Jeremy Drake'));

        $this->process->expects($this->exactly(2))
                      ->method('run');

        $this->process->expects($this->at(0))
                      ->method('setCommandLine')
                      ->with($this->stringContains('tivodecode --version'));

        $decodeCommand = 'tivodecode '.$input.' -m '.$this->mak.' -n -o '.$output;
        $this->process->expects($this->at(4))
                      ->method('setCommandLine')
                      ->with($this->stringContains($decodeCommand));

        $this->logger->expects($this->never())
                     ->method('emergency');

        $this->fixture->decode($input, $output);
    }

    /**
     * Test proper reaction to TiVo Decoder not working properly.
     */
    public function testNoDecoder()
    {
        $input  = rand();
        $output = rand();

        $this->process->expects($this->once())
                      ->method('getErrorOutput')
                      ->will($this->returnValue(null));

        $this->process->expects($this->exactly(1))
                      ->method('run');

        $this->process->expects($this->exactly(1))
                      ->method('setCommandLine')
                      ->with($this->stringContains('tivodecode --version'));

        $this->logger->expects($this->once())
                     ->method('emergency');

        $this->fixture->decode($input, $output);
    }
}