<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Download is a service for fetching video files of a TiVo.
 */
class Download
{
    private $mak;
    private $guzzle;
    private $logger;

    /**
     * Constructor
     *
     * @param string                   $mak    Media Access Key
     * @param GuzzleHttp\Client        $guzzle A Guzzle Client
     * @param \Psr\Log\LoggerInterface $logger A PSR-0 Logger
     */
    public function __construct($mak, GuzzleClient $guzzle, LoggerInterface $logger = null)
    {
        $this->mak    = $mak;
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    /**
     * Store the video file to the local file system
     *
     * @param string $urlPath
     * @param string $filePath
     */
    public function store($urlPath, $filePath)
    {
        $this->escapePath($urlPath);
        $this->touchSecureServer($urlPath);

        $options = array(
            'auth' => ['tivo', $this->mak, 'digest'],
            'verify' => false,
            'cookies' => ['sid' => rand()],
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
     * Just hit the sever via HTTPS.
     *
     * @param string $urlPath
     */
    protected function touchSecureServer($urlPath)
    {
        $url = 'https://' . $this->parseIpAddress($urlPath);
        $options = array(
            'auth' =>  ['tivo', $this->mak, 'digest'],
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
        $pattern = '/http:..(\d+\.\d+\.\d+\.\d+):80/';
        preg_match($pattern, $urlPath, $matches);
        if (!empty($matches) && isset($matches[1])) {

            return $matches[1];
        }

        return '';
    }

}