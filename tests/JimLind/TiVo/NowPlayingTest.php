<?php

namespace Tests\JimLind\TiVo;

use JimLind\TiVo;

class NowPlayingTest extends \PHPUnit_Framework_TestCase {

    private $location;
    private $guzzle;
    private $logger;
    private $response;

    public function setUp() {
        $this->location = $this->getMockBuilder('\JimLind\TiVo\Location')
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->guzzle = $this->getMockBuilder('\GuzzleHttp\Client')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
                             ->disableOriginalConstructor()
                             ->getMock();
        
        $this->response = $this->getMockBuilder('\GuzzleHttp\Message\Response')
                               ->disableOriginalConstructor()
                               ->getMock();
    }

    /**
     * @dataProvider nowPlayingDownloadProvider
     */
    public function testNowPlayingDownload($xmlList, $expected) {
        $ip  = rand();
        $mak = rand();
        
        // Constructor
        $nowPlaying = new TiVo\NowPlaying(
            $ip, $mak, $this->guzzle, $this->logger
        );
        
        // Setup Options
        $options = array(
            'auth' =>  ['tivo', $mak, 'digest'],
            'query' => array(
                'Command' => 'QueryContainer',
                'Container' => '/NowPlaying',
                'Recurse' => 'Yes',
                'AnchorOffset' => 0,
            ),
            'verify' => false,
        );

        $count = 0;
        foreach($xmlList as $index => $xml) {
            
            $optionReplace = array('query' => array('AnchorOffset' => $index));
            $optionInput = array_replace_recursive($options, $optionReplace);
            
            $this->guzzle->expects($this->at($count))
                     ->method('get')
                     ->with($this->equalTo('https://' . $ip . '/TiVoConnect'),
                            $this->equalTo($optionInput))
                     ->will($this->returnValue($this->response));
        
            $simpleXml = simplexml_load_string($xml);
            $this->response->expects($this->at($count))
                           ->method('xml')
                           ->will($this->returnValue($simpleXml));
            $count++;
        }
        
        // Download
        $actual = $nowPlaying->download();
        $this->assertEquals($expected, $actual);
    }

    public function nowPlayingDownloadProvider() {
        return array(
            array(
                'xmlList' => array(''),
                'expected' => array(),
            ),
            array(
                'xmlList' => array(
                    '<xml><NorseWords>Ragnarok</NorseWords></xml>',
                ),
                'expected' => array(),
            ),
            array(
                'xmlList' => array(
                    0 => '<xml><ItemCount>2</ItemCount><Item /><Item /></xml>',
                    2 => '<xml><ItemCount>1</ItemCount><Item /></xml>',
                    3 => '<xml><ItemCount>0</ItemCount></xml>',
                ),
                'expected' => array(
                    new \SimpleXMLElement('<Item />'),
                    new \SimpleXMLElement('<Item />'),
                    new \SimpleXMLElement('<Item />'),
                ),
            ),
        );
    }
}
