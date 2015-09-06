<?php

namespace JimLind\TiVo\Factory;

use JimLind\TiVo\Characteristic\XmlTrait;
use JimLind\TiVo\Model\Show;
use SimpleXMLElement;

/**
 * Build a populated show
 */
class ShowFactory
{
    use XmlTrait;

    /**
     * @var Show
     */
    protected $show = null;

    /**
     * Create a show from an XML Element
     *
     * @param SimpleXMLElement $xml XML Element from the TiVo
     *
     * @return Show
     */
    public function createShowFromXml(SimpleXMLElement $xml)
    {
        $namespacedXml = $this->registerTiVoNamespace($xml);
        $showArray     = $this->normalizeShowXml($namespacedXml);
        $emptyShow     = $this->newShow();

        return $this->populateShow($emptyShow, $showArray);
    }

    /**
     * Get all useful show data into an array
     *
     * @param SimpleXMLElement $xml
     *
     * @return mixed[]
     */
    protected function normalizeShowXml(SimpleXMLElement $xml) {
        $urlList       = $xml->xpath('tivo:Links/tivo:Content/tivo:Url');
        $detailXml     = reset($xml->xpath('tivo:Details'));
        $namespacedXml = $this->registerTiVoNamespace($detailXml);

        return [
            'showTitle' => $this->popXPath($namespacedXml, 'Title'),
            'episodeTitle' => $this->popXPath($namespacedXml, 'EpisodeTitle'),
            'episodeNumber' => $this->popXPath($namespacedXml, 'EpisodeNumber'),
            'duration' => $this->popXPath($namespacedXml, 'Duration'),
            'description' => $this->popXPath($namespacedXml, 'Description'),
            'channel' => $this->popXPath($namespacedXml, 'SourceChannel'),
            'station' => $this->popXPath($namespacedXml, 'SourceStation'),
            'hd' => $this->popXPath($namespacedXml, 'HighDefinition'),
            'date' => $this->popXPath($namespacedXml, 'CaptureDate'),
            'url' => (string) reset($urlList),
        ];
    }

    /**
     * Create a new show with an easily replaceable method
     *
     * @return Show
     */
    protected function newShow()
    {
        return new Show();
    }

    /**
     * Populate the model with data
     *
     * @param Show    $show Empty show model
     * @param mixed[] $data Raw show data
     *
     * @return Show
     */
    protected function populateShow(Show $show, $data)
    {
        $hd   = strtoupper($data['hd']) == 'YES';
        $date = new \DateTime('@'.hexdec($data['date']));
        $id   = $this->parseID($data['url']);

        $show->setId($id);
        $show->setShowTitle($data['showTitle']);
        $show->setEpisodeTitle($data['episodeTitle']);
        $show->setEpisodeNumber($data['episodeNumber']);
        $show->setDuration($data['duration']);
        $show->setDate($date);
        $show->setDescription($data['description']);
        $show->setChannel($data['channel']);
        $show->setStation($data['station']);
        $show->setHD($hd);
        $show->setUrl($data['url']);

        return $show;
    }

    /**
     * Parses an ID from a URL string
     *
     * @param string $urlString A full URL with parameters
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
     * Return an XML value from an XPath
     *
     * @param SimpleXMLElement $xml  The XML element that hopefully contains the XPath
     * @param string           $path The XPath string to parse the XML with
     *
     * @return string
     */
    protected function popXPath($xml, $path)
    {
        $namespacedXml = $this->registerTiVoNamespace($xml);
        $pathList      = $namespacedXml->xpath('tivo:'.$path);

        if (count($pathList) == 1) {
            return (string) reset($pathList);
        }

        return '';
    }
}
