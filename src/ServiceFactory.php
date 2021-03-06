<?php

namespace Butterfly\Component\DI;

use Butterfly\Component\DI\Exception\BuildObjectException;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceFactory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $interfaces;

    /**
     * @param Container $container
     * @param array $interfaces
     */
    public function __construct(Container $container, array $interfaces = array())
    {
        $this->container  = $container;
        $this->interfaces = $interfaces;
    }

    /**
     * @param array $configuration
     * @return Object
     * @throws BuildObjectException if impossible to create a service
     * @throws BuildObjectException if class is not found
     * @throws BuildObjectException if factory class is not found
     * @throws BuildObjectException if factory method is not found
     * @throws BuildObjectException if object's method is not found
     * @throws BuildObjectException if object's property is not found
     * @throws BuildObjectException if trigger type is not found
     * @throws BuildObjectException if trigger class is not found
     * @throws BuildObjectException if trigger method is not found
     */
    public function create(array $configuration)
    {
        $objectBuilder = $this->createObjectBuilder();

        if (isset($configuration['preTriggers'])) {
            $this->runTriggers($configuration['preTriggers']);
        }

        $this->createService($objectBuilder, $configuration);

        if (isset($configuration['calls'])) {
            $this->callsMethods($objectBuilder, $configuration['calls']);
        }

        if (isset($configuration['properties'])) {
            $this->initProperties($objectBuilder, $configuration['properties']);
        }

        $object = $objectBuilder->getObject();

        if (isset($configuration['postTriggers'])) {
            $this->runTriggers($configuration['postTriggers']);
        }

        return $object;
    }

    /**
     * @param ObjectBuilder $objectBuilder
     * @param array $configuration
     * @throws BuildObjectException if impossible to create a service
     */
    protected function createService(ObjectBuilder $objectBuilder, array $configuration)
    {
        $dependencies = !empty($configuration['arguments']) ? $configuration['arguments'] : array();
        $arguments    = $this->resolveDependencies($dependencies);

        if (isset($configuration['class'])) {
            $objectBuilder->nativeCreate($configuration['class'], $arguments);
        } elseif (isset($configuration['factoryStaticMethod'])) {
            list ($className, $methodName) = $configuration['factoryStaticMethod'];
            $objectBuilder->staticFactoryMethodCreate($className, $methodName, $arguments);
        } elseif (isset($configuration['factoryMethod'])) {
            list ($factoryServiceId, $methodName) = $configuration['factoryMethod'];
            $factoryService = $this->container->getService(substr($factoryServiceId, 1));
            $objectBuilder->factoryMethodCreate($factoryService, $methodName, $arguments);
        } else {
            throw new BuildObjectException('Impossible to create a service');
        }
    }

    /**
     * @param ObjectBuilder $objectBuilder
     * @param array $calls
     */
    protected function callsMethods(ObjectBuilder $objectBuilder, array $calls)
    {
        foreach ($calls as $callConfiguration) {
            list($methodName, $dependencies) = $callConfiguration;

            $forceCreate = false;

            if (array_key_exists(2, $callConfiguration)) {
                $forceCreate = (bool)$callConfiguration[2];
            }

            $objectBuilder->callObjectMethod($methodName, $this->resolveDependencies($dependencies), $forceCreate);
        }
    }

    /**
     * @param ObjectBuilder $objectBuilder
     * @param array $properties
     */
    protected function initProperties(ObjectBuilder $objectBuilder, array $properties)
    {
        foreach ($properties as $propertyName => $argument) {
            $objectBuilder->setObjectProperty($propertyName, $this->resolveDependence($argument));
        }
    }

    /**
     * @param array $triggers
     * @throws BuildObjectException if impossible to run a trigger
     * @throws BuildObjectException if trigger class is not found
     * @throws BuildObjectException if trigger method is not found
     */
    protected function runTriggers(array $triggers)
    {
        foreach ($triggers as $trigger) {
            if (isset($trigger['service'])) {
                $this->runServiceTrigger($trigger);
            } elseif (isset($trigger['class'])) {
                $this->runStaticTrigger($trigger);
            } else {
                throw new BuildObjectException(sprintf("Impossible to run a trigger"));
            }
        }
    }

    /**
     * @param array $triggerConfiguration
     * @throws BuildObjectException if object's method is not found
     */
    protected function runServiceTrigger(array $triggerConfiguration)
    {
        $service   = $this->resolveDependence($triggerConfiguration['service']);
        $arguments = $this->resolveDependencies($triggerConfiguration['arguments']);

        $this
            ->createObjectBuilder($service)
            ->callObjectMethod($triggerConfiguration['method'], $arguments);
    }

    /**
     * @param array $triggerConfiguration
     * @throws BuildObjectException if trigger class is not found
     * @throws BuildObjectException if trigger method is not found
     */
    protected function runStaticTrigger(array $triggerConfiguration)
    {
        if (!class_exists($triggerConfiguration['class'])) {
            throw new BuildObjectException(sprintf("Trigger class '%s' is not found", $triggerConfiguration['class']));
        }
        if (!method_exists($triggerConfiguration['class'], $triggerConfiguration['method'])) {
            throw new BuildObjectException(sprintf(
                "Method '%s' for trigger class '%s' is not found",
                $triggerConfiguration['class'],
                $triggerConfiguration['method']
            ));
        }

        $callback  = array($triggerConfiguration['class'], $triggerConfiguration['method']);
        $arguments = $this->resolveDependencies($triggerConfiguration['arguments']);

        call_user_func_array($callback, $arguments);
    }

    /**
     * @param array $dependencies
     * @return array
     */
    protected function resolveDependencies(array $dependencies)
    {
        $resolvedDependencies = array();

        foreach ($dependencies as $dependence) {
            $resolvedDependencies[] = $this->resolveDependence($dependence);
        }

        return $resolvedDependencies;
    }

    /**
     * @param mixed $dependence
     * @return mixed
     */
    protected function resolveDependence($dependence)
    {
        return $this->container->get($dependence);
    }

    /**
     * @param Object|null $object
     * @return ObjectBuilder
     */
    protected function createObjectBuilder($object = null)
    {
        return new ObjectBuilder($object);
    }
}
