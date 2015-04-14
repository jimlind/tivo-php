<?php

namespace JimLind\TiVo\Factory;

use JimLind\TiVo\Model\Show;
use JimLind\TiVo\Utilities\XmlNamespace;

/**
 * Default show factory to build a show model.
 */
class ShowFactory
{
    /**
     * @var JimLind\TiVo\Model\Show
     */
    protected $show = null;

    /**
     * Create a show from an XML Element.
     *
     * @param SimpleXMLElement $xml XML Element from the TiVo.
     *
     * @return JimLind\TiVo\Model\Show
     */
    public function createShowFromXml($xml)
    {
        XmlNamespace::addTiVoNamespace($xml);

        $urlList   = $xml->xpath('tivo:Links/tivo:Content/tivo:Url');
        $urlString = (string) array_pop($urlList);

        $this->show = new Show();
        $this->show->setId($this->parseID($urlString));
        $this->show->setURL($urlString);

        $detailList = $xml->xpath('tivo:Details');
        $detailXml  = array_pop($detailList);
        $this->populateWithXMLPieces($detailXml, $urlString);

        return $this->show;
    }

    /**
     * Populate the model with data.
     *
     * @param SimpleXMLElement $detailXML All the particular show data.
     * @param string           $urlString The full string of the TiVo show URL.
     *
     * @return \JimLind\TiVo\Model\Show
     */
    protected function populateWithXMLPieces($detailXML, $urlString)
    {
        XmlNamespace::addTiVoNamespace($detailXML);

        $this->show->setShowTitle($this->popXPath($detailXML, 'Title'));
        $this->show->setEpisodeTitle($this->popXPath($detailXML, 'EpisodeTitle'));
        $this->show->setEpisodeNumber($this->popXPath($detailXML, 'EpisodeNumber'));
        $this->show->setDuration($this->popXPath($detailXML, 'Duration'));
        $this->show->setDescription($this->popXPath($detailXML, 'Description'));
        $this->show->setChannel($this->popXPath($detailXML, 'SourceChannel'));
        $this->show->setStation($this->popXPath($detailXML, 'SourceStation'));
        $this->show->setHD(strtoupper($this->popXPath($detailXML, 'HighDefinition')) == 'YES');

        $timestamp = hexdec($this->popXPath($detailXML, 'CaptureDate'));
        $this->show->setDate(new \DateTime('@' . $timestamp));
    }

    /**
     * Parses an ID from a download string.
     *
     * @param string $urlString A full URL with parameters.
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

    /**
     * Return a string represented from the XPath.
     *
     * @param SimpleXMLElement $xml  The XML element that hopefully contains the XPath.
     * @param string           $path The XPath string to parse the XML with.
     *
     * @return string
     */
    protected function popXPath($xml, $path)
    {
        $pathList = $xml->xpath('tivo:' . $path);
        if (count($pathList) == 1) {
            return (string) array_pop($pathList);
        }

        return '';
    }
}