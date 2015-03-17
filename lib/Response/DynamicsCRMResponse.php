<?php


/**
 * Class DynamicsCRMResponse
 *
 * @package Sixdg\DynamicsCRMConnector\Responses
 */
abstract class DynamicsCRMResponse extends \DOMDocument
{

    /**
     * Returns an key-value array of nodes in the namespace given
     *
     * @param  string  $ns
     * @param  bool    $formatted  set to false to get the raw attributes
     * @param  DOMNode $parentNode
     * @return array   Key-value array of nodes in the namespace given
     */
    protected function getArrayFromNamespace($ns, $formatted, $parentNode = null)
    {
        if (!$parentNode) {
            $parentNode = $this;
        }
        $results = $parentNode->getElementsByTagNameNS($ns, '*');
        $keys = array();
        $values = array();
        foreach ($results as $item) {
            if (!$formatted and $item->parentNode->parentNode->localName == 'FormattedValues') {
                //we stop processing when we get the FormattedValues
                break;
            }
            if ($item->localName == 'key') {
                //build a keys array
                array_push($keys, $item->nodeValue);
            } elseif ($item->localName == 'value') {
                $value = $this->extractNodeValue($item);
                //build a values array
                array_push($values, $value);
            }
        }

        return array_combine($keys, $values);
    }

    /**
     * Creates the entities
     *
     * @param  Entity        $entity
     * @param  EntityFactory $entityFactory
     * @return array
     */
    public function getEntities(\EntityFactory $entityFactory)
    {
        $entities = array();
        foreach ($this->asArray(false) as $attributes) {
            $newEntity = $entityFactory->makeEntity($this->getEntityName());
            $entities[] = $this->hydrateEntity($newEntity, $attributes, $entityFactory);
        }

        return $entities;
    }

    /**
     *
     * @param  Entity $name
     * @param  array  $attributes
     * @return Entity
     */
    protected function hydrateEntity(
        Entity $entity,
        array $attributes,
        \EntityFactory $entityFactory
    )
    {
        $relatedEntities =array();
        foreach ($attributes as $attribute => $value) {
            if (is_array($value)) {
                //these are AliasedValues and belong to a link entity. We create an array for now
                $relatedEntities[$attribute] = $value;
                continue;
            }
            //set the attribute value
            $entity->$attribute = $value;
        }
        $this->createRelatedEntities($entity, $relatedEntities, $entityFactory);

        return $entity;
    }

    /**
     *
     */
    protected function createRelatedEntities(
        Entity $entity,
        array $relatedEntities,
        \EntityFactory $entityFactory
    )
    {
        $entities = array();

        foreach ($relatedEntities as $aliasedValue => $values) {
            $aliasedValueParts = explode('.', $aliasedValue);
            $entityAlias = $aliasedValueParts[0];

            if (!array_key_exists($entityAlias, $entities)) {
                //create a new related entity
                $entityLogicalName = $values['EntityLogicalName'];

                $entities[$entityAlias] = $entityFactory->makeEntity($entityLogicalName);
                //link it to the main entity
                $entity->setLinkEntity($entityAlias, $entities[$entityAlias]);
            }
            $attributeName = $values['AttributeLogicalName'];
            $attributeValue = $values['Value'];
            //set the attribute to the new entity
            $entities[$entityAlias]->$attributeName = $attributeValue;
        }
    }

    /**
     *  returns the entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->getTagValue('EntityName');
    }

    /**
     *  returns the value for a given tag
     *
     * @return string
     */
    protected function getTagValue($tagName)
    {
        return $this->getElementsByTagName($tagName)->item(0)->textContent;
    }

    /**
     *
     * @param  DOMElement $item
     * @return array      | string
     */
    protected function extractNodeValue(\DOMElement $item)
    {
        $value = $item->nodeValue;

        if (isset($item->attributes)) {
            //for AliasedValue types and EntityReference
            $typeNode = $item->attributes->getNamedItem('type');
            if ($typeNode and $typeNode->nodeValue == 'b:EntityReference') {
                return $this->getEntityReferenceValue($item);
            } elseif ($typeNode and $typeNode->nodeValue == 'b:AliasedValue') {
                return $this->getAliasedValue($item);
            }
        }

        return $value;
    }

    /**
     * Handle Entity Reference node type
     *
     * @param  \DOMElement $item
     * @return string
     */
    protected function getEntityReferenceValue(\DOMElement $item)
    {
        $value = new \stdClass;
        foreach ($item->childNodes as $element) {
            $method = $element->localName;
            $value->$method = $element->nodeValue;
        }

        return $value;
    }

    /**
     * Handle Aliased Value node type
     *
     * @param  \DOMElement $item
     * @return array
     */
    protected function getAliasedValue(\DOMElement $item)
    {
        $value =array();
        foreach ($item->childNodes as $element) {
            $value[$element->localName] = $element->nodeValue;
        }

        return $value;
    }
}
