<?php

namespace JimLind\TiVo;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;

/**
 * Service for downloading video files from a TiVo
 */
class VideoDownloader extends AbstractBase
{

    /**
     * @var string
     */
    protected $mak;

    /**
     * @var ClientInterface
     */
    protected $guzzle;

    /**
     * @param string          $mak    Your TiVo's Media Access Key
     * @param ClientInterface $guzzle A Guzzle Client
     */
    public function __construct($mak, ClientInterface $guzzle)
    {
        $this->mak    = $mak;
        $this->guzzle = $guzzle;

        parent::__construct();
    }

    /**
     * Download a video file from a TiVo or log failure
     *
     * @param string $url      Where the remote file is
     * @param string $filePath Where the downloaded file goes
     */
    public function download($url, $filePath)
    {
        try {
            $this->downloadWithTimeout($url, $filePath);
        } catch (\Exception $exception) {
            // Something went wrong with Guzzle
            $this->logger->warning('Unable to download a video file.');
            $this->logger->warning($exception->getMessage());
        }
    }

    /**
     * Download a preview of a video file from a TiVo
     *
     * The download action is halted with a timeout
     * Timeout is logged as `info` and an actual error is looged as `warning`
     *
     * @param string $url      Where the remote file is
     * @param string $filePath Where the downloaded file goes
     */
    public function downloadPreview($url, $filePath)
    {
        try {
            $this->downloadWithTimeout($url, $filePath, 120);
        } catch (RequestException $requestException) {
            // Connection timed out as expected
            $this->logger->info('Intentional timeout caught.');
            $this->logger->info($requestException->getMessage());
        } catch (\Exception $exception) {
            // Something went wrong with Guzzle
            $this->logger->warning('Unable to download a video file preview.');
            $this->logger->warning($exception->getMessage());
        }
    }

    /**
     * Download the remote file to the local system
     *
     * To get a file from a TiVo via HTTP you must first touch the HTTPS interface
     * to authenticate before the actual download can start
     *
     * @param string  $url      Where the remote file is
     * @param string  $filePath Where the downloaded file goes
     * @param integer $timeout  Timeout download (Default 0, never)
     */
    protected function downloadWithTimeout($url, $filePath, $timeout = 0)
    {
        $cookieJar     = $this->touchSecureServer($url);
        $authorization = ['tivo', $this->mak, 'digest'];

        $options = [
            'auth'    => $authorization,
            'verify'  => false,
            'cookies' => $cookieJar,
            'save_to' => $filePath,
            'timeout' => $timeout,
        ];

        $this->guzzle->request(
            'GET',
            $this->escapeURL($url),
            $options
        );
    }

    /**
     * Escape the URL for downloading
     *
     * TiVo and Guzzle have differing opinions on how the exclamation point should be handled
     *
     * @param string $url Where the remote file is
     *
     * @return string
     */
    protected function escapeURL($url)
    {
        return str_replace('!', '\!', $url);
    }

    /**
     * Touch the TiVo via HTTPS to start Cookie storage
     *
     * @param string $url Where the remote file is
     *
     * @return CookieJar
     */
    protected function touchSecureServer($url)
    {
        $cookieJar     = new CookieJar();
        $authorization = ['tivo', $this->mak, 'digest'];

        $httpsURL = 'https://'.$this->parseIpFromFileURL($url);
        $options  = [
            'auth'    => $authorization,
            'verify'  => false,
            'cookies' => $cookieJar,
        ];

        try {
            $this->guzzle->request('GET', $httpsURL, $options);
        } catch (\Exception $exception) {
            // Something went wrong with Guzzle.
            $this->logger->warning('Unable to access the TiVo via HTTPS');
            $this->logger->warning($exception->getMessage());
        }

        return $cookieJar;
    }

    /**
     * Parse the IP address from the file URL provided by the TiVo
     *
     * @param string $url Where the remote file is
     *
     * @return string
     */
    protected function parseIpFromFileURL($url)
    {
        $matches = [];
        $pattern = '/http:..(\d+\.\d+\.\d+\.\d+):80/';
        preg_match($pattern, $url, $matches);
        if (empty($matches) && count($matches) < 2) {
            // Failure: Log and exit early
            $message = 'Unable to parse IP from URL.';
            $this->logger->warning($message);
            $this->logger->warning('URL: "'.$url.'"');

            return '';
        }

        return $matches[1];
    }
}
