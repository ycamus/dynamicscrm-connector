<?php
namespace Sixdg\DynamicsCRMConnector\Factories;

use Sixdg\DynamicsCRMConnector\Controllers\DynamicsCRMController;
use Sixdg\DynamicsCRMConnector\Models\Entity;

/**
 * Class EntityFactory
 *
 * @package Sixdg\DynamicsCRMConnector\Factories
 */
class EntityFactory
{
    /**
     * @var DynamicsCRMController
     */
    protected $controller;
    /**
     * @var MetadataRegistryFactory
     */
    protected $metaDataFactory;

    /**
     * @param DynamicsCRMController   $controller
     * @param MetadataRegistryFactory $metaDataFactory
     */
    public function __construct(DynamicsCRMController $controller, MetadataRegistryFactory $metaDataFactory)
    {
        $this->controller = $controller;
        $this->metaDataFactory = $metaDataFactory;
    }

    /**
     * @param string $name The LogicalName of the entity
     *
     * @return Entity
     */
    public function makeEntity($name)
    {
        $reflection = new \ReflectionClass('\Sixdg\DynamicsCRMConnector\Models\Entity');

        $entity = $reflection->newInstanceWithoutConstructor();
        $entity->setEntityName($name);
        $entity->setMetaDataRegistry($this->metaDataFactory->makeMetaData($entity));

        return $entity;
    }
}
