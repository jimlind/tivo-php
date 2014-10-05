<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
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
    private $returnList;

    /**
     * Constructor
     *
     * @param string                   $ip     The IP for the TiVo
     * @param string                   $mak    The MAK for the TiVo
     * @param GuzzleHttp\Client        $guzzle A Guzzle Client
     * @param \Psr\Log\LoggerInterface $logger A PSR-0 Logger
     */
    public function __construct($ip, $mak, GuzzleClient $guzzle, LoggerInterface $logger)
    {
        $this->url = 'https://' . $ip . '/TiVoConnect';
        $this->mak = $mak;
        $this->guzzle = $guzzle;
        $this->logger = $logger;
        $this->returnList = array();
    }

    /**
     * Returns multiple XML file downloads merged into one array.
     *
     * @param integer $offset Offset indicates count of previous shows
     *
     * @return SimpleXMLElement[]
     */
    public function download($offset = 0)
    {
        $xmlFile = $this->downloadXmlFile($offset);
        $showList = $this->xmlFileToItemList($xmlFile);
        if (count($showList) > 0) {
            $this->returnList = array_merge($this->returnList, $showList);
            $this->download(count($this->returnList));
        }

        return $this->returnList;
    }

    /**
     * Downloads a single file as SimpleXML
     *
     * @param integer $anchorOffset Offset indicates count of previous shows
     *
     * @return GuzzleHttp\Message\Response
     */
    private function downloadXmlFile($anchorOffset)
    {
        $options = array(
            'auth' =>  ['tivo', $this->mak, 'digest'],
            'query' => array(
                'Command' => 'QueryContainer',
                'Container' => '/NowPlaying',
                'Recurse' => 'Yes',
                'AnchorOffset' => $anchorOffset,
            ),
            'verify' => false,
        );

        try {
            $response = $this->guzzle->get($this->url, $options);

            return $response->xml();
        } catch (TransferException $exception) {
            return new \SimpleXMLElement('<xml />');
        }
    }

    /**
     * Copies 'Item' elements from origin SimpleXML object.
     *
     * @param SimpleXMLElement $simpleXml
     *
     * @return SimpleXMLElement[]
     */
    private function xmlFileToItemList($simpleXml)
    {
        $shows = array();
        foreach ($simpleXml->children() as $child) {
            if ($child->getName() == 'Item') {
                $shows[] = $child;
            }
        }

        return $shows;
    }

}
