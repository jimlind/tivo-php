<?php

namespace JimLind\TiVo\Tests\Model;

use JimLind\TiVo\Model\Show;

/**
 * Test the Show Model.
 */
class ShowTest extends \PHPUnit_Framework_TestCase
{
    protected $fixture = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setup()
    {
        $this->fixture = new Show();
    }

    /**
     * Test set and get Show Id.
     */
    public function testId()
    {
        $value = rand();
        $this->fixture->setId((string) $value);

        $actual = $this->fixture->getId();
        $this->assertSame($value, $actual, 'Problem getting or setting the Show Id as an integer.');
    }

    /**
     * Test set and get Show Title.
     */
    public function testShowTitle()
    {
        $value = rand();
        $this->fixture->setShowTitle($value);

        $actual = $this->fixture->getShowTitle();
        $this->assertSame((string) $value, $actual, 'Problem getting or setting the Show Title as a string.');
    }

    /**
     * Test set and get Episode Number.
     */
    public function testEpisodeNumber()
    {
        $value = rand();
        $this->fixture->setEpisodeNumber((string) $value);

        $actual = $this->fixture->getEpisodeNumber();
        $this->assertSame($value, $actual, 'Problem getting or setting the Episode Number as an integer.');
    }

    /**
     * Test set and get Episode Title.
     */
    public function testEpisodeTitle()
    {
        $value = rand();
        $this->fixture->setEpisodeTitle($value);

        $actual = $this->fixture->getEpisodeTitle();
        $this->assertSame((string) $value, $actual, 'Problem getting or setting the Episode Title as a string.');
    }

    /**
     * Test set and get Show Duration.
     */
    public function testShowDuration()
    {
        $value = rand();
        $this->fixture->setDuration((string) $value);

        $actual = $this->fixture->getDuration();
        $this->assertSame($value, $actual, 'Problem getting or setting the Show Duration as an integer.');
    }

    /**
     * Test set and get Show Date.
     */
    public function testShowDate()
    {
        $dayObject = new \DateTime(rand(0, 100) . ' days');
        $this->fixture->setDate($dayObject);

        $actual = $this->fixture->getDate();
        $this->assertEquals($dayObject, $actual, 'Problem getting or setting the Show Date as a DateTime object.');
    }

    /**
     * Test set and get Show Date as string.
     */
    public function testShowDateAsString()
    {
        $dayString = rand(0, 100) . ' days';
        $this->fixture->setDate($dayString);

        $expected = new \DateTime($dayString);
        $actual   = $this->fixture->getDate();
        $this->assertEquals($expected, $actual, 'Problem getting or setting the Show Date as a string.');
    }

    /**
     * Test set and get Show Description.
     */
    public function testShowDescription()
    {
        $value = rand();
        $this->fixture->setDescription($value);

        $actual = $this->fixture->getDescription();
        $this->assertSame((string) $value, $actual, 'Problem getting or setting the Show Description as a string.');
    }

    /**
     * Test set and get Show Channel.
     */
    public function testShowChannel()
    {
        $value = rand();
        $this->fixture->setChannel((string) $value);

        $actual = $this->fixture->getChannel();
        $this->assertSame($value, $actual, 'Problem getting or setting the Show Channel as an integer.');
    }

    /**
     * Test set and get Show Station.
     */
    public function testShowStation()
    {
        $value = rand();
        $this->fixture->setStation($value);

        $actual = $this->fixture->getStation();
        $this->assertSame((string) $value, $actual, 'Problem getting or setting the Show Station as a string.');
    }

    /**
     * Test set and get Show HD.
     */
    public function testShowHD()
    {
        $value = rand(0, 1);
        $this->fixture->setHd($value);

        $actual = $this->fixture->getHd();
        $this->assertSame((bool) $value, $actual, 'Problem getting or setting the Show HD as a boolean.');
    }

    /**
     * Test set and get Show HD.
     */
    public function testShowURL()
    {
        $value = rand();
        $this->fixture->setUrl($value);

        $actual = $this->fixture->getUrl();
        $this->assertSame((string) $value, $actual, 'Problem getting or setting the Show URL as a string.');
    }
}
