<?php

namespace JimLind\TiVo\Tests\Factory;

use JimLind\TiVo\Factory;
use JimLind\TiVo\Model;

/**
 * Test the factory for show models.
 */
class ShowFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $fixture = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setup()
    {
        $this->fixture = new Factory\ShowFactory(new Model\Show());
    }

    /**
     * Test logging with a logger.
     */
    public function testNormal()
    {
        $xml  = simplexml_load_string($this->returnXml());
        $show = $this->fixture->createFromXml($xml);

        $this->assertEquals(1234, $show->getId());
        $this->assertEquals('url?id=1234', $show->getURL());
        $this->assertEquals('Title A01', $show->getShowTitle());
        $this->assertEquals('Episode B02', $show->getEpisodeTitle());
        $this->assertEquals(2345, $show->getEpisodeNumber());
        $this->assertEquals(3456, $show->getDuration());
        $this->assertEquals('Description C03', $show->getDescription());
        $this->assertEquals(4567, $show->getChannel());
        $this->assertEquals('Station D04', $show->getStation());
    }

    /**
     * Creates a nice big XML string.
     *
     * @return string
     */
    public function returnXml()
    {
        $return = '<xml>'
                . '<Links><Content><Url>url?id=1234</Url></Content></Links>'
                . '<Details>'
                . '<Title>Title A01</Title>'
                . '<EpisodeTitle>Episode B02</EpisodeTitle>'
                . '<EpisodeNumber>2345</EpisodeNumber>'
                . '<Duration>3456</Duration>'
                . '<Description>Description C03</Description>'
                . '<SourceChannel>4567</SourceChannel>'
                . '<SourceStation>Station D04</SourceStation>'
                . '</Details>'
                . '</xml>';

        return $return;
    }

    /**
     * Test setting HighDefinition true and false.
     */
    public function testHighDefinition()
    {
        $xmlHd = '<xml>'
               . '<Details>'
               . '<HighDefinition>yes</HighDefinition>'
               . '</Details>'
               . '</xml>';

        $simpleXmlHd  = simplexml_load_string($xmlHd);
        $showHd       = $this->fixture->createFromXml($simpleXmlHd);

        $this->assertTrue($showHd->getHd());

        $xmlSd = '<xml>'
               . '<Details>'
               . '<HighDefinition>no</HighDefinition>'
               . '</Details>'
               . '</xml>';

        $simpleXmlSd  = simplexml_load_string($xmlSd);
        $showSd       = $this->fixture->createFromXml($simpleXmlSd);

        $this->assertFalse($showSd->getHd());
    }

    /**
     * Test setting CaptureDate.
     */
    public function testCaptureDate()
    {
        $xml2K = '<xml>'
               . '<Details>'
               . '<CaptureDate>3a4f1fc0</CaptureDate>'
               . '</Details>'
               . '</xml>';

        $simpleXml2K  = simplexml_load_string($xml2K);
        $show2K       = $this->fixture->createFromXml($simpleXml2K);

        $this->assertEquals(new \DateTime('2000-12-31 12:00:00 GMT'), $show2K->getDate());

        $xmlEpoch = '<xml>'
               . '<Details>'
               . '<CaptureDate>0</CaptureDate>'
               . '</Details>'
               . '</xml>';

        $simpleXmlEpoch  = simplexml_load_string($xmlEpoch);
        $showEpoch       = $this->fixture->createFromXml($simpleXmlEpoch);

        $this->assertEquals(new \DateTime('1970-01-01 00:00:00 GMT'), $showEpoch->getDate());
    }

    /**
     * Test running factory on an XML list.
     */
    public function testList()
    {
        $xmlList = array();
        $xmlList[] = simplexml_load_string('<xml><Details><Title>Title A01</Title></Details></xml>');
        $xmlList[] = simplexml_load_string('<xml><Details><Title>Title B02</Title></Details></xml>');

        $showList = $this->fixture->createFromXmlList($xmlList);
        $this->assertCount(2, $showList);

        $this->assertEquals('Title A01', $showList[0]->getShowTitle());
        $this->assertEquals('Title B02', $showList[1]->getShowTitle());
    }

}
