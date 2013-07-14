<?php

namespace Syringe\Component\DI;

use Syringe\Component\DI\Exception\BuildObjectException;

class ObjectBuilder
{
    /**
     * @var Object
     */
    protected $object;

    /**
     * @param string $className
     * @param array $arguments
     * @return $this
     * @throws Exception\BuildObjectException if class is not found
     */
    public function nativeCreate($className, array $arguments = [])
    {
        if (!class_exists($className)) {
            throw new BuildObjectException(sprintf("Class '%s' is not found", $className));
        }

        $reflection = new \ReflectionClass($className);

        $this->object = (null === $reflection->getConstructor())
            ? $reflection->newInstance()
            : $reflection->newInstanceArgs($arguments);

        return $this;
    }

    /**
     * @param string $factoryClassName
     * @param string $methodName
     * @param array $arguments
     * @return $this
     * @throws Exception\BuildObjectException if factory class is not found
     * @throws Exception\BuildObjectException if factory method is not found
     */
    public function staticFactoryMethodCreate($factoryClassName, $methodName, array $arguments = [])
    {
        if (!class_exists($factoryClassName)) {
            throw new BuildObjectException(sprintf("Factory class '%s' is not found", $factoryClassName));
        }

        if (!method_exists($factoryClassName, $methodName)) {
            throw new BuildObjectException(
                sprintf("Factory method '%s' for factory '%s' is not found", $methodName, $factoryClassName)
            );
        }

        $this->object = call_user_func_array([$factoryClassName, $methodName], $arguments);

        return $this;
    }

    /**
     * @param string $factory
     * @param string $methodName
     * @param array $arguments
     * @return $this
     * @throws Exception\BuildObjectException if factory method is not found
     */
    public function factoryMethodCreate($factory, $methodName, array $arguments = [])
    {
        if (!method_exists($factory, $methodName)) {
            throw new BuildObjectException(
                sprintf("Factory method '%s' for factory '%s' is not found", $methodName, get_class($factory))
            );
        }

        $this->object = call_user_func_array([$factory, $methodName], $arguments);

        return $this;
    }

    /**
     * @param string $methodName
     * @param array $arguments
     * @return $this
     * @throws Exception\BuildObjectException if object's method is not found
     */
    public function callObjectMethod($methodName, array $arguments = [])
    {
        if (!method_exists($this->object, $methodName)) {
            throw new BuildObjectException(
                sprintf("Method '%s' for object '%s' is not found", $methodName, get_class($this->object))
            );
        }

        call_user_func_array([$this->object, $methodName], $arguments);

        return $this;
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     * @return $this
     * @throws Exception\BuildObjectException if object's property is not found
     */
    public function setObjectProperty($propertyName, $value)
    {
        if (!property_exists($this->object, $propertyName)) {
            throw new BuildObjectException(
                sprintf("Property '%s' for object '%s' is not found", $propertyName, get_class($this->object))
            );
        }

        $this->object->$propertyName = $value;

        return $this;
    }

    /**
     * @return Object
     */
    public function getObject()
    {
        return $this->object;
    }
}
