<?php

namespace JimLind\TiVo\Model;

/**
 * Model of Show that's stored on the TiVo
 */
class Show
{

    /**
     * @var integer
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $showTitle = null;

    /**
     * @var string
     */
    protected $episodeTitle = null;

    /**
     * @var integer
     */
    protected $episodeNumber = null;

    /**
     * @var integer
     */
    protected $duration = null;

    /**
     * @var DateTime
     */
    protected $date = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @var integer
     */
    protected $channel = null;

    /**
     * @var string
     */
    protected $station = null;

    /**
     * @var boolean
     */
    protected $hd = null;

    /**
     * @var string
     */
    protected $url = null;


    /**
     * Get Id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = intval($id);
    }

    /**
     * Get Show Title
     *
     * @return string
     */
    public function getShowTitle()
    {
        return $this->showTitle;
    }

    /**
     * Set Show Title
     *
     * @param string $showTitle
     */
    public function setShowTitle($showTitle)
    {
        $this->showTitle = (string) $showTitle;
    }

    /**
     * Get Episode Number
     *
     * @return integer
     */
    public function getEpisodeNumber()
    {
        return $this->episodeNumber;
    }

    /**
     * Set Episode Number
     *
     * @param integer $episodeNumber
     */
    public function setEpisodeNumber($episodeNumber)
    {
        $this->episodeNumber = intval($episodeNumber);
    }

    /**
     * Get Episode Title
     *
     * @return string
     */
    public function getEpisodeTitle()
    {
        return $this->episodeTitle;
    }

    /**
     * Set Episode Title
     *
     * @param string $episodeTitle
     */
    public function setEpisodeTitle($episodeTitle)
    {
        $this->episodeTitle = $episodeTitle;
    }

    /**
     * Get Duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set Duration
     *
     * @param integer $duration
     */
    public function setDuration($duration)
    {
        $this->duration = intval($duration);
    }

    /**
     * Get Date
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set Date
     *
     * @param DateTime $date
     */
    public function setDate($date)
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }
        $this->date = $date;
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set Description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get Channel
     *
     * @return integer
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set Channel
     *
     * @param integer $channel
     */
    public function setChannel($channel)
    {
        $this->channel = intval($channel);
    }

    /**
     * Get Station
     *
     * @return string
     */
    public function getStation()
    {
        return $this->station;
    }

    /**
     * Set Station
     *
     * @param string $station
     */
    public function setStation($station)
    {
        $this->station = (string) $station;
    }

    /**
     * Get HD
     *
     * @return boolean
     */
    public function getHD()
    {
        return $this->hd;
    }

    /**
     * Set HD
     *
     * @param boolean $hd
     */
    public function setHD($hd)
    {
        $this->hd = (bool) $hd;
    }

    /**
     * Get URL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Set URL
     *
     * @param string $url
     */
    public function setURL($url)
    {
        $this->url = (string) $url;
    }

}