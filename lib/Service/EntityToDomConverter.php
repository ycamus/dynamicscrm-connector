<?php


use connector\lib\Model\Entity;

/**
 * Class EntityToDomConverter
 *
 * @package Sixdg\DynamicsCRMConnector\Services
 */
class EntityToDomConverter
{
    /**
     * @var DOMHelper
     */
    protected $domHelper;

    /**
     * @var Entity;
     */
    protected $entity;

    /**
     * @param \DOMHelper $domHelper
     */
    public function __construct(DOMHelper $domHelper)
    {
        $this->domHelper = $domHelper;
    }

    /**
     * @param Entity $entity
     *
     * @return \DOMElement
     */
    public function convert($entity)
    {
        $this->entity = $entity;

        $entityStateNode = $this->domHelper->createElement('b:EntityState');
        $entityStateNode->setAttribute('i:nil', 'true');

        $idNode = $this->domHelper->createElement('b:Id', $entity->getId());
        $logicalNameNode = $this->domHelper->createElement('b:LogicalName', $this->entity->getEntityName());

        $entityNode = $this->domHelper->createElement('entity');
        $entityNode->appendChild($this->createAttributeNode());
        $entityNode->appendChild($entityStateNode);
        $entityNode->appendChild($this->getFormattedValuesNode());
        $entityNode->appendChild($idNode);
        $entityNode->appendChild($logicalNameNode);
        $entityNode->appendChild($this->getRelatedEntitiesNode());

        return $entityNode;
    }

    /**
     * @return \DOMElement
     */
    private function getFormattedValuesNode()
    {
        $formattedValuesNode = $this->domHelper->createElement('b:FormattedValues');
        $formattedValuesNode->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:c',
            'http://schemas.datacontract.org/2004/07/System.Collections.Generic'
        );

        return $formattedValuesNode;
    }

    /**
     * @return \DOMElement
     */
    private function getRelatedEntitiesNode()
    {
        $relatedEntitiesNode = $this->domHelper->createElement('b:RelatedEntities');
        $relatedEntitiesNode->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:c',
            'http://schemas.datacontract.org/2004/07/System.Collections.Generic'
        );

        return $relatedEntitiesNode;
    }

    /**
     * @return \DOMElement
     */
    private function createAttributeNode()
    {
        $attributeNode = $this->domHelper->createElementNS(
            'http://schemas.microsoft.com/xrm/2011/Contracts',
            'b:Attributes'
        );
        $attributeNode->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:c',
            'http://schemas.datacontract.org/2004/07/System.Collections.Generic'
        );

        foreach ($this->entity->getMetadataRegistry() as $key => $item) {
            $method = 'get' . $key;
            if ($this->entity->$method()) {
                if ($item->getIsLookup()) {
                    $node = $this->getLookupDom($key, $item);
                    $attributeNode->appendChild($node);
                } else {
                    $node = $this->populateKeyValuePairs($key, $item);
                    $attributeNode->appendChild($node);
                }
            }
        }

        return $attributeNode;
    }

    /**
     * @param string $key
     * @param string $item
     */
    private function getLookupDom($key, $item)
    {
        $keyValueNode = $this->domHelper->createElement('b:KeyValuePairOfstringanyType');
        $keyValueNode->appendChild($this->createKeyNode($key));

        $valueNode = $keyValueNode->appendChild($this->domHelper->createElement('c:value'));
        $valueNode->setAttribute('i:type', 'b:EntityReference');
        $method = 'get' . $key;
        $valueNode->appendChild($this->domHelper->createElement('b:Id', $this->entity->$method()->getId()));
        $valueNode->appendChild($this->domHelper->createElement('b:LogicalName', $this->entity->$method()->getEntityName()));

        $valueNode->appendChild($this->domHelper->createElement('b:Name'))->setAttribute('i:nil', 'true');

        $keyValueNode->appendChild($valueNode);

        return $keyValueNode;
    }

    /**
     * @param string $key
     * @param object $item
     *
     * @return \DOMElement
     */
    private function populateKeyValuePairs($key, $item)
    {
        $type = strtolower($item->getAttributeType());

        $nameSpace = $this->getNodeValueNamespace($type);
        $value = $this->getNodeValue($key, $type);
        $valueType = $this->getNodeValueType($type);
        $valueChild = null;

        if ($valueType === "OptionSetValue") {
            $valueChild = $this->domHelper->createElement('b:Value', $this->entity->$key);
        }

        $keyValueNode = $this->domHelper->createElement('b:KeyValuePairOfstringanyType');
        $keyValueNode->appendChild($this->createKeyNode($key));
        $keyValueNode->appendChild($this->createValueNode($nameSpace, $valueType, $value, $valueChild));

        return $keyValueNode;
    }

    /**
     * @param string $key
     *
     * @return \DOMElement
     */
    private function createKeyNode($key)
    {
        return $this->domHelper->createElement('c:key', $key);
    }

    /**
     * @param string $nameSpace
     * @param string $valueType
     * @param mixed  $value
     * @param mixed  $valueChild
     *
     * @return \DOMElement
     */
    private function createValueNode($nameSpace, $valueType, $value = null, $valueChild = null)
    {
        $valueNode = $this->domHelper->createElement('c:value');
        $iTypePrefix = 'd:';
        if ($valueChild) {
            $iTypePrefix = 'b:';
        }
        $valueNode->setAttribute('i:type', $iTypePrefix . $valueType);
        $valueNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d', $nameSpace);

        if ($value) {
            $valueNode->appendChild(new \DOMText($value));
        }

        if ($valueChild) {
            $valueNode->appendChild($valueChild);
        }

        return $valueNode;
    }

    /**
     * @param string $key
     * @param string $type
     *
     * @return null|string
     */
    private function getNodeValue($key, $type)
    {
        $value = $this->entity->$key;
        switch ($type) {
            case 'datetype':
                $value = gmdate("Y-m-d\TH:i:s\Z", $value);
                break;
            case 'picklist':
            case 'state':
            case 'status':
                $value = null;
                break;
        }

        return $value;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getNodeValueType($type)
    {
        switch ($type) {
            case 'memo':
                $type = 'string';
                break;
            case 'integer':
                $type = 'int';
                break;
            case 'datetime':
                $type = 'dateTime';
                break;
            case 'uniqueidentifier':
                $type = 'guid';
                break;
            case 'picklist':
            case 'state':
            case 'status':
                $type = 'OptionSetValue';
                break;
        }

        return $type;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getNodeValueNamespace($type)
    {
        $nameSpace = 'http://www.w3.org/2001/XMLSchema';

        switch ($type) {
            case "status":
                $nameSpace = 'http://schemas.microsoft.com/xrm/2011/Contracts';
                break;
        }

        return $nameSpace;
    }
}
