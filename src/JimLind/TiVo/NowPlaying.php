<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use JimLind\TiVo\Utilities;
use Psr\Log\LoggerInterface;

/**
 * NowPlaying is a service for downloading list of shows on a TiVo.
 */
class NowPlaying
{
    private $url;
    private $mak;
    private $guzzle;
    private $logger;

    /**
     * Constructor
     *
     * @param string                   $ip     The IP for the TiVo
     * @param string                   $mak    The MAK for the TiVo
     * @param GuzzleHttp\Client        $guzzle A Guzzle Client
     * @param \Psr\Log\LoggerInterface $logger A PSR-0 Logger
     */
    public function __construct($ip, $mak, GuzzleClient $guzzle, LoggerInterface $logger = null)
    {
        $this->url    = 'https://' . $ip . '/TiVoConnect';
        $this->mak    = $mak;
        $this->guzzle = $guzzle;
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
            Utilities\Log::warn($exception->getMessage(), $this->logger);
            // Return an empty XML element.
            return new \SimpleXMLElement('<xml />');
        }
    }

}
