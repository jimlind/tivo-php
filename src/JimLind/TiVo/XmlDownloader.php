<?php

namespace JimLind\TiVo;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use JimLind\TiVo\Utilities\XmlNamespace;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Service for downloading list of shows on a TiVo.
 */
class XmlDownloader
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $mak;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param string          $ip     The IP for the TiVo
     * @param string          $mak    The MAK for the TiVo
     * @param ClientInterface $guzzle A Guzzle Client
     */
    public function __construct($ip, $mak, ClientInterface $guzzle)
    {
        $this->url    = 'https://'.$ip.'/TiVoConnect';
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
     * Returns multiple XML file downloads merged into one array.
     *
     * @param SimpleXMLElement[] $previousShowList Array of previous shows
     *
     * @return SimpleXMLElement[]
     */
    public function download($previousShowList = array())
    {
        $xmlFile = $this->downloadXmlPiece(count($previousShowList));
        XmlNamespace::addTiVoNamespace($xmlFile);

        $showList = $xmlFile->xpath('//tivo:Item');
        if (count($showList) > 0) {
            // Recurse on next set of shows.
            return $this->download(array_merge($previousShowList, $showList));
        } else {
            // Last set of shows reached.
            return $previousShowList;
        }
    }

    /**
     * Downloads a single piece of list as SimpleXML
     *
     * @param integer $anchorOffset Count of previous shows
     *
     * @return GuzzleHttp\Message\Response
     */
    private function downloadXmlPiece($anchorOffset)
    {
        try {
            $response = $this->guzzle->get(
                $this->url,
                $this->buildGuzzleOptions($anchorOffset)
            );
        } catch (TransferException $exception) {
            $this->logger->warning($exception->getMessage());

            return new \SimpleXMLElement('<xml />');
        }

        return $this->parseXmlFromResponse($response);
    }

    /**
     * Create an option array for Guzzle.
     *
     * @param integer $offset
     * @return mixed[][]
     */
    private function buildGuzzleOptions($offset)
    {
        return array(
            'auth'  =>  ['tivo', $this->mak, 'digest'],
            'query' => array(
                'Command'      => 'QueryContainer',
                'Container'    => '/NowPlaying',
                'Recurse'      => 'Yes',
                'AnchorOffset' => $offset,
            ),
            'verify' => false,
        );
    }

    /**
     * Parse XML from the Guzzle Response
     *
     * @param mixed $response
     *
     * @return \SimpleXMLElement
     */
    private function parseXmlFromResponse($response)
    {
        if (false === is_a($response, 'GuzzleHttp\Psr7\Response')){
            $this->logger->warning('Empty response from Guzzle.');

            return new \SimpleXMLElement('<xml />');
        }

        set_error_handler([$this, 'throwException']);

        try {
            $responseBody = $response->getBody();

            return new \SimpleXMLElement($responseBody);
        } catch (\Exception $exception) {
            $this->logger->warning('Not an XML response from Guzzle.');
            $this->logger->warning($exception->getMessage());

            return new \SimpleXMLElement('<xml />');
        }

        restore_error_handler();
    }

    /**
     * Upgrade an error to an Exception for easy catching.
     *
     * @param string $code
     * @param int $message
     * @throws \Exception
     */
    private function throwException($code, $message)
    {
        throw new \Exception($message, $code);
    }
}
