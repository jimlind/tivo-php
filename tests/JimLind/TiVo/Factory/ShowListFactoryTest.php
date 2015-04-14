<?php

namespace JimLind\TiVo\Tests\Factory;

use JimLind\TiVo\Factory\ShowListFactory;

/**
 * Test the factory for show models.
 */
class ShowFactoryListTest extends \PHPUnit_Framework_TestCase
{
    protected $fixture = null;

    /**
     * Setup the PHPUnit test.
     */
    public function setup()
    {
        $this->fixture = new ShowListFactory();
    }

    /**
     * Tests that an ArrayObject was returned.
     */
    public function testReturnType()
    {
        $xmlList  = [simplexml_load_string('<xml><Details /></xml>')];
        $showList = $this->fixture->createShowListFromXmlList($xmlList);

        $this->assertInstanceOf('ArrayObject', $showList);
    }

    /**
     * Tests that list of shows is created.
     */
    public function testCreate()
    {
        $count     = rand(1, 10);
        $simpleXml = simplexml_load_string('<xml><Details /></xml>');
        $xmlList   = array_fill(0, $count, $simpleXml);

        $showList = $this->fixture->createShowListFromXmlList($xmlList);

        $this->assertCount($count, $showList);
        $this->assertContainsOnlyInstancesOf('JimLind\TiVo\Model\Show', $showList);
    }

}