<?php

namespace JimLind\TiVo\Model;

/**
 * Data object for a show
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
     * Get show Id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set show Id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = (integer) ($id);
    }

    /**
     * Get show title
     *
     * @return string
     */
    public function getShowTitle()
    {
        return $this->showTitle;
    }

    /**
     * Set show title
     *
     * @param string $showTitle
     */
    public function setShowTitle($showTitle)
    {
        $this->showTitle = (string) $showTitle;
    }

    /**
     * Get show episode number
     *
     * @return integer
     */
    public function getEpisodeNumber()
    {
        return $this->episodeNumber;
    }

    /**
     * Set show episode number
     *
     * @param integer $episodeNumber
     */
    public function setEpisodeNumber($episodeNumber)
    {
        $this->episodeNumber = (integer) ($episodeNumber);
    }

    /**
     * Get show episode title
     *
     * @return string
     */
    public function getEpisodeTitle()
    {
        return $this->episodeTitle;
    }

    /**
     * Set show episode title
     *
     * @param string $episodeTitle
     */
    public function setEpisodeTitle($episodeTitle)
    {
        $this->episodeTitle = (string) $episodeTitle;
    }

    /**
     * Get show duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set show duration
     *
     * @param integer $duration
     */
    public function setDuration($duration)
    {
        $this->duration = (integer) $duration;
    }

    /**
     * Get show date
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set show date
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
     * Get show description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set show description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
    }

    /**
     * Get show channel
     *
     * @return integer
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set show channel
     *
     * @param integer $channel
     */
    public function setChannel($channel)
    {
        $this->channel = (integer) $channel;
    }

    /**
     * Get show station
     *
     * @return string
     */
    public function getStation()
    {
        return $this->station;
    }

    /**
     * Set show station
     *
     * @param string $station
     */
    public function setStation($station)
    {
        $this->station = (string) $station;
    }

    /**
     * Get show HD status
     *
     * @return boolean
     */
    public function getHd()
    {
        return $this->hd;
    }

    /**
     * Set show HD status
     *
     * @param boolean $hd
     */
    public function setHd($hd)
    {
        $this->hd = (bool) $hd;
    }

    /**
     * Get show URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set show URL
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;
    }
}
