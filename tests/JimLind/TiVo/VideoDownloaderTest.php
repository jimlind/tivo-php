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
    protected $guzzle;

    /**
     * @var VideoDownloader
     */
    protected $fixture;

    protected function setUp()
    {
        $this->guzzle = $this->getMock('\\GuzzleHttp\\ClientInterface');

        $this->fixture = new VideoDownloader(null, $this->guzzle);
    }

    /**
     * A single call to get a file should perform two guzzle requests
     */
    public function testDoubleRequestOnDownloadPreview()
    {
        $this->guzzle->expects($this->exactly(2))
            ->method('request');

        $this->fixture->downloadPreview(null, null);
    }

    /**
     * Test that MAC gets passed through to Guzzle
     */
    public function testMacPassThroughOnDownload()
    {
        $mac      = rand();
        $expected = ['tivo', $mac, 'digest'];

        $spy = $this->any();
        $this->guzzle->expects($spy)->method('request');

        $this->fixture = new VideoDownloader($mac, $this->guzzle);
        $this->fixture->downloadPreview(null, null);

        $invocationList = $spy->getInvocations();
        foreach ($invocationList as $invocation) {
            $authentication = $invocation->parameters[2]['auth'];
            $this->assertEquals($expected, $authentication);
        }
    }

    /**
     * Test that filePath gets passed through to Guzzle
     */
    public function testFilePathPassThroughOnDownload()
    {
        $filePath = rand();

        $spy = $this->any();
        $this->guzzle->expects($spy)->method('request');

        $this->fixture->downloadPreview(null, $filePath);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[2]['save_to'];
        $this->assertEquals($filePath, $actual);
    }

    /**
     * Test the timeout is passed through to Guzzle
     *
     * @dataProvider testGuzzleTimeoutProvider
     */
    public function testGuzzleTimeout($method, $timeout)
    {
        $spy = $this->any();
        $this->guzzle->expects($spy)->method('request');

        $this->fixture->$method(null, null);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[2]['timeout'];
        $this->assertEquals($timeout, $actual);
    }

    /**
     * Data provider for the timeout test
     *
     * @return mixed[]
     */
    public function testGuzzleTimeoutProvider()
    {
        return [
            ['download', 0],
            ['downloadPreview', 120],
        ];
    }

    /**
     * Test that URL escapes any odd characters for Guzzle
     *
     * @param string $input
     * @param string $expected
     *
     * @dataProvider testEscapingURLProvider
     */
    public function testEscapingURL($input, $expected)
    {
        $spy = $this->any();
        $this->guzzle->expects($spy)->method('request');

        $this->fixture->downloadPreview($input, null);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[1];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for the parsing test
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
     * Test the IP parsing
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
        $this->guzzle->expects($spy)->method('request');

        $this->fixture->downloadPreview($input, null);

        $invocationList = $spy->getInvocations();
        $this->assertEquals($expectedSecure, $invocationList[0]->parameters[1]);
        $this->assertEquals($expectedInsecure, $invocationList[1]->parameters[1]);
    }

    /**
     * Data provider for the parsing test
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
     * Test logged warning on bad data
     */
    public function testLoggedBadParsing()
    {
        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->expects($this->at(0))
            ->method('warning')->with('Unable to parse IP from URL.');
        $logger->expects($this->at(1))
            ->method('warning')->with('URL: ""');

        $this->fixture->setLogger($logger);
        $this->fixture->downloadPreview('', '');
    }

    /**
     * Test catching an exception from the first touch
     */
    public function testSecureTouchException()
    {
        $message   = rand();
        $exception = new \Exception($message);
        $this->guzzle->expects($this->at(0))
            ->method('request')
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
     * Test logging expected timeout on storePreview
     */
    public function testDownloadPreviewTimeout()
    {
        $message     = rand();
        $mockRequest = $this->getMock('\Psr\Http\Message\RequestInterface');
        $exception   = new \GuzzleHttp\Exception\RequestException($message, $mockRequest);

        $this->guzzle->expects($this->at(1))
            ->method('request')
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
     * Test catching a real exception from the preview file download
     */
    public function testPreviewFileDownloadException()
    {
        $this->setUpExceptionTest('Unable to download a video file preview.');
        $this->fixture->downloadPreview('http://0.0.0.0:80', null);
    }

    /**
     * Test catching a real exception from the file download
     */
    public function testFileDownloadException()
    {
        $this->setUpExceptionTest('Unable to download a video file.');
        $this->fixture->download('http://0.0.0.0:80', null);
    }

    /**
     * Setup pieces for testing exception passing
     *
     * @param string $message
     */
    protected function setUpExceptionTest($message)
    {
        $secondWarning = rand();

        $this->guzzle->expects($this->at(1))
            ->method('request')
            ->will($this->throwException(new \Exception($secondWarning)));

        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $logger->expects($this->at(0))
            ->method('warning')
            ->with($this->equalTo($message));
        $logger->expects($this->at(1))
            ->method('warning')
            ->with($this->equalTo($secondWarning));

        $this->fixture->setLogger($logger);
    }
}
