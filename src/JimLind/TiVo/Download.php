<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Download is a service for fetching video files of a TiVo.
 */
class Download
{
    /**
     * @var string
     */
    private $mak;

    /**
     * @var GuzzleHttp\Client
     */
    private $guzzle;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param string            $mak    Your TiVo's Media Access Key.
     * @param GuzzleHttp\Client $guzzle Any Guzzle Client.
     */
    public function __construct($mak, GuzzleClient $guzzle)
    {
        $this->mak    = $mak;
        $this->guzzle = $guzzle;

        // Default to the NullLogger
        $this->setLogger(new NullLogger());
    }

    /**
     * Set the Logger
     *
     * @param Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Store the video file to the local file system.
     *
     * @param string  $urlPath  Download file from here.
     * @param string  $filePath Download file to here.
     * @param integer $timeout  Timeout download. Default 0 (never).
     */
    public function store($urlPath, $filePath, $timeout = 0)
    {
        $this->escapePath($urlPath);
        $this->touchSecureServer($urlPath);

        $options = array(
            'auth'    => ['tivo', $this->mak, 'digest'],
            'verify'  => false,
            'cookies' => ['sid' => 'SESSIONID'],
            'save_to' => $filePath,
            'timeout' => $timeout,
        );

        $this->guzzle->get($urlPath, $options);
    }

    /**
     * Store a quick piece of a file to the local file system.
     *
     * Timeout to only grab a piece of the full file.
     *
     * @param string $urlPath
     * @param string $filePath
     */
    public function storePreview($urlPath, $filePath)
    {
        $timeout = 60; // 60 seconds
        try {
            $this->store($urlPath, $filePath, $timeout);
        } catch (RequestException $requestException) {
            // Connection timed out as expected.
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->logger->emergency($message);
        }
    }

    /**
     * Escape the URL so that it can be properly downloaded.
     *
     * @param string $url
     */
    protected function escapePath(&$url)
    {
        $url = str_replace('!', '\!', $url);
    }

    /**
     * Touch the server via HTTPS.
     *
     * @param string $urlPath
     */
    protected function touchSecureServer($urlPath)
    {
        $url     = 'https://' . $this->parseIpAddress($urlPath);
        $options = array(
            'auth'   =>  ['tivo', $this->mak, 'digest'],
            'verify' => false,
        );

        try {
            $this->guzzle->get($url, $options);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->logger->emergency($message);
        }
    }

    /**
     * Parse the IP address from the full URL.
     *
     * @param string $urlPath
     *
     * @return string
     */
    protected function parseIpAddress($urlPath)
    {
        $matches = array();
        $pattern = '/http:..(\d+\.\d+\.\d+\.\d+):80/';
        preg_match($pattern, $urlPath, $matches);
        if (!empty($matches) && isset($matches[1])) {
            // Successfully parsed.
            return $matches[1];
        }
        // Nothing parsed.
        return '';
    }

}
