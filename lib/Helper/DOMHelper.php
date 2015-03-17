<?php


/**
 * Class DOMHelper
 *
 * @package connector\lib\Helper\DOM
 */
class DOMHelper
{
    private $dom;

    /**
     * @return \DOMDocument
     */
    public function createNewDomDocument()
    {
        if (!$this->dom) {
            $this->dom = new \DOMDocument();
        }

        return $this->dom;
    }

    /**
     * @param string $namespaceURI
     * @param string $qualifiedName
     *
     * @return mixed
     */
    public function createElementNS($namespaceURI, $qualifiedName = null)
    {
        $this->createNewDomDocument();

        return $this->dom->createElementNS($namespaceURI, $qualifiedName);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return DOMElement
     */
    public function createElement($name, $value = null)
    {
        $this->createNewDomDocument();

        return $this->dom->createElement($name, $value);
    }

    /**
     * @return mixed
     */
    public function createDocumentFragment()
    {
        $this->createNewDomDocument();

        return $this->dom->createDocumentFragment();
    }

    /**
     * @return mixed
     */
    public function importNode(\DOMNode $importedNode, $deep = null)
    {
        $this->createNewDomDocument();

        return $this->dom->importNode($importedNode, $deep);
    }
}
