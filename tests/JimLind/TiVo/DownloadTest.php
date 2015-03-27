<?php

namespace JimLind\TiVo\Tests;

use JimLind\TiVo;

/**
 * Test the TiVo\Download service.
 */
class DownloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GuzzleHttp\Client
     */
    private $guzzle = null;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $this->guzzle = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->logger = $this->getMock('\Psr\Log\LoggerInterface');
    }

    /**
     * A single call to get a file should perform two guzzle requests.
     */
    public function testDoubleGetOnStorePreview()
    {
        $fixture = new TiVo\Download(null, $this->guzzle);
        $this->guzzle->expects($this->exactly(2))
                     ->method('get');

        $fixture->storePreview(null, null);
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

        $fixture = new TiVo\Download($mac, $this->guzzle);
        $fixture->storePreview(null, null);

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

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->storePreview(null, $filePath);

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

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->storePreview(null, null);

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

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->store(null, null);

        $invocationList = $spy->getInvocations();
        $actual         = $invocationList[1]->parameters[1]['timeout'];
        $this->assertEquals(0, $actual);
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

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->storePreview($input, null);

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
        $this->logger->method('warning')
                     ->with('Unable to parse IP from URL.');

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->setLogger($this->logger);
        $fixture->storePreview(null, null);
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

        $this->logger->expects($this->at(0))
                     ->method('warning')
                     ->with($this->equalTo('Unable to access the TiVo via HTTPS'));
        $this->logger->expects($this->at(1))
                     ->method('warning')
                     ->with($this->equalTo($message));

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->setLogger($this->logger);
        $fixture->storePreview('http://1.1.1.1:80', null);
    }

    /**
     * Test logging expected timeout on storePreview.
     */
    public function testStorePreviewTimeout()
    {
        $message     = rand();
        $mockRequest = $this->getMock('\GuzzleHttp\Message\RequestInterface');
        $exception   = new \GuzzleHttp\Exception\RequestException($message, $mockRequest);

        $this->guzzle->expects($this->at(1))
                     ->method('get')
                     ->will($this->throwException($exception));

        $this->logger->expects($this->at(0))
                     ->method('info')
                     ->with($this->equalTo('Intentional timeout caught.'));
        $this->logger->expects($this->at(1))
                     ->method('info')
                     ->with($this->equalTo($message));

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->setLogger($this->logger);
        $fixture->storePreview('http://0.0.0.0:80', null);
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

        $this->logger->expects($this->at(0))
                     ->method('warning')
                     ->with($this->equalTo('Unable to download a partial video file.'));
        $this->logger->expects($this->at(1))
                     ->method('warning')
                     ->with($this->equalTo($message));

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->setLogger($this->logger);
        $fixture->storePreview('http://0.0.0.0:80', null);
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

        $this->logger->expects($this->at(0))
                     ->method('warning')
                     ->with($this->equalTo('Unable to download a complete video file.'));
        $this->logger->expects($this->at(1))
                     ->method('warning')
                     ->with($this->equalTo($message));

        $fixture = new TiVo\Download(null, $this->guzzle);
        $fixture->setLogger($this->logger);
        $fixture->store('http://0.0.0.0:80', null);
    }
}