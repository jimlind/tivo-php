<?php

namespace JimLind\TiVo\Tests\Characteristic;

use JimLind\TiVo\Characteristic\XmlTrait;
use PHPUnit_Framework_TestCase;

/**
 * Test the TiVo\Utilities\Log class
 */
class XmlNamespaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var XmlTrait Test Object
     */
    protected $fixture;

    protected function setUp()
    {
        $this->fixture = $this->getObjectForTrait('JimLind\TiVo\Characteristic\XmlTrait');
    }

    /**
     * Test a snippet of XML with a defined namespace
     */
    public function testNamespacedXml()
    {
        $xml = '<items xmlns="http://www.example.org/schema"><item id="1" /></items>';
        $simpleXml = simplexml_load_string($xml);
        $outputXml = $this->fixture->registerTiVoNamespace($simpleXml);

        $itemList = $outputXml->xpath('tivo:item[@id = 1]');
        $this->assertCount(1, $itemList);

        $this->assertEquals(
            $outputXml->getDocNamespaces(true),
            $outputXml->getNamespaces(true)
        );
    }

    /**
     * Test a snippet of XML without a namespace
     */
    public function testRawXml()
    {
        $xml = '<items><item id="1" /></items>';
        $simpleXml = simplexml_load_string($xml);
        $outputXml = $this->fixture->registerTiVoNamespace($simpleXml);

        $itemList = $outputXml->xpath('tivo:item[@id = 1]');
        $this->assertCount(1, $itemList);

        $this->assertEquals(
            $outputXml->getDocNamespaces(true),
            $outputXml->getNamespaces(true)
        );
    }

    /**
     * Test empty XML attempt.
     *
     * @expectedException Exception
     */
    public function testEmptyXMLString()
    {
        $xml = '';
        $simpleXml = simplexml_load_string($xml);
        $this->fixture->registerTiVoNamespace($simpleXml);
    }

    /**
     * Test XML output
     *
     * @param string $input    Raw XML string
     * @param string $expected Namespaced XML string
     *
     * @dataProvider testAfterRegisterProvider
     */
    public function testAfterRegister($input, $expected)
    {
        $inputXml  = simplexml_load_string($input);
        $outputXml = $this->fixture->registerTiVoNamespace($inputXml);
        $actual    = $outputXml->asXml();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for the after register test
     *
     * @return mixed[]
     */
    public function testAfterRegisterProvider()
    {
        return [
            [
                '<x />',
                "<?xml version=\"1.0\"?>\n<x xmlns=\"http://www.w3.org/2001/XMLSchema\"/>\n",
            ],
            [
                '<x><y /></x>',
                "<?xml version=\"1.0\"?>\n<x xmlns=\"http://www.w3.org/2001/XMLSchema\"><y/></x>\n",
            ],
        ];
    }
}
