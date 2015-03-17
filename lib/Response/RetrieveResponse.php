<?php


/**
 * Class RetrieveResponse
 *
 * @package Sixdg\DynamicsCRMConnector\Responses
 */
class RetrieveResponse extends DynamicsCRMResponse
{

    /**
     * Returns the attributes as array
     *
     * @param bool $formatted set to false to get the raw attributes
     *
     * @return array
     */
    public function asArray($formatted = true)
    {
        $array = array(
            $this->getArrayFromNamespace(
                'http://schemas.datacontract.org/2004/07/System.Collections.Generic',
                $formatted
            )
        );

        $array[0]['id'] = $this->getEntityId();

        return $array;
    }

    /**
     * Get the Entity
     *
     * @param EntityFactory $entityFactory
     *
     * @return Entity
     */
    public function getEntity($entityFactory)
    {
        $entitites = $this->getEntities($entityFactory);

        return $entitites[0];
    }

    private function getEntityId()
    {
        $tags = $this->getElementsByTagName('Id');
        $idTag = $tags->item($tags->length - 1);

        return $idTag->textContent;
    }

    /**
     *  returns the entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        //the entity name is in the last LogicalName tag
        $tags = $this->getElementsByTagName('LogicalName');
        $entityNameTag = $tags->item($tags->length - 1);

        return $entityNameTag->textContent;
    }
}
