<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * NowPlaying is a service for downloading list of shows on a TiVo.
 */
class NowPlaying
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
     * @param string                  $ip     The IP for the TiVo
     * @param string                  $mak    The MAK for the TiVo
     * @param GuzzleHttp\Client       $guzzle A Guzzle Client
     */
    public function __construct($ip, $mak, GuzzleClient $guzzle)
    {
        $this->url    = 'https://' . $ip . '/TiVoConnect';
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
    public function setLogger(LoggerInterface $logger) {
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
        $xmlFile = $this->downloadXmlFile(count($previousShowList));
        Utilities\XmlNamespace::addTiVoNamespace($xmlFile);

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
     * Downloads a single file as SimpleXML
     *
     * @param integer $anchorOffset Count of previous shows
     *
     * @return GuzzleHttp\Message\Response
     */
    private function downloadXmlFile($anchorOffset)
    {
        $options = array(
            'auth'  =>  ['tivo', $this->mak, 'digest'],
            'query' => array(
                'Command'      => 'QueryContainer',
                'Container'    => '/NowPlaying',
                'Recurse'      => 'Yes',
                'AnchorOffset' => $anchorOffset,
            ),
            'verify' => false,
        );

        try {
            $response = $this->guzzle->get($this->url, $options);
            // Return response as XML.
            return $response->xml();
        } catch (TransferException $exception) {
            $message = $exception->getMessage();
            $this->logger->emergency($message);
            // Return an empty XML element.
            return new \SimpleXMLElement('<xml />');
        }
    }

}
