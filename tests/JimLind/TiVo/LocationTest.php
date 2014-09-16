<?php

namespace JimLind\TiVo;

use JimLind\TiVo;

class LocationTest extends \PHPUnit_Framework_TestCase {

    private $logger;
    private $process;
    private $fixture;

    public function setUp() {
        $this->process = $this->getMockBuilder('\Symfony\Component\Process\Process')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
                             ->disableOriginalConstructor()
                             ->getMock();
        
        $this->fixture = new TiVo\Location(
            $this->process,
            $this->logger
        );
    }

    /**
     * @dataProvider provider
     */
    public function testLocatorFind($return, $expected) {
        $this->process->expects($this->any())
                      ->method('getOutput')
                      ->will($this->returnValue($return));

        // Expect something to be logged if bad output.
        if ($expected === false) {
            $this->logger->expects($this->once())
	                 ->method('warning');
        } else {
            $this->logger->expects($this->exactly(0))
		 ->method('warning');
        }

        $actual = $this->fixture->find();
        $this->assertEquals($expected, $actual);
    }

    public function provider() {
        return array(
            array(
                'return' => null,
                'expected' => false,
            ),
            array(
                'return' => 0,
                'expected' => false,
            ),
            array(
                'return' => '',
                'expected' => false,
            ),
            array(
                'return' => ' address = [192.168.1.187]',
                'expected' => '192.168.1.187',
            ),
            array(
                'return' => ' address = [192.168.1.X]',
                'expected' => false,
            ),
            array(
                'return' => '+ eth0 IPv4 Living Room _tivo-videos._tcp' . PHP_EOL .
                '= eth0 IPv4 Living Room _tivo-videos._tcp' . PHP_EOL .
                ' hostname = [DVR-F449.local]' . PHP_EOL .
                ' address = [192.168.0.42]' . PHP_EOL .
                ' port = [443]' . PHP_EOL .
                ' txt = ["TSN=65200118047F449" "platform=tcd/Series3"]',
                'expected' => '192.168.0.42',
            )
        );
    }

}
