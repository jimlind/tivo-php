<?php

namespace JimLind\TiVo\Factory;

use JimLind\TiVo\Factory\ShowFactory;

/**
 * Default show list factory to build a list of show models.
 */
class ShowListFactory
{
    /**
     * @var ShowFactory
     */
    private $showFactory = null;

    /**
     * @var ArrayObject
     */
    private $showList = null;

    /**
     * Constructs the ShowList Factory.
     */
    public function __construct()
    {
        $this->showFactory = new ShowFactory();
        $this->showList    = new \ArrayObject();
    }

    /**
     * Create a list of shows from a list of XML Elements.
     *
     * @param SimpleXMLElement[] $xmlList XML Element from the TiVo.
     *
     * @return JimLind\TiVo\Model\Show[]
     */
    public function createShowListFromXmlList($xmlList)
    {
        foreach ($xmlList as $xmlElement) {
            $show = $this->showFactory->createShowFromXml($xmlElement);
            $this->showList->append($show);
        }

        // Array of created shows.
        return $this->showList;
    }
}