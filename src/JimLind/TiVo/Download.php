<?php

namespace JimLind\TiVo;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Download is a service for fetching video files off of a TiVo.
 */
class Download
{
    /**
     * @var string
     */
    private $mak;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param string          $mak    Your TiVo's Media Access Key.
     * @param ClientInterface $guzzle Any Guzzle Client.
     */
    public function __construct($mak, ClientInterface $guzzle)
    {
        $this->mak    = $mak;
        $this->guzzle = $guzzle;

        // Default to the NullLogger
        $this->setLogger(new NullLogger());
    }

    /**
     * Set the Logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Download a complete video file.
     *
     * @param string $url
     * @param string $filePath
     */
    public function store($url, $filePath)
    {
        try {
            $this->getFile($url, $filePath);
        } catch (\Exception $exception) {
            // Something went wrong with Guzzle.
            $this->logger->warning('Unable to download a complete video file.');
            $this->logger->warning($exception->getMessage());
        }
    }

    /**
     * Download a partial video file.
     *
     * @param string $url
     * @param string $filePath
     */
    public function storePreview($url, $filePath)
    {
        try {
            $this->getFile($url, $filePath, 60);
        } catch (RequestException $requestException) {
            // Connection timed out as expected.
            $this->logger->info('Intentional timeout caught.');
            $this->logger->info($requestException->getMessage());
        } catch (\Exception $exception) {
            // Something went wrong with Guzzle.
            $this->logger->warning('Unable to download a partial video file.');
            $this->logger->warning($exception->getMessage());
        }
    }

    /**
     * Store the video file to the local file system.
     *
     * @param string  $url      Download file from here.
     * @param string  $filePath Download file to here.
     * @param integer $timeout  Timeout download. Default 0 (never).
     *
     */
    protected function getFile($url, $filePath, $timeout = 0)
    {
        $this->touchSecureServer($url);

        $options = array(
            'auth'    => ['tivo', $this->mak, 'digest'],
            'verify'  => false,
            'cookies' => ['sid' => 'SESSIONID'],
            'save_to' => $filePath,
            'timeout' => $timeout,
        );

        $this->guzzle->get(
            $this->escapeURL($url),
            $options
        );
    }

    /**
     * Escape the URL so that it can be properly downloaded.
     *
     * @param string $url
     */
    protected function escapeURL($url)
    {
        return str_replace('!', '\!', $url);
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
            // Something went wrong with Guzzle.
            $this->logger->warning('Unable to access the TiVo via HTTPS');
            $this->logger->warning($exception->getMessage());
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

        if (empty($matches) && count($matches) < 2) {
            // Failure. Log and exit early.
            $message = 'Unable to parse IP from URL.';
            $this->logger->warning($message);

            return '';
        }

        return $matches[1];
    }
}
