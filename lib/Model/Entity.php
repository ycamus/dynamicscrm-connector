<?php

namespace connector\lib\Model;

/**
 * Class Entity
 *
 * @package Sixdg\DynamicsCRMConnector\Models
 */
class Entity
{

    private $id;
    private $name;
    private $metadataRegistry = array();
    private $data = array();
    private $linkEntities = array();

    /**
     * Entities must be created via the factor so the meta data gets created properly.
     */
    private function __construct()
    {

    }

    /**
     * @param $name
     *
     * @return mixed|null
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        if (strtolower($name) === "id") {
            return $this->getId();
        }

        if (isset($this->linkEntities[$name])) {
            return $this->linkEntities[$name];
        }

        if (!isset($this->getMetadataRegistry()[$name])) {
            throw new \InvalidArgumentException(
                "Call to get" . $name . " failed. $name must exist in entity metadata for " . $this->getEntityName()
            );
        }

        if (in_array($name, array_keys($this->data))) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getDataKeys()
    {
        return array_keys($this->data);
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        if (strtolower($name) === "id") {
            $this->setId($value);
        } else {
            if (!isset($this->getMetadataRegistry()[$name])) {
                throw new \InvalidArgumentException(
                    "Call to get" . $name . " failed. $name must exist in entity metadata for " . $this->getEntityName()
                );
            }
            $this->data[$name] = $value;
        }
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setEntityName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        if ($this->id === null) {
            $this->id = '00000000-0000-0000-0000-000000000000';
        }

        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getMetadataRegistry()
    {
        return $this->metadataRegistry;
    }

    /**
     * @param EntityMetadataRegistryItem[] $metadataRegistry
     */
    public function setMetadataRegistry($metadataRegistry)
    {
        $this->metadataRegistry = $metadataRegistry;
    }

    /**
     * Provides getter and setter methods.
     *
     * @param string $method    The method name
     * @param array  $arguments The method arguments
     *
     * @return mixed The returned value of the called method
     */
    public function __call($method, $arguments)
    {
        $action = substr($method, 0, 3);
        if (in_array($action, array('set', 'get'))) {
            $name = strtolower(substr($method, 3));
            $arguments = array_merge(array($name), $arguments);

            return call_user_func_array(
                array($this, '__' . $action),
                $arguments
            );
        }

        return false;
    }

    /**
     * Store the link to the link entity in the $data array
     *
     * @param string $name
     * @param Entity $entity
     */
    public function setLinkEntity($name, Entity $entity)
    {
        $this->linkEntities[strtolower($name)] = $entity;
    }

    /**
     * Magic method to check if a value has been set
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $allDataArray = array_merge($this->data, $this->linkEntities);

        if (array_key_exists($name, $allDataArray)) {
            return true;
        }

        return false;
    }

    /**
     * Magic method to unset a magic value
     *
     * @param string $name
     */
    public function __unset($name)
    {
        if (in_array($name, array_keys($this->data))) {
            unset($this->data[$name]);
        }
    }
}
