<?php

namespace JimLind\TiVo\Tests;

use GuzzleHttp\Exception\TransferException;
use JimLind\TiVo\NowPlaying;

/**
 * Test the TiVo\NowPlaying service.
 */
class NowPlayingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GuzzleHttp\ClientInterface
     */
    private $guzzle;

    /**
     * @var GuzzleHttp\Message\ResponseInterface
     */
    private $response;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $this->guzzle   = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->logger   = $this->getMock('\Psr\Log\LoggerInterface');
        $this->response = $this->getMock('\GuzzleHttp\Message\ResponseInterface');
    }

    /**
     * Test that IP gets passed through to Guzzle.
     */
    public function testIPPathPassThroughOnStore()
    {
        $ip       = rand();
        $expected = 'https://' . $ip . '/TiVoConnect';

        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $fixture = new NowPlaying($ip, null, $this->guzzle);
        $fixture->download();

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[0]->parameters[0];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that MAC gets passed through to Guzzle.
     */
    public function testMacPassThroughOnStore()
    {
        $mac      = rand();
        $expected = ['tivo', $mac, 'digest'];

        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $fixture = new NowPlaying(null, $mac, $this->guzzle);
        $fixture->download();

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[0]->parameters[1]['auth'];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that AnchorOffset increments on successful Guzzle call.
     */
    public function testAnchorOffsetIncrement()
    {
        $xmlElement = new \SimpleXMLElement('<xml><Item /></xml>');
        $this->response->method('xml')
                       ->will($this->returnValue($xmlElement));

        $spy = $this->any();
        $this->guzzle->expects($spy)
                     ->method('get')
                     ->will($this->onConsecutiveCalls($this->response));

        $fixture = new NowPlaying(null, null, $this->guzzle);
        $fixture->download();

        $invocationList = $spy->getInvocations();

        $firstAnchor = $invocationList[0]->parameters[1]['query']['AnchorOffset'];
        $this->assertEquals(0, $firstAnchor);
        $secondAnchor = $invocationList[1]->parameters[1]['query']['AnchorOffset'];
        $this->assertEquals(1, $secondAnchor);
    }

    /**
     * Test what happens when Guzzle only throws an exception.
     */
    public function testNowPlayingException()
    {
        $fixture = new NowPlaying(null, null, $this->guzzle);

        $this->guzzle->method('get')
                     ->will($this->throwException(new TransferException));

        $actual = $fixture->download();
        $this->assertEquals(array(), $actual);
    }

    /**
     * Test the NowPlaying string to SimpleXML parsing.
     *
     * @param string[]           $xmlList  Array of XML strings
     * @param SimpleXMLElement[] $expected Array of XML Elements
     *
     * @dataProvider guzzleReturnParsingProvider
     */
    public function testGuzzleReturnParsing($xmlList, $expected)
    {
        $fixture = new NowPlaying(null, null, $this->guzzle);

        foreach ($xmlList as $index => $xmlString) {
            $simpleXml = simplexml_load_string($xmlString);
            $this->response->expects($this->at($index))
                           ->method('xml')
                           ->will($this->returnValue($simpleXml));

            $this->guzzle->expects($this->at($index))
                         ->method('get')
                         ->will($this->returnValue($this->response));
        }

        $actual = $fixture->download();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for the XML parsing test.
     *
     * @return mixed[]
     */
    public function guzzleReturnParsingProvider()
    {
        return array(
            array(
                'xmlList' => array('<xml />'),
                'expected' => array(),
            ),
            array(
                'xmlList' => array(
                    '<xml><NorseWords>Ragnarok</NorseWords></xml>',
                ),
                'expected' => array(),
            ),
            array(
                'xmlList' => array(
                    '<xml><ItemCount>2</ItemCount><Item /><Item /></xml>',
                    '<xml><ItemCount>1</ItemCount><Item /></xml>',
                    '<xml><ItemCount>0</ItemCount></xml>',
                ),
                'expected' => array(
                    new \SimpleXMLElement('<Item />'),
                    new \SimpleXMLElement('<Item />'),
                    new \SimpleXMLElement('<Item />'),
                ),
            ),
        );
    }
}
