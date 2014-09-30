<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

class NowPlaying {

    private $url;
    private $mak;
    private $guzzle;
    private $logger;

    function __construct($ip, $mak, GuzzleClient $guzzle, LoggerInterface $logger) {
        $this->url = 'https://' . $ip . '/TiVoConnect';
        $this->mak = $mak;
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    /**
     * 
     * @return SimpleXMLElement[]
     */
    public function download() {
        $xmlFile = $this->downloadXmlFile();
        $showList = $this->xmlFileToArray($xmlFile);

        while ($xmlFile) {
            $anchorOffset = count($showList);
            $xmlFile = $this->downloadXmlFile($anchorOffset);
            if ($xmlFile) {
                $showList = array_merge($showList, $this->xmlFileToArray($xmlFile));
            }
        }

        return $showList;
    }

    private function downloadXmlFile($anchorOffset = 0) {
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

        $response = $this->guzzle->get($this->url, $options);
        $xml = $response->xml();
        
        if (!is_object($xml)) {
            return false;
        }
        if (!isset($xml->ItemCount)) {
            return false;
        }
        $itemCount = (int) $xml->ItemCount;
        if ($itemCount == 0) {
            return false;
        } else {
            return $xml;
        }
    }

    private function xmlFileToArray($simpleXml) {
        $shows = array();
        if (!isset($simpleXml->Item)) {
            return $shows;
        }
        foreach ($simpleXml->Item as $show) {
            $shows[] = $show;
        }
        return $shows;
    }

}
