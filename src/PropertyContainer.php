<?php

namespace PassManager;

/**
 * "Property Container" design pattern
 *
 */
class PropertyContainer
{

    /**
     * Properties storage key => value array
     *
     *
     * @var array
     *
     */
    protected array $storage;


    public function __construct(){
        $this->storage = [];
    }


    /**
     * Set property value
     *
     * @param string $name property name
     * @param mixed $value property value
     * @return void
     */
    public function setProperty(string $name, mixed $value): void
    {
        $this->storage[$name] = $value;
    }


    /**
     * Get property value
     *
     * @param string $name property name
     * @return mixed
     */
    public function getProperty(string $name): mixed
    {
        return $this->storage[$name] ?? false;
    }


    /**
     * Delete property value
     *
     * @param string $name property name
     * @return void
     */
    public function deleteProperty(string $name): void
    {
        if(isset($this->storage[$name])){
            unset($this->storage[$name]);
        }
    }


    /**
     * Update property value
     *
     * @param string $name property name
     * @param mixed $value property value
     * @return void
     */
    public function updateProperty(string $name, mixed $value): void
    {
        if(isset($this->storage[$name])){
            $this->storage[$name] = $value;
        }
    }
}