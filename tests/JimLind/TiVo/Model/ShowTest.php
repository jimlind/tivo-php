<?php

namespace Tests\JimLind\TiVo\Model;

use JimLind\TiVo\Model;

/**
 * Test the TiVo\Model\Show Model.
 */
class ShowTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test normal setters and getters.
     */
    public function testSettersAndGetters()
    {
        $show = new Model\Show();

        $show->setId(1);
        $show->setShowTitle('Show Title');
        $show->setEpisodeNumber(2);
        $show->setEpisodeTitle('Episode Title');
        $show->setDuration(3);
        $show->setDate(new \DateTime('1/1/2001 12:00:00'));
        $show->setDescription('Description');
        $show->setChannel(4);
        $show->setStation('Station');
        $show->setHD(true);
        $show->setURL('URL');

        $this->assertSame(1, $show->getId());
        $this->assertSame('Show Title', $show->getShowTitle());
        $this->assertSame(2, $show->getEpisodeNumber());
        $this->assertSame('Episode Title', $show->getEpisodeTitle());
        $this->assertSame(3, $show->getDuration());
        $this->assertEquals(new \DateTime('1/1/2001 12:00:00'), $show->getDate());
        $this->assertSame('Description', $show->getDescription());
        $this->assertSame(4, $show->getChannel());
        $this->assertSame('Station', $show->getStation());
        $this->assertSame(true, $show->getHD());
        $this->assertSame('URL', $show->getURL());
    }

    /**
     * Test converting setters and getters.
     */
    public function testConversionSettersAndGetters()
    {
        $show = new Model\Show();

        $show->setId('1');
        $show->setEpisodeNumber('2');
        $show->setDuration('3');
        $show->setDate('1/1/2001 12:00:00');
        $show->setChannel('4');
        $show->setHD(1);

        $this->assertSame(1, $show->getId());
        $this->assertSame(2, $show->getEpisodeNumber());
        $this->assertSame(3, $show->getDuration());
        $this->assertEquals(new \DateTime('1/1/2001 12:00:00'), $show->getDate());
        $this->assertSame(4, $show->getChannel());
        $this->assertSame(true, $show->getHD());
    }

    /**
     * Test alternate converting setters and getters.
     */
    public function testAlternateConversionSettersAndGetters()
    {
        $show = new Model\Show();

        $show->setHD(0);

        $this->assertSame(false, $show->getHD());
    }
}
