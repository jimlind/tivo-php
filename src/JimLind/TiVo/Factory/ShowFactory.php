<?php

namespace JimLind\TiVo\Factory;

use JimLind\TiVo\Model\Show;

class ShowFactory {

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
     * @return \JimLind\TiVo\Model\Show
     */
    public function createFromXML(\SimpleXMLElement $xml)
    {
        $details   = $xml->Details;
        $links     = $xml->Links;
        $detailUrl = (string) $links->TiVoVideoDetails->Url;

        preg_match('/.+?id=([0-9]+)$/', $detailUrl, $matches);
        $id = $matches[1];

        return $this->populateWithXMLPieces($id, $details, $links);
    }

    protected function populateWithXMLPieces($id, $details, $links)
    {
        $timestamp = hexdec($details->CaptureDate);

        $this->show->setId($id);
        $this->show->setShowTitle($details->Title);
        $this->show->setEpisodeTitle($details->EpisodeTitle);
        $this->show->setEpisodeNumber($details->EpisodeNumber);
        $this->show->setDuration($details->Duration);
        $this->show->setDescription($details->Description);
        $this->show->setChannel($details->SourceChannel);
        $this->show->setStation($details->SourceStation);
        $this->show->setHD(strtoupper($details->HighDefinition) == 'YES');
        $this->show->setDate(new \DateTime("@$timestamp"));
        $this->show->setURL($links->Content->Url);

        return $this->show;
    }
}