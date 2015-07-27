<?php

namespace JimLind\TiVo\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use JimLind\TiVo\XmlDownloader;

/**
 * Test the TiVo\XmlDownloader service.
 */
class XmlDownloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var XmlDownloader
     */
    private $fixture = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $clientMethodList = ['get', 'send', 'sendAsync', 'request', 'requestAsync', 'getConfig'];

        $this->guzzle = $this->getMock('\GuzzleHttp\ClientInterface', $clientMethodList);

        $this->fixture = new XmlDownloader(null, null, $this->guzzle);
    }

    /**
     * Test that IP gets passed through to Guzzle.
     */
    public function testIPPathPassThroughOnStore()
    {
        $ip       = rand();
        $expected = 'https://'.$ip.'/TiVoConnect';

        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $this->fixture = new XmlDownloader($ip, null, $this->guzzle);
        $this->fixture->download();

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

        $this->fixture = new XmlDownloader(null, $mac, $this->guzzle);
        $this->fixture->download();

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[0]->parameters[1]['auth'];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that AnchorOffset increments on successful Guzzle call.
     */
    public function testAnchorOffsetIncrement()
    {
        $xmlString = '<xml><Item /></xml>';

        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $response->method('getBody')
            ->will($this->returnValue($xmlString));

        $spy = $this->any();
        $this->guzzle->expects($spy)
            ->method('get')
            ->will($this->onConsecutiveCalls($response));

        $this->fixture->download();

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
        $this->guzzle->method('get')
            ->will($this->throwException(new TransferException()));

        $actual = $this->fixture->download();
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
        foreach ($xmlList as $index => $xmlString) {
            $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
            $response->expects($this->once())
                ->method('getBody')
                ->will($this->returnValue($xmlString));

            $this->guzzle->expects($this->at($index))
                ->method('get')
                ->will($this->returnValue($response));
        }

        $actual = $this->fixture->download();
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
