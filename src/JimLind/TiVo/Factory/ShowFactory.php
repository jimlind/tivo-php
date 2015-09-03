<?php

namespace JimLind\TiVo\Factory;

use JimLind\TiVo\Model\Show;
use JimLind\TiVo\Extra\XmlTrait;

/**
 * Default show factory to build a show model.
 */
class ShowFactory
{
    use XmlTrait;

    /**
     * @var JimLind\TiVo\Model\Show
     */
    protected $show = null;

    /**
     * Create a show from an XML Element.
     *
     * @param SimpleXMLElement $xml XML Element from the TiVo.
     *
     * @return Show
     */
    public function createShowFromXml($xml)
    {
        $namespacedXml = $this->addTiVoNamespace($xml);

        $urlList   = $namespacedXml->xpath('tivo:Links/tivo:Content/tivo:Url');
        $urlString = (string) array_pop($urlList);

        $this->show = $this->newShow();
        $this->show->setId($this->parseID($urlString));
        $this->show->setURL($urlString);

        $detailList = $namespacedXml->xpath('tivo:Details');
        $detailXml  = array_pop($detailList);
        $this->populateWithXMLPieces($detailXml, $urlString);

        return $this->show;
    }

    /**
     * Create a new show with an easily replaceable method.
     *
     * @return Show
     */
    protected function newShow()
    {
        return new Show();
    }

    /**
     * Populate the model with data.
     *
     * @param SimpleXMLElement $rawXml    All the particular show data.
     * @param string           $urlString The full string of the TiVo show URL.
     *
     * @return \JimLind\TiVo\Model\Show
     */
    protected function populateWithXMLPieces($rawXml, $urlString)
    {
        $namespacedXml = $this->addTiVoNamespace($rawXml);

        $this->show->setShowTitle($this->popXPath($namespacedXml, 'Title'));
        $this->show->setEpisodeTitle($this->popXPath($namespacedXml, 'EpisodeTitle'));
        $this->show->setEpisodeNumber($this->popXPath($namespacedXml, 'EpisodeNumber'));
        $this->show->setDuration($this->popXPath($namespacedXml, 'Duration'));
        $this->show->setDescription($this->popXPath($namespacedXml, 'Description'));
        $this->show->setChannel($this->popXPath($namespacedXml, 'SourceChannel'));
        $this->show->setStation($this->popXPath($namespacedXml, 'SourceStation'));
        $this->show->setHD(strtoupper($this->popXPath($namespacedXml, 'HighDefinition')) == 'YES');

        $timestamp = hexdec($this->popXPath($namespacedXml, 'CaptureDate'));
        $this->show->setDate(new \DateTime('@'.$timestamp));
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
        $matches = [];
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
        $pathList = $xml->xpath('tivo:'.$path);
        if (count($pathList) == 1) {
            return (string) array_pop($pathList);
        }

        return '';
    }
}
