<?php

namespace JimLind\TiVo;

use GuzzleHttp\Client as Guzzle;
use Psr\Log\LoggerInterface;

class NowPlaying {

    private $url;
    private $mak;
    private $guzzle;
    private $logger;

    function __construct($ip, $mak, Guzzle $guzzle, LoggerInterface $logger) {
        $this->url = 'https://' . $ip . '/TiVoConnect';
        $this->mak = $mak;
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    public function download() {
        $anchorOffset = 0;
        $xmlFile = $this->downloadXmlFile($anchorOffset);
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

    private function downloadXmlFile($anchorOffset) {
        $data = array(
            'Command' => 'QueryContainer',
            'Container' => '/NowPlaying',
            'Recurse' => 'Yes',
            'AnchorOffset' => $anchorOffset,
        );
        $config = array(
            'stream_context' => [
                'ssl' => [
                    'allow_self_signed' => true
                ],
            ]
        );
        
        $req = $this->guzzle->get($this->url, [
            'query' => $data,
            'auth' =>  ['tivo', $this->mak, 'digest'],
            //'config' => $config,
            'verify' => false,
        ]);
        
        //$req->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
        //$req->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
        
        //$response = $req->send();
        
        echo $req->getBody();
        die;
                
        
        $url = 'https://' . $this->ip . '/TiVoConnect?' . http_build_query($data);
        $command = "curl -s '$url' -k --digest -u tivo:" . $this->mak;

        /*
        $this->process->setCommandLine($command);
        $this->process->setTimeout(600); // 10 minutes
        $this->process->run();

        
        
        $out = $this->process->getOutput();
        var_dump($out);
        die;
        
        $xml = simplexml_load_string($this->process->getOutput());
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
         *
         */
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
