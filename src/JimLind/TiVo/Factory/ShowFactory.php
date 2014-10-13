<?php

namespace JimLind\TiVo\Factory;

use JimLind\TiVo\Model\Show;
use JimLind\TiVo\Utilities;

/**
 * Build a Show Model
 */
class ShowFactory
{

    /**
     * @var \JimLind\TiVo\Model\Show
     */
    protected $show = null;

    /**
     * Constructor
     *
     * @param \JimLind\TiVo\Model\Show $show
     */
    public function __construct(Show $show)
    {
        $this->show = $show;
    }

    /**
     * Create a Show from an XML Element.
     *
     * @param \SimpleXMLElement $xml
     *
     * @return \JimLind\TiVo\Model\Show
     */
    public function createFromXML(\SimpleXMLElement $xml)
    {
        $this->show = clone $this->show;
        Utilities\XmlNameSpace::addTiVoNameSpace($xml);

        $detailList = $xml->xpath('tivo:Details');
        $urlList = $xml->xpath('tivo:Links/tivo:Content/tivo:Url');

        $detailXML = array_pop($detailList);
        $urlString = (string) array_pop($urlList);

        return $this->populateWithXMLPieces($detailXML, $urlString);
    }

    /**
     * Populate the Model with data.
     *
     * @param \SimpleXMLElement $detailXML
     * @param string            $urlString
     *
     * @return \JimLind\TiVo\Model\Show
     */
    protected function populateWithXMLPieces($detailXML, $urlString)
    {
        Utilities\XmlNameSpace::addTiVoNameSpace($detailXML);

        $this->show->setId($this->parseID($urlString));
        $this->show->setShowTitle($this->popXPath($detailXML, 'Title'));
        $this->show->setEpisodeTitle($this->popXPath($detailXML, 'EpisodeTitle'));
        $this->show->setEpisodeNumber($this->popXPath($detailXML, 'EpisodeNumber'));
        $this->show->setDuration($this->popXPath($detailXML, 'Duration'));
        $this->show->setDescription($this->popXPath($detailXML, 'Description'));
        $this->show->setChannel($this->popXPath($detailXML, 'SourceChannel'));
        $this->show->setStation($this->popXPath($detailXML, 'SourceStation'));
        $this->show->setHD(strtoupper($this->popXPath($detailXML, 'HighDefinition')) == 'YES');
        $this->show->setURL($urlString);

        $timestamp = hexdec($this->popXPath($detailXML, 'CaptureDate'));
        $this->show->setDate(new \DateTime("@$timestamp"));

        return $this->show;
    }

    protected function popXPath($xml, $path)
    {
        $pathList = $xml->xpath('tivo:' . $path);
        if (count($pathList) == 1) {
            return (string) array_pop($pathList);
        }

        return '';
    }

    /**
     * Parses an ID from a download string.
     *
     * @param string $urlString
     * 
     * @return integer
     */
    protected function parseID($urlString)
    {
        $matches = array();
        preg_match('/.+?id=([0-9]+)$/', $urlString, $matches);
        if (count($matches) == 2) {
            return (integer) $matches[1];
        }

        return 0;
    }
}