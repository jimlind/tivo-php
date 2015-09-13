<?php

namespace JimLind\TiVo\Tests\Factory;

use DateTime;
use JimLind\TiVo\Factory\ShowFactory;
use PHPUnit_Framework_TestCase;

/**
 * Test the factory for show models.
 */
class ShowFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $fixture = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setup()
    {
        $this->fixture = new ShowFactory();
    }

    /**
     * Test parsing Show ID.
     */
    public function testShowId()
    {
        $actual     = rand();
        $xmlString  = '<xml><Links><Content><Url>url?id='.$actual.'</Url></Content></Links><Details /></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getId(), $actual);
    }

    /**
     * Test parsing Show URL.
     */
    public function testShowUrl()
    {
        $actual     = rand();
        $xmlString  = '<xml><Links><Content><Url>'.$actual.'</Url></Content></Links><Details /></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getUrl(), $actual);
    }

    /**
     * Test parsing Show Title.
     */
    public function testShowTitle()
    {
        $actual     = rand();
        $xmlString  = '<xml><Details><Title>'.$actual.'</Title></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getShowTitle(), $actual);
    }

    /**
     * Test parsing Episode Title.
     */
    public function testEpisodeTitle()
    {
        $actual     = rand();
        $xmlString  = '<xml><Details><EpisodeTitle>'.$actual.'</EpisodeTitle></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getEpisodeTitle(), $actual);
    }

    /**
     * Test parsing Episode Number.
     */
    public function testEpisodeNumber()
    {
        $actual     = rand();
        $xmlString  = '<xml><Details><EpisodeNumber>'.$actual.'</EpisodeNumber></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getEpisodeNumber(), $actual);
    }

    /**
     * Test parsing Show Duration.
     */
    public function testShowDuration()
    {
        $actual     = rand();
        $xmlString  = '<xml><Details><Duration>'.$actual.'</Duration></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getDuration(), $actual);
    }

    /**
     * Test parsing Show Description.
     */
    public function testShowDescription()
    {
        $actual     = rand();
        $xmlString  = '<xml><Details><Description>'.$actual.'</Description></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getDescription(), $actual);
    }

    /**
     * Test parsing Show Channel.
     */
    public function testShowChannel()
    {
        $actual     = rand();
        $xmlString  = '<xml><Details><SourceChannel>'.$actual.'</SourceChannel></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getChannel(), $actual);
    }

    /**
     * Test parsing Show Station.
     */
    public function testShowStation()
    {
        $actual     = rand();
        $xmlString  = '<xml><Details><SourceStation>'.$actual.'</SourceStation></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertEquals($show->getStation(), $actual);
    }

    /**
     * Test parsing positive Show HD status.
     */
    public function testHighDefinitionTrue()
    {
        $xmlString  = '<xml><Details><HighDefinition>yes</HighDefinition></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertTrue($show->getHd());
    }

    /**
     * Test parsing negative Show HD status.
     */
    public function testHighDefinitionFalse()
    {
        $xmlString  = '<xml><Details><HighDefinition>no</HighDefinition></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show = $this->fixture->createShowFromXml($xmlElement);
        $this->assertFalse($show->getHd());
    }

    /**
     * Test parsing Show Date.
     */
    public function testCaptureDate()
    {
        $xmlString  = '<xml><Details><CaptureDate>ffffffff</CaptureDate></Details></xml>';
        $xmlElement = simplexml_load_string($xmlString);

        $show     = $this->fixture->createShowFromXml($xmlElement);
        $expected = new DateTime('2106-02-07 06:28:15 GMT');
        $this->assertEquals($show->getDate(), $expected);
    }

    /**
     * Test that the Factory isn't stateful.
     */
    public function testRepeatCreates()
    {
        $firstTitle  = rand();
        $firstString = '<xml><Details><Title>'.$firstTitle.'</Title></Details></xml>';
        $firstXML    = simplexml_load_string($firstString);
        $firstShow   = $this->fixture->createShowFromXml($firstXML);

        $secondTitle  = rand();
        $secondString = '<xml><Details><Title>'.$secondTitle.'</Title></Details></xml>';
        $secondXML    = simplexml_load_string($secondString);
        $secondShow   = $this->fixture->createShowFromXml($secondXML);

        $this->assertEquals($firstTitle, $firstShow->getShowTitle());
        $this->assertEquals($secondTitle, $secondShow->getShowTitle());
    }
}
