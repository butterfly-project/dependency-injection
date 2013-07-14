<?php

namespace Syringe\Component\DI;

class ServiceFactory
{
    /**
     * @var ObjectBuilder
     */
    protected $objectBuilder;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param ObjectBuilder $objectBuilder
     * @param Container $container
     */
    public function __construct(ObjectBuilder $objectBuilder, Container $container)
    {
        $this->objectBuilder = $objectBuilder;
        $this->container     = $container;
    }

    /**
     * @param array $configuration
     * @return Object
     * @throws Exception\BuildObjectException if class is not found
     * @throws Exception\BuildObjectException if factory class is not found
     * @throws Exception\BuildObjectException if factory method is not found
     * @throws Exception\BuildObjectException if object's method is not found
     * @throws Exception\BuildObjectException if object's property is not found
     */
    public function build(array $configuration)
    {
        $objectBuilder = clone $this->objectBuilder;

        if (isset($configuration['class'])) {
            $dependencies = !empty($configuration['arguments']) ? $configuration['arguments'] : [];

            $objectBuilder->nativeCreate($configuration['class'], $this->resolveDependencies($dependencies));
        } elseif (isset($configuration['factoryStaticMethod'])) {
            list ($className, $methodName) = $configuration['factoryStaticMethod'];
            $dependencies = !empty($configuration['arguments']) ? $configuration['arguments'] : [];

            $objectBuilder->staticFactoryMethodCreate($className, $methodName, $this->resolveDependencies($dependencies));
        } elseif (isset($configuration['factoryMethod'])) {
            list ($factoryServiceId, $methodName) = $configuration['factoryMethod'];
            $factoryService = $this->container->get(substr($factoryServiceId, 1));
            $dependencies   = !empty($configuration['arguments']) ? $configuration['arguments'] : [];

            $objectBuilder->factoryMethodCreate($factoryService, $methodName, $this->resolveDependencies($dependencies));
        }

        if (isset($configuration['calls'])) {
            foreach ($configuration['calls'] as $callConfiguration) {
                list($methodName, $dependencies) = $callConfiguration;
                $objectBuilder->callObjectMethod($methodName, $this->resolveDependencies($dependencies));
            }
        }

        if (isset($configuration['properties'])) {
            foreach ($configuration['properties'] as $propertyName => $argument) {
                $objectBuilder->setObjectProperty($propertyName, $this->resolveDependence($argument));
            }
        }

        return $objectBuilder->getObject();
    }

    /**
     * @param array $dependencies
     * @return array
     */
    protected function resolveDependencies(array $dependencies)
    {
        $resolvedDependencies = [];

        foreach ($dependencies as $dependence) {
            $resolvedDependencies[] = $this->resolveDependence($dependence);
        }

        return $resolvedDependencies;
    }

    /**
     * @param string $dependence
     * @return mixed
     */
    protected function resolveDependence($dependence)
    {
        if (!is_string($dependence)) {
            return $dependence;
        }

        $firstSymbol = substr($dependence, 0, 1);
        switch ($firstSymbol) {
            case '@':
                return $this->container->get(substr($dependence, 1));
                break;
            case '#':
                return $this->container->getServicesByTag(substr($dependence, 1));
                break;
            default:
                return $dependence;
                break;
        }
    }
}
