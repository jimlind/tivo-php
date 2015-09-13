<?php

namespace JimLind\TiVo\Tests;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use JimLind\TiVo\XmlDownloader;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

/**
 * Test the XmlDownloader service
 */
class XmlDownloaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface
     */
    protected $guzzle;

    /**
     * @var XmlDownloader
     */
    protected $fixture;

    protected function setUp()
    {
        $this->guzzle = $this->getMock('\GuzzleHttp\ClientInterface');

        $this->fixture = new XmlDownloader(null, null, $this->guzzle);
    }

    /**
     * Test that Guzzle uses correct method
     */
    public function testMethodOnDownload()
    {
        $spy      = $this->any();
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $this->guzzle->expects($spy)
            ->method('send')
            ->willReturn($response);

        $this->fixture->download();

        $invocationList = $spy->getInvocations();
        $request        = $invocationList[0]->parameters[0];

        $this->assertEquals('GET', $request->getMethod());
    }

    /**
     * Test that Guzzle uses correct URI
     */
    public function testUriOnDownload()
    {
        $spy      = $this->any();
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $this->guzzle->expects($spy)
            ->method('send')
            ->willReturn($response);

        $ipAddress = rand();
        $this->fixture = new XmlDownloader($ipAddress, null, $this->guzzle);
        $this->fixture->download();

        $invocationList = $spy->getInvocations();
        $request        = $invocationList[0]->parameters[0];
        $this->assertEquals(
            'https://'.$ipAddress.'/TiVoConnect',
            (string) $request->getUri()
        );
    }

    /**
     * Test that Guzzle uses correct auth options
     */
    public function testAuthOnDownload()
    {
        $spy      = $this->any();
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $this->guzzle->expects($spy)
            ->method('send')
            ->willReturn($response);

        $mak      = rand();
        $expected = ['tivo', $mak, 'digest'];
        $this->fixture = new XmlDownloader(null, $mak, $this->guzzle);
        $this->fixture->download();

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[0]->parameters[1]['auth'];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test Guzzle with bad response exception has empty result
     */
    public function testBadResponseExceptionOnDownload()
    {
        $request  = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');

        $exception = new BadResponseException(uniqid(), $request, $response);

        $this->guzzle->method('send')
            ->will($this->throwException($exception));

        $actual = $this->fixture->download();
        $this->assertEquals([], $actual);
    }

    /**
     * Test Guzzle with bad response exception logged
     */
    public function testBadResponseExceptionLoggedOnDownload()
    {
        $responseBody = rand();
        $responseCode = rand();

        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $response->method('getBody')->willReturn($responseBody);
        $response->method('getStatusCode')->willReturn($responseCode);

        $request   = $this->getMock('\Psr\Http\Message\RequestInterface');
        $exception = new BadResponseException(rand(), $request, $response);

        $this->guzzle->method('send')
            ->will($this->throwException($exception));

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $spy    = $this->any();
        $logger->expects($spy)->method('warning');

        $this->fixture->setLogger($logger);
        $this->fixture->download();

        $invocationList = $spy->getInvocations();
        $firstWarning   = $invocationList[0]->parameters[0];
        $secondWarning  = $invocationList[1]->parameters[0];

        $this->assertEquals('Client response was not a success', $firstWarning);
        $this->assertEquals($responseCode.': `'.$responseBody.'`', $secondWarning);
    }

    /**
     * Test Guzzle with exception has empty result
     */
    public function testExceptionOnDownload()
    {
        $exception = new Exception(uniqid());

        $this->guzzle->method('send')
            ->will($this->throwException($exception));

        $actual = $this->fixture->download();
        $this->assertEquals([], $actual);
    }

    /**
     * Test Guzzle with exception logged
     */
    public function testExceptionMessageLoggedOnDownload()
    {
        $message   = uniqid();
        $exception = new Exception($message);

        $this->guzzle->method('send')
            ->will($this->throwException($exception));

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $spy    = $this->any();
        $logger->expects($spy)->method('warning');

        $this->fixture->setLogger($logger);
        $this->fixture->download();

        $invocationList = $spy->getInvocations();
        $firstWarning   = $invocationList[0]->parameters[0];
        $secondWarning  = $invocationList[1]->parameters[0];

        $this->assertEquals('Client response was not a success', $firstWarning);
        $this->assertEquals('0: `'.$message.'`', $secondWarning);
    }

    /**
     * Test that AnchorOffset increments on successful Guzzle call
     */
    public function testAnchorOffsetIncrement()
    {
        $firstResponse = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $firstResponse->method('getBody')
            ->willReturn('<xml><Item /><Item /></xml>');
        $firstResponse->method('getStatusCode')
            ->willReturn(200);

        $secondResponse = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $secondResponse->method('getBody')
            ->willReturn('<xml />');
        $secondResponse->method('getStatusCode')
            ->willReturn(200);

        $spy = $this->any();
        $this->guzzle->expects($spy)
            ->method('send')
            ->will($this->onConsecutiveCalls($firstResponse, $secondResponse));

        $this->fixture->download();

        $invocationList = $spy->getInvocations();

        $firstAnchor = $invocationList[0]->parameters[1]['query']['AnchorOffset'];
        $this->assertEquals(0, $firstAnchor);
        $secondAnchor = $invocationList[1]->parameters[1]['query']['AnchorOffset'];
        $this->assertEquals(2, $secondAnchor);
    }

    /**
     * Test the NowPlaying string to SimpleXML parsing
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
            $response->method('getBody')
                ->willReturn($xmlString);
            $response->method('getStatusCode')
                ->willReturn(200);

            $this->guzzle->expects($this->at($index))
                ->method('send')
                ->will($this->returnValue($response));
        }

        $actual = $this->fixture->download();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for the XML parsing test
     *
     * @return mixed[]
     */
    public function guzzleReturnParsingProvider()
    {
        return [
            [
                'xmlList' => ['Not Valid XML'],
                'expected' => [],
            ],
            [
                'xmlList' => ['<xml />'],
                'expected' => [],
            ],
            [
                'xmlList' => [
                    '<xml><NorseWords>Ragnarok</NorseWords></xml>',
                ],
                'expected' => [],
            ],
            [
                'xmlList' => [
                    '<xml><ItemCount>2</ItemCount><Item /><Item /></xml>',
                    '<xml><ItemCount>1</ItemCount><Item /></xml>',
                    '<xml><ItemCount>0</ItemCount></xml>',
                ],
                'expected' => [
                    new SimpleXMLElement('<Item />'),
                    new SimpleXMLElement('<Item />'),
                    new SimpleXMLElement('<Item />'),
                ],
            ],
        ];
    }
}
