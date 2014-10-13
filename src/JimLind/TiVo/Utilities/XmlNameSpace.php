<?php

namespace JimLind\TiVo\Utilities;

/**
 * Adjust XML NameSpaces
 */
class XmlNameSpace
{
    /**
     * Add the default namespace as 'tivo' namespace.
     *
     * @param SimpleXMLElement $simpleXml
     */
    public static function addTiVoNameSpace(&$simpleXml)
    {
        $namespaces = $simpleXml->getNamespaces(true);
        if (isset($namespaces[''])) {
            $simpleXml->registerXPathNamespace('tivo', $namespaces['']);
        }
    }
}
