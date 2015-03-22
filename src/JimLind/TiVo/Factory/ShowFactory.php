<?php

namespace JimLind\TiVo\Factory;

use JimLind\TiVo\Model\Show;
use JimLind\TiVo\Utilities;

/**
 * Build a show model.
 */
class ShowFactory
{
    /**
     * @var JimLind\TiVo\Model\Show
     */
    protected $show = null;

    /**
     * Constructor
     *
     * @param JimLind\TiVo\Model\Show $show An empty show model to be filled in.
     */
    public function __construct(Show $show)
    {
        $this->show = $show;
    }

    /**
     * Create a list of shows from a list of XML Elements.
     *
     * @param SimpleXMLElement[] $xmlList XML Element from the TiVo.
     *
     * @return JimLind\TiVo\Model\Show[]
     */
    public function createFromXmlList($xmlList)
    {
        $showList = array();
        foreach ($xmlList as $xmlElement) {
            $showList[] = $this->createFromXml($xmlElement);
        }
        // Array of created shows.
        return $showList;
    }

    /**
     * Create a show from an XML Element.
     *
     * @param SimpleXMLElement $xml XML Element from the TiVo.
     *
     * @return JimLind\TiVo\Model\Show
     */
    public function createFromXml($xml)
    {
        $this->show = clone $this->show;
        Utilities\XmlNamespace::addTiVoNamespace($xml);

        $detailList = $xml->xpath('tivo:Details');
        $urlList = $xml->xpath('tivo:Links/tivo:Content/tivo:Url');

        $detailXml = array_pop($detailList);
        $urlString = (string) array_pop($urlList);

        return $this->populateWithXMLPieces($detailXml, $urlString);
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
        Utilities\XmlNamespace::addTiVoNamespace($detailXML);

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
        $this->show->setDate(new \DateTime('@' . $timestamp));

        return $this->show;
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
}