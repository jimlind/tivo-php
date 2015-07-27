<?php

namespace JimLind\TiVo\Tests;

use GuzzleHttp\ClientInterface;
use JimLind\TiVo\VideoDownloader;

/**
 * Test the TiVo\VideoDownloader service.
 */
class VideoDownloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface
     */
    private $guzzle = null;

    /**
     * @var VideoDownloader
     */
    private $fixture = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $clientMethodList = ['get', 'send', 'sendAsync', 'request', 'requestAsync', 'getConfig'];

        $this->guzzle = $this->getMock('\GuzzleHttp\ClientInterface', $clientMethodList);

        $this->fixture = new VideoDownloader(null, $this->guzzle);
    }

    /**
     * A single call to get a file should perform two guzzle requests.
     */
    public function testDoubleGetOnStorePreview()
    {
        $this->guzzle->expects($this->exactly(2))
            ->method('get');

        $this->fixture->downloadPreview(null, null);
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

        $this->fixture = new VideoDownloader($mac, $this->guzzle);
        $this->fixture->downloadPreview(null, null);

        $invocationList = $spy->getInvocations();
        foreach ($invocationList as $invocation) {
            $authentication = $invocation->parameters[1]['auth'];
            $this->assertEquals($expected, $authentication);
        }
    }

    /**
     * Test that filePath gets passed through to Guzzle.
     */
    public function testFilePathPassThroughOnStore()
    {
        $filePath = rand();

        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $this->fixture->downloadPreview(null, $filePath);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[1]['save_to'];
        $this->assertEquals($filePath, $actual);
    }

    /**
     * Test the timeout for storePreview is passed through to Guzzle.
     */
    public function testStorePreviewGuzzleTimeout()
    {
        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $this->fixture->downloadPreview(null, null);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[1]['timeout'];
        $this->assertEquals(60, $actual);
    }

    /**
     * Test the timeout for store is passed through to Guzzle.
     */
    public function testStoreGuzzleTimeout()
    {
        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $this->fixture->download(null, null);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[1]['timeout'];
        $this->assertEquals(0, $actual);
    }

    /**
     * Test that URL escapes any odd characters for Guzzle.
     *
     * @param string $input
     * @param string $expected
     *
     * @dataProvider testEscapingURLProvider
     */
    public function testEscapingURL($input, $expected)
    {
        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $this->fixture->downloadPreview($input, null);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[0];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for the parsing test.
     *
     * @return mixed[]
     */
    public function testEscapingURLProvider()
    {
        return [
            ['http://1.1.1.1:80/The Beatles - Help!', 'http://1.1.1.1:80/The Beatles - Help\!'],
        ];
    }

    /**
     * Test the IP parsing.
     *
     * @param string $input
     * @param string $expectedSecure
     * @param string $expectedInsecure
     *
     * @dataProvider testParsingProvider
     */
    public function testParsing($input, $expectedSecure, $expectedInsecure)
    {
        $spy = $this->any();
        $this->guzzle->expects($spy)->method('get');

        $this->fixture->downloadPreview($input, null);

        $invocationList = $spy->getInvocations();
        $this->assertEquals($expectedSecure, $invocationList[0]->parameters[0]);
        $this->assertEquals($expectedInsecure, $invocationList[1]->parameters[0]);
    }

    /**
     * Data provider for the parsing test.
     *
     * @return mixed[]
     */
    public function testParsingProvider()
    {
        return [
            [
                'input' => '',
                'secure' => 'https://',
                'insecure' => '',
            ],
            [
                'input' => 'Error Text',
                'secure' => 'https://',
                'insecure' => 'Error Text',
            ],
            [
                'input' => 'http://192.168.1.1:80',
                'secure' => 'https://192.168.1.1',
                'insecure' => 'http://192.168.1.1:80',
            ],
            [
                'input' => 'http://192.168.1.1:80/XYZ',
                'secure' => 'https://192.168.1.1',
                'insecure' => 'http://192.168.1.1:80/XYZ',
            ],
        ];
    }

    /**
     * Test logged warning on bad data.
     */
    public function testLoggedBadParsing()
    {
        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->method('warning')->with('Unable to parse IP from URL.');

        $this->fixture->setLogger($logger);
        $this->fixture->downloadPreview(null, null);
    }

    /**
     * Test catching an exception from the first touch.
     */
    public function testSecureTouchException()
    {
        $message   = rand();
        $exception = new \Exception($message);
        $this->guzzle->expects($this->at(0))
            ->method('get')
            ->will($this->throwException($exception));

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->expects($this->at(0))
            ->method('warning')
            ->with($this->equalTo('Unable to access the TiVo via HTTPS'));
        $logger->expects($this->at(1))
            ->method('warning')
            ->with($this->equalTo($message));

        $this->fixture->setLogger($logger);
        $this->fixture->downloadPreview('http://1.1.1.1:80', null);
    }

    /**
     * Test logging expected timeout on storePreview.
     */
    public function testStorePreviewTimeout()
    {
        $message     = rand();
        $mockRequest = $this->getMock('\Psr\Http\Message\RequestInterface');
        $exception   = new \GuzzleHttp\Exception\RequestException($message, $mockRequest);

        $this->guzzle->expects($this->at(1))
            ->method('get')
            ->will($this->throwException($exception));

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->expects($this->at(0))
            ->method('info')
            ->with($this->equalTo('Intentional timeout caught.'));
        $logger->expects($this->at(1))
            ->method('info')
            ->with($this->equalTo($message));

        $this->fixture->setLogger($logger);
        $this->fixture->downloadPreview('http://0.0.0.0:80', null);
    }

    /**
     * Test catching a real exception from the preview file download.
     */
    public function testPreviewFileDownloadException()
    {
        $message   = rand();

        $this->guzzle->expects($this->at(1))
            ->method('get')
            ->will($this->throwException(new \Exception($message)));

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->expects($this->at(0))
            ->method('warning')
            ->with($this->equalTo('Unable to download a partial video file.'));
        $logger->expects($this->at(1))
            ->method('warning')
            ->with($this->equalTo($message));

        $this->fixture->setLogger($logger);
        $this->fixture->downloadPreview('http://0.0.0.0:80', null);
    }

    /**
     * Test catching a real exception from the file download.
     */
    public function testFileDownloadException()
    {
        $message   = rand();

        $this->guzzle->expects($this->at(1))
            ->method('get')
            ->will($this->throwException(new \Exception($message)));

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->expects($this->at(0))
            ->method('warning')
            ->with($this->equalTo('Unable to download a complete video file.'));
        $logger->expects($this->at(1))
            ->method('warning')
            ->with($this->equalTo($message));

        $this->fixture->setLogger($logger);
        $this->fixture->download('http://0.0.0.0:80', null);
    }
}
