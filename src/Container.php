<?php

namespace Butterfly\Component\DI;

use Butterfly\Component\DI\Exception\BuildObjectException;
use Butterfly\Component\DI\Exception\BuildServiceException;
use Butterfly\Component\DI\Exception\IncorrectSyntheticServiceException;
use Butterfly\Component\DI\Exception\UndefinedInstanceException;
use Butterfly\Component\DI\Exception\UndefinedParameterException;
use Butterfly\Component\DI\Exception\UndefinedServiceException;
use Butterfly\Component\DI\Exception\UndefinedTagException;
use Butterfly\Component\DI\Keeper;

/**
 * Container
 * done Aliases
 * done Triggers
 * done Synthetic Service
 * done Private Field Injection
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

    const SERVICE_CONTAINER_ID = 'service_container';

    /**
     * @var array
     */
    protected $configuration = array(
        'parameters' => array(),
        'interfaces' => array(),
        'services'   => array(),
        'tags'       => array(),
        'aliases'    => array(),
    );

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
        $serviceBuilder = new ServiceFactory($this, $this->configuration['interfaces']);

        $this->builders = array(
            self::SCOPE_SINGLETON => new Keeper\Singleton($serviceBuilder),
            self::SCOPE_PROTOTYPE => new Keeper\Prototype($serviceBuilder),
            self::SCOPE_FACTORY   => new Keeper\Factory($serviceBuilder),
            self::SCOPE_SYNTHETIC => new Keeper\Synthetic(),
        );
    }

    /**
     * @param string $id
     * @return mixed
     * @throws UndefinedInstanceException if instance is not found
     */
    public function get($id)
    {
        if ($this->hasParameter($id)) {
            return $this->getParameter($id);
        }

        if ($this->hasService($id)) {
            return $this->getService($id);
        }

        if ($this->hasTag($id)) {
            return $this->getServicesByTag($id);
        }

        throw new UndefinedInstanceException(sprintf("Instance '%s' is not found", $id));
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return $this->hasParameter($id) ||
               $this->hasService($id) ||
               $this->hasTag($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasParameter($id)
    {
        return array_key_exists(strtolower($id), $this->configuration['parameters']);
    }

    /**
     * @param string $id
     * @return mixed
     * @throws UndefinedParameterException if parameter is not found
     */
    public function getParameter($id)
    {
        $id = strtolower($id);

        if (!$this->hasParameter($id)) {
            throw new UndefinedParameterException(sprintf("Parameter '%s' is not found", $id));
        }

        return $this->configuration['parameters'][$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasService($id)
    {
        $id = strtolower($id);

        if (self::SERVICE_CONTAINER_ID == $id) {
            return true;
        }

        if (array_key_exists($id, $this->configuration['services'])) {
            return true;
        }

        if (array_key_exists($id, $this->configuration['aliases']) &&
            $this->hasService($this->configuration['aliases'][$id])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $id
     * @return Object
     * @throws UndefinedServiceException if service is not found
     * @throws BuildServiceException if scope is not found
     * @throws BuildServiceException if fail to build service
     */
    public function getService($id)
    {
        $id = strtolower($id);

        if (self::SERVICE_CONTAINER_ID == $id) {
            return $this;
        }

        $serviceConfiguration = $this->getServiceDefinition($id);

        $scope = isset($serviceConfiguration['scope']) ? $serviceConfiguration['scope'] : self::SCOPE_SINGLETON;

        try {
            return $this->getBuilder($scope)->buildObject($id, $serviceConfiguration);
        } catch (BuildObjectException $e) {
            throw new BuildServiceException(sprintf("Failed to build service '%s': %s", $id, $e->getMessage()));
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasTag($name)
    {
        return array_key_exists(strtolower($name), $this->configuration['tags']);
    }

    /**
     * @return array
     */
    public function getTagsList()
    {
        return array_keys($this->configuration['tags']);
    }

    /**
     * @param string $name
     * @return object[]
     */
    public function getServicesByTag($name)
    {
        $servicesIds = $this->getServicesIdsByTag($name);

        $services = array();

        foreach ($servicesIds as $serviceId) {
            $services[] = $this->getService($serviceId);
        }

        return $services;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getServicesIdsByTag($name)
    {
        $name = strtolower($name);

        return $this->hasTag($name)
            ? $this->configuration['tags'][$name]
            : array();
    }

    /**
     * @param string $id
     * @param object $service
     * @throws IncorrectSyntheticServiceException if incorrect object class
     */
    public function setSyntheticService($id, $service)
    {
        $id = strtolower($id);

        $serviceDefinition = $this->getServiceDefinition($id);
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
     * @throws UndefinedServiceException if service is not found
     */
    protected function getServiceDefinition($id)
    {
        if (array_key_exists($id, $this->configuration['services'])) {
            return $this->configuration['services'][$id];
        }

        if (array_key_exists($id, $this->configuration['aliases'])) {
            return $this->getServiceDefinition($this->configuration['aliases'][$id]);
        }

        throw new UndefinedServiceException(sprintf("Service '%s' is not found", $id));
    }
}
