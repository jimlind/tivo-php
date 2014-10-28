<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as GuzzleClient;

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
     * @param string                  $mak    Your TiVo's Media Access Key.
     * @param GuzzleHttp\Client       $guzzle Any Guzzle Client.
     * @param Psr\Log\LoggerInterface $logger Any PSR-0 Logger.
     */
    public function __construct($mak, GuzzleClient $guzzle, LoggerInterface $logger = null)
    {
        $this->mak    = $mak;
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    /**
     * Store the video file to the local file system.
     *
     * @param string $urlPath
     * @param string $filePath
     */
    public function store($urlPath, $filePath)
    {
        $this->escapePath($urlPath);
        $this->touchSecureServer($urlPath);

        $options = array(
            'auth'    => ['tivo', $this->mak, 'digest'],
            'verify'  => false,
            'cookies' => ['sid' => 'SESSIONID'],
            'save_to' => $filePath,
        );

        $this->guzzle->get($urlPath, $options);
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

        $this->guzzle->get($url, $options);
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