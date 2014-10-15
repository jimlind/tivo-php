<?php

namespace JimLind\TiVo\Tests\Factory;

use JimLind\TiVo\Factory;
use JimLind\TiVo\Model;

/**
 * Test the factory for Show models.
 */
class ShowFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $fixture = null;

    /**
     * Setup the PHPUnit Test
     */
    public function setup()
    {
        $this->fixture = new Factory\ShowFactory(new Model\Show());
    }

    /**
     * Test logging with a logger.
     */
    public function testNormal()
    {
        $xml  = simplexml_load_string($this->returnXml());
        $show = $this->fixture->createFromXML($xml);

        $this->assertEquals(1234, $show->getId());
        $this->assertEquals('url?id=1234', $show->getURL());
    }

    /**
     * Creates a nice big XML string.
     *
     * @return string
     */
    public function returnXml()
    {
        $return = '<xml>'
                . '<Links><Content><Url>url?id=1234</Url></Content></Links>'
                . '<Details>'
                . '</Details>'
                . '</xml>';

        return $return;
    }

}
