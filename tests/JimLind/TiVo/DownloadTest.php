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
     * @var GuzzleHttp\Exception\RequestException
     */
    private $exception = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setUp()
    {
        $this->guzzle = $this->getMockBuilder('\GuzzleHttp\Client')
                             ->disableOriginalConstructor()
                             ->getMock();
    }

    /**
     * A single request to store should perform two guzzle requests.
     */
    public function testDoubleGet()
    {
        $fixture = new TiVo\Download(rand(), $this->guzzle);

        $this->guzzle->expects($this->exactly(2))
                     ->method('get');

        $fixture->store(rand(), rand());
    }

    /**
     * Test using Guzzle to touch the TiVo via HTTPS.
     */
    public function testSecureTouch()
    {
        $mak         = rand();
        $fixture     = new TiVo\Download($mak, $this->guzzle);
        $fakeIp      = rand() . '.' . rand() . '.' . rand() . '.' . rand();
        $insecureURL = 'http://' . $fakeIp . ':80/test';

        $options = array(
            'auth' =>  ['tivo', $mak, 'digest'],
            'verify' => false,
        );

        $this->guzzle->expects($this->at(0))
                     ->method('get')
                     ->with(
                         $this->equalTo('https://' . $fakeIp),
                         $this->equalTo($options)
                     );

        $fixture->store($insecureURL, rand());
    }

    /**
     * Test using Guzzle to download the file from the TiVo.
     */
    public function testFileDownload()
    {
        $mak         = rand();
        $fixture     = new TiVo\Download($mak, $this->guzzle);
        $fakeIp      = rand() . '.' . rand() . '.' . rand() . '.' . rand();
        $insecureURL = 'http://' . $fakeIp . ':80/test';
        $filePath    = rand();

        $options = array(
            'auth' => ['tivo', $mak, 'digest'],
            'verify' => false,
            'cookies' => ['sid' => 'SESSIONID'],
            'save_to' => $filePath,
            'timeout' => 0,
        );

        $this->guzzle->expects($this->at(1))
                     ->method('get')
                     ->with(
                         $this->equalTo($insecureURL),
                         $this->equalTo($options)
                     );

        $fixture->store($insecureURL, $filePath);
    }

    /**
     * Test using Guzzle to download a piece of a file from the TiVo.
     */
    public function testFilePreviewDownload()
    {
        $mak         = rand();
        $fixture     = new TiVo\Download($mak, $this->guzzle);
        $fakeIp      = rand() . '.' . rand() . '.' . rand() . '.' . rand();
        $insecureURL = 'http://' . $fakeIp . ':80/test';
        $filePath    = rand();

        $options = array(
            'auth' => ['tivo', $mak, 'digest'],
            'verify' => false,
            'cookies' => ['sid' => 'SESSIONID'],
            'save_to' => $filePath,
            'timeout' => 60,
        );

        $mock      = $this->getMock('\GuzzleHttp\Message\RequestInterface');
        $exception = new \GuzzleHttp\Exception\RequestException(rand(), $mock);

        $this->guzzle->expects($this->at(1))
                     ->method('get')
                     ->with(
                         $this->equalTo($insecureURL),
                         $this->equalTo($options)
                     )
                     ->will($this->throwException($exception));

        $fixture->storePreview($insecureURL, $filePath);
    }
}