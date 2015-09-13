<?php

namespace JimLind\TiVo\Factory;

use ArrayObject;
use JimLind\TiVo\Factory\ShowFactory;

/**
 * Build a list of populated shows
 */
class ShowListFactory
{
    /**
     * @var ShowFactory
     */
    protected $showFactory = null;

    /**
     * @var ArrayObject
     */
    protected $showList = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->showFactory = new ShowFactory();
        $this->showList    = new ArrayObject();
    }

    /**
     * Create a list of shows from a list of XML Elements
     *
     * @param SimpleXMLElement[] $xmlList XML Element from a TiVo
     *
     * @return JimLind\TiVo\Model\Show[]
     */
    public function createShowListFromXmlList($xmlList)
    {
        foreach ($xmlList as $xmlElement) {
            $show = $this->showFactory->createShowFromXml($xmlElement);
            $this->showList->append($show);
        }

        // List of shows
        return $this->showList;
    }
}
