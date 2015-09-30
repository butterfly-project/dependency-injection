<?php

namespace Butterfly\Component\DI;

use Butterfly\Component\DI\Compiler\Annotation\ReflectionClass;
use Butterfly\Component\DI\Exception\BuildObjectException;
use Butterfly\Component\DI\Exception\BuildServiceException;
use Butterfly\Component\DI\Exception\IncorrectExpressionPathException;
use Butterfly\Component\DI\Exception\IncorrectSyntheticServiceException;
use Butterfly\Component\DI\Exception\UndefinedInstanceException;
use Butterfly\Component\DI\Keeper;

/**
 * Container
 * done Aliases
 * done Triggers
 * done Synthetic Service
 * done Private Field Injection
 * done Interface Injections
 * done Get interface
 * done Interfaces aliases
 * done Reflection
 * @todo Private services
 * @todo Depends-on http://docs.spring.io/spring/docs/current/spring-framework-reference/html/beans.html#beans-factory-dependson
 *
 * Container services
 * @todo Lazy Load Proxy
 * @todo Debug times
 * @todo Debug Dependency Statistics
 *
 * Container building
 * done Abstract Services
 * done Composer integration
 * done Annotations
 *
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class Container
{
    const SCOPE_SINGLETON = 'singleton';
    const SCOPE_FACTORY   = 'factory';
    const SCOPE_PROTOTYPE = 'prototype';
    const SCOPE_SYNTHETIC = 'synthetic';

    const EXPR_SERVICE = '@';
    const EXPR_CONFIGURATION = '%';

    const CONFIG_PATH_SEPARATOR = '/';

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @var Keeper\AbstractKeeper[]
     */
    protected $builders;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = array_merge($this->configuration, $configuration);

        $this->init();
    }

    protected function init()
    {
        $serviceBuilder = new ServiceFactory($this);

        $this->builders = array(
            self::SCOPE_SINGLETON => new Keeper\Singleton($serviceBuilder),
            self::SCOPE_PROTOTYPE => new Keeper\Prototype($serviceBuilder),
            self::SCOPE_FACTORY   => new Keeper\Factory($serviceBuilder),
            self::SCOPE_SYNTHETIC => new Keeper\Synthetic(),
        );
    }

    /**
     * @param string $expression
     * @return mixed
     * @throws IncorrectExpressionPathException if incorrect expression
     */
    public function get($expression)
    {
        $firstSymbol = substr($expression, 0, 1);
        switch ($firstSymbol) {
            case '%':
                $path     = array_filter(explode(self::CONFIG_PATH_SEPARATOR, substr($expression, 1)));
                $instance = $this->configuration;
                break;

            case '@':
                if ('@' == $expression) {
                    return $this;
                }

                $path       = array_filter(explode(self::CONFIG_PATH_SEPARATOR, $expression));
                $instanceId = array_shift($path);
                $instance   = $this->getInstance(substr($instanceId, 1));
                break;

            case '#':
                $path       = array_filter(explode(self::CONFIG_PATH_SEPARATOR, $expression));
                $instanceId = array_shift($path);
                $instance   = $this->getServicesByTag(substr($instanceId, 1));
                break;

            default:
                $path       = array_filter(explode(self::CONFIG_PATH_SEPARATOR, $expression));
                $instanceId = array_shift($path);
                $instance   = $this->getInstance($instanceId);
                break;
        }

        return $this->resolvePath($path, $instance);
    }

    /**
     * @param array $path
     * @param mixed $instance
     * @return mixed
     * @throws IncorrectExpressionPathException if incorrect expression
     */
    protected function resolvePath(array $path, $instance)
    {
        $path = array_filter($path);

        if (empty($path)) {
            return $instance;
        }

        $key = array_shift($path);

        if (is_array($instance) || $instance instanceof \ArrayObject) {

            if (!array_key_exists($key, $instance)) {
                throw new IncorrectExpressionPathException($key, $instance);
            }

            $result = $instance[$key];

        } elseif (is_object($instance)) {

            $instanceReflection = new ReflectionClass($instance);

            if ($instanceReflection->hasProperty($key) && $instanceReflection->getProperty($key)->isPublic()) {
                $result = $instance->$key;
            } elseif (is_callable(array($instance, $key))) {
                $result = call_user_func(array($instance, $key));
            } elseif (is_callable(array($instance, 'get'. ucfirst($key)))) {
                $result = call_user_func(array($instance, 'get'. ucfirst($key)));
            } else {
                throw new IncorrectExpressionPathException($key, $instance);
            }

        } else {
            throw new IncorrectExpressionPathException($key, $instance);
        }

        return $this->resolvePath($path, $result);
    }

    /**
     * @param string $expression
     * @return bool
     */
    public function has($expression)
    {
        $path = explode(self::CONFIG_PATH_SEPARATOR, $expression);

        $id          = array_shift($path);
        $firstSymbol = substr($id, 0, 1);
        $instanceId  = substr($id, 1);

        switch ($firstSymbol) {
            case '@':

                try {
                    $this->get($expression);
                    return true;
                } catch (IncorrectExpressionPathException $e) {
                    return false;
                }

                break;
            case '#':
                return $this->hasTag($instanceId);
            case '%':

                if (empty($instanceId)) {
                    return true;
                }

                if (!array_key_exists($instanceId, $this->configuration)) {
                    return false;
                }

                $instance = $this->configuration[$instanceId];

                break;
            default:
                try {
                    $this->get($expression);
                    return true;
                } catch (IncorrectExpressionPathException $e) {
                    return false;
                }
                break;
        }

        try {
            $this->resolvePath($path, $instance);
            return true;
        } catch (IncorrectExpressionPathException $e) {
            return false;
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    protected function hasInstance($id)
    {
        if (array_key_exists($id, $this->configuration)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $id
     * @return Object
     * @throws IncorrectExpressionPathException if incorrect expression
     * @throws BuildServiceException if scope is not found
     * @throws BuildServiceException if fail to build service
     */
    protected function getInstance($id)
    {
        $serviceConfiguration = $this->getInstanceDefinition($id);

        if (is_string($serviceConfiguration)) {
            return $this->get($serviceConfiguration);
        }

        $scope = isset($serviceConfiguration['scope']) ? $serviceConfiguration['scope'] : self::SCOPE_SINGLETON;

        try {
            return $this->getBuilder($scope)->buildObject($id, $serviceConfiguration);
        } catch (BuildObjectException $e) {
            throw new BuildServiceException(sprintf("Failed to build service '%s': %s", $id, $e->getMessage()));
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasInterface($id)
    {
        if (array_key_exists($id, $this->configuration['interfaces'])) {
            return true;
        }

        if (array_key_exists($id, $this->configuration['interfaces_aliases']) &&
            $this->hasInterface($this->configuration['interfaces_aliases'][$id])) {

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasTag($name)
    {
        return !empty($this->configuration['tags']) && array_key_exists($name, $this->configuration['tags']);
    }

    /**
     * @return array
     */
    public function getTagsList()
    {
        return !empty($this->configuration['tags']) ? array_keys($this->configuration['tags']) : array();
    }

    /**
     * @param string $name
     * @return ServicesCollection
     */
    public function getServicesByTag($name)
    {
        $servicesIds = $this->hasTag($name) ? $this->configuration['tags'][$name] : array();

        return new ServicesCollection($this, $servicesIds);
    }

    /**
     * @param string $id
     * @param object $service
     * @throws IncorrectExpressionPathException if incorrect expression
     * @throws IncorrectSyntheticServiceException if incorrect object class
     */
    public function setSyntheticService($id, $service)
    {
        $serviceDefinition = $this->getInstanceDefinition($id);
        $serviceClass      = $serviceDefinition['class'];

        if (!($service instanceof $serviceClass)) {
            throw new IncorrectSyntheticServiceException(sprintf(
                "Synthetic injection error for '%s': Object class '%s' does not math for '%s'",
                $id, get_class($service), $serviceClass
            ));
        }

        /** @var Keeper\Synthetic $synteticKeeper */
        $synteticKeeper = $this->getBuilder(self::SCOPE_SYNTHETIC);
        $synteticKeeper->setService($id, $service);
    }

    /**
     * @param string $scope
     * @return Keeper\AbstractKeeper
     * @throws BuildServiceException if scope is not found
     */
    protected function getBuilder($scope)
    {
        if (!isset($this->builders[$scope])) {
            throw new BuildServiceException(sprintf("Scope '%s' is not registered", $scope));
        }

        return $this->builders[$scope];
    }

    /**
     * @param string $id
     * @return array
     * @throws IncorrectExpressionPathException if instance is not found
     */
    protected function getInstanceDefinition($id)
    {
        if (array_key_exists($id, $this->configuration)) {
            return $this->configuration[$id];
        }

        throw new IncorrectExpressionPathException($id, $this->configuration);
    }
}
