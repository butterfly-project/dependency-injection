<?php

namespace Butterfly\Component\DI\Compiler\Annotation;

use Butterfly\Component\Annotations\Visitor\IAnnotationVisitor;
use Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class AnnotationConfigVisitor implements IAnnotationVisitor
{
    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var array
     */
    protected $services = array();

    /**
     * @return array
     */
    public function extractDiConfiguration()
    {
        return array(
            'services' => $this->services,
        );
    }

    public function clean()
    {
        $this->services = array();
    }

    /**
     * @param string $className
     * @param array $classAnnotations
     */
    public function visit($className, array $classAnnotations)
    {
        $serviceName = $this->getServiceName($className, $classAnnotations['class']);

        if (null === $serviceName) {
            return;
        }

        $reflectionClass = new ReflectionClass($className);

        $config      = $this->convertAutowiredToConfig($className, $classAnnotations, $reflectionClass);
        $config      = $this->convertAnnotationsToConfig($config, $className, $classAnnotations);

        $this->services[$serviceName] = $config;

        $servicesForFactories = $this->getServicesForFactories($serviceName, $reflectionClass, $classAnnotations['methods']);
        foreach ($servicesForFactories as $name => $config) {
            $this->services[$name] = $config;
        }
    }

    /**
     * @param string $className
     * @param array $classAnnotations
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    protected function convertAutowiredToConfig($className, array $classAnnotations, ReflectionClass $reflectionClass)
    {
        $config = array(
            'class' => $className,
        );

        list($arguments, $calls) = $this->getMethods($classAnnotations, $reflectionClass);
        $config = $this->addSectionIfNotEmpty($config, 'arguments', $arguments);
        $config = $this->addSectionIfNotEmpty($config, 'calls', $calls);

        $config = $this->addSectionIfNotEmpty($config, 'properties', $this->getProperties($classAnnotations, $reflectionClass));

        return $config;
    }

    /**
     * @param array $config
     * @param string $className
     * @param array $annotations
     * @return array
     */
    protected function convertAnnotationsToConfig(array $config, $className, array $annotations)
    {
        $classAnnotations = $annotations['class'];

        $config = $this->addSectionIfNotEmpty($config, 'arguments', $this->getServiceArguments($className, $classAnnotations));
        $config = $this->addSectionIfNotEmpty($config, 'calls', $this->getServiceCalls($className, $classAnnotations));
        $config = $this->addSectionIfNotEmpty($config, 'properties', $this->getServiceProperties($className, $classAnnotations));
        $config = $this->addSectionIfNotEmpty($config, 'alias', $this->getServiceAliases($classAnnotations, $className));
        $config = $this->addSectionIfNotEmpty($config, 'preTriggers', $this->getServiceTriggers('preTriggers', $classAnnotations, $className));
        $config = $this->addSectionIfNotEmpty($config, 'postTriggers', $this->getServiceTriggers('postTriggers', $classAnnotations, $className));
        $config = $this->addSectionIfNotEmpty($config, 'scope', $this->getScope($classAnnotations));
        $config = $this->addSectionIfNotEmpty($config, 'tags', $this->getTags($classAnnotations));

        return $config;
    }

    /**
     * @param string $factoryServiceName
     * @param ReflectionClass $reflectionClass
     * @param array $methodsAnnotations
     * @return array
     */
    protected function getServicesForFactories($factoryServiceName, ReflectionClass $reflectionClass, array $methodsAnnotations)
    {
        $classname = $reflectionClass->getName();

        $services = array();

        foreach ($methodsAnnotations as $methodName => $methodAnnotations) {
            $reflectionMethod = $reflectionClass->getMethod($methodName);

            if ($reflectionMethod->isConstructor() || !array_key_exists('factory', $methodAnnotations)) {
                continue;
            }

            $annotation = $methodAnnotations['factory'];

            if (null === $annotation) {
                throw new InvalidConfigurationException(sprintf(
                    "Incorrect @factory annotation value in %s. Expected string or array, given null",
                    $classname
                ));
            }


            $config = array();
            if ($reflectionMethod->isStatic()) {
                $config['factoryStaticMethod'] = array($classname, $methodName);
            } else {
                $config['factoryMethod'] = array('@' . $factoryServiceName, $methodName);
            }

            if (is_array($annotation)) {
                if (empty($annotation['service']) || empty($annotation['arguments'])) {
                    throw new InvalidConfigurationException(sprintf(
                        "Incorrect @factory annotation value in %s. Expected 'service' and 'arguments' key in array, given %s",
                        $classname, var_export($annotation, true)
                    ));
                }

                $serviceName = $annotation['service'];
                $arguments   = $annotation['arguments'];
            } else {
                $serviceName = $annotation;
                $arguments = array();
            }

            $config['arguments'] = $arguments;

            $services[$serviceName] = $config;
        }


        return $services;
    }

    /**
     * @param array $config
     * @param string $section
     * @param mixed $value
     * @return array
     */
    protected function addSectionIfNotEmpty(array $config, $section, $value)
    {
        if (!empty($value)) {
            $config[$section] = $value;
        }

        return $config;
    }

    /**
     * @param string $className
     * @param array $classAnnotations
     * @return array
     * @throws InvalidConfigurationException if invalid arguments annotation
     */
    protected function getServiceArguments($className, array $classAnnotations)
    {
        if (!array_key_exists('arguments', $classAnnotations)) {
            return array();
        }

        if (!is_array($classAnnotations['arguments'])) {
            throw new InvalidConfigurationException(sprintf(
                "Invalid value of @arguments annotation in '%s' service. Expected array, given: %s",
                $className, var_export($classAnnotations['arguments'], true)
            ));
        }

        $arguments = array();

        foreach ($classAnnotations['arguments'] as $dependency) {
            $arguments[] = $this->formatDependency($dependency);
        }

        return $arguments;
    }

    /**
     * @param string $className
     * @param array $classAnnotations
     * @return array
     * @throws InvalidConfigurationException if invalid calls annotation
     * @throws InvalidConfigurationException if invalid calls annotation
     */
    protected function getServiceCalls($className, array $classAnnotations)
    {
        if (!array_key_exists('calls', $classAnnotations)) {
            return array();
        }

        $calls = array();

        foreach ($classAnnotations['calls'] as $callConfig) {
            if (!is_array($callConfig) || count($callConfig) != 2) {
                throw new InvalidConfigurationException(sprintf(
                    "Invalid value of @calls annotation in '%s' service. Expected array with 2 values, given: %s",
                    $className, var_export($callConfig, true)
                ));
            }

            list($method, $rawArguments) = $callConfig;

            if (!is_array($rawArguments)) {
                throw new InvalidConfigurationException(sprintf(
                    "Invalid value of @calls annotation in '%s' service. Expected array arguments, given: %s",
                    $className, var_export($rawArguments, true)
                ));
            }

            $arguments = array();
            foreach ($rawArguments as $rawArgument) {
                $arguments[] = $this->formatDependency($rawArgument);
            }

            $calls[] = array($method, $arguments);
        }

        return $calls;
    }

    /**
     * @param string $className
     * @param array $classAnnotations
     * @return array
     */
    protected function getServiceProperties($className, array $classAnnotations)
    {
        if (!array_key_exists('properties', $classAnnotations)) {
            return array();
        }

        $properties = array();

        foreach ($classAnnotations['properties'] as $propertyName => $propertyConfig) {
            $properties[$propertyName] = $this->formatDependency($propertyConfig);
        }


        return $properties;
    }

    /**
     * @param array $classAnnotations
     * @param string $className
     * @return array
     */
    protected function getServiceAliases(array $classAnnotations, $className)
    {
        $aliases = array();

        if (null !== $classAnnotations['service']) {
            $aliases[] = strtolower($className);
        }

        if (array_key_exists('alias', $classAnnotations)) {
            $aliases = array_merge($aliases, (array)$classAnnotations['alias']);
        }

        return $aliases;
    }

    /**
     * @param string $annotationName
     * @param array $classAnnotations
     * @param string $className
     * @return array
     */
    protected function getServiceTriggers($annotationName, array $classAnnotations, $className)
    {
        if (!array_key_exists($annotationName, $classAnnotations)) {
            return array();
        }

        $rawTriggers = $classAnnotations[$annotationName];

        if (!is_array($rawTriggers)) {
            throw new InvalidConfigurationException(sprintf(
                "Invalid @%s annotation value in '%s'. Expected array, given: %s",
                $annotationName, $className, var_export($rawTriggers, true)
            ));
        }

        $triggers = array();

        foreach ($rawTriggers as $rawTrigger) {
            if (!is_array($rawTrigger)) {
                throw new InvalidConfigurationException(sprintf(
                    "Invalid @%s annotation value in '%s'. Expected array trigger configuration, given: %s",
                    $annotationName, $className, var_export($rawTrigger, true)
                ));
            }

            if (!array_key_exists('service', $rawTrigger) && !array_key_exists('class', $rawTrigger)) {
                throw new InvalidConfigurationException(sprintf(
                    "Invalid @%s annotation value in '%s'. Expected section 'class' or 'service' in trigger configuration, given: %s",
                    $annotationName, $className, var_export($rawTrigger, true)
                ));
            }

            if (!array_key_exists('method', $rawTrigger)) {
                throw new InvalidConfigurationException(sprintf(
                    "Invalid @%s annotation value in '%s'. Expected section 'method' in trigger configuration, given: %s",
                    $annotationName, $className, var_export($rawTrigger, true)
                ));
            }

            if (!array_key_exists('arguments', $rawTrigger) || !is_array($rawTrigger['arguments'])) {
                throw new InvalidConfigurationException(sprintf(
                    "Invalid @%s annotation value in '%s'. Expected section 'method' in trigger configuration, given: %s",
                    $annotationName, $className, var_export($rawTrigger, true)
                ));
            }

            $arguments = array();
            foreach ($rawTrigger['arguments'] as $dependency) {
                $arguments[] = $this->formatDependency($dependency);
            }

            $rawTrigger['arguments'] = $arguments;

            $triggers[] = $rawTrigger;
        }

        return $triggers;
    }

    /**
     * @param array $classAnnotations
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    protected function getMethods(array $classAnnotations, ReflectionClass $reflectionClass)
    {
        $constructorArguments = array();
        $calls = array();

        foreach ($classAnnotations['methods'] as $methodName => $methodAnnotation) {
            if (!array_key_exists('autowired', $methodAnnotation)) {
                continue;
            }

            $arguments = $this->getMethodArguments($methodName, $methodAnnotation, $reflectionClass);

            if (null === $arguments) {
                continue;
            }

            if ('__construct' == $methodName) {
                $constructorArguments = $arguments;
            } else {
                $calls[] = array($methodName, $arguments);
            }
        }

        return array($constructorArguments, $calls);
    }

    /**
     * @param string $methodName
     * @param array $methodAnnotation
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    protected function getMethodArguments($methodName, array $methodAnnotation, ReflectionClass $reflectionClass)
    {
        if (is_array($methodAnnotation['autowired'])) {
            $arguments = array();

            foreach ($methodAnnotation['autowired'] as $dependency) {
                $arguments[] = $this->formatDependency($dependency);
            }

            return $arguments;
        }

        if (null === $methodAnnotation['autowired']) {
            $parameters = $reflectionClass->getMethod($methodName)->getParameters();
            $arguments  = $this->getMethodDependenciesForNative($parameters);

            if (null !== $arguments) {
                return $arguments;
            }
        }

        if (!empty($methodAnnotation['param'])) {
            $arguments = $this->getMethodDependenciesForPhpDoc($methodAnnotation['param'], $reflectionClass);

            if (null !== $arguments) {
                return $arguments;
            }
        }

        return null;
    }

    /**
     * @param \ReflectionParameter[] $reflectionParameters
     * @return array|null
     */
    protected function getMethodDependenciesForNative(array $reflectionParameters)
    {
        $arguments = array();

        /** @var \ReflectionParameter[] $reflectionParameters */
        foreach ($reflectionParameters as $reflectionParameter) {
            $position             = $reflectionParameter->getPosition();
            $nativeTypeClass      = $reflectionParameter->getClass();

            if (null === $nativeTypeClass) {
                return null;
            }

            $innerServiceName     = '@' . strtolower($nativeTypeClass->getName());
            $arguments[$position] = $innerServiceName;
        }

        return $arguments;
    }

    /**
     * @param array $annotations
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    protected function getMethodDependenciesForPhpDoc(array $annotations, ReflectionClass $reflectionClass)
    {
        $arguments = array();

        foreach ($annotations as $value) {
            $words         = array_filter(explode(' ', $value));
            $shortType     = array_shift($words);
            $fullNamespace = strtolower($reflectionClass->getFullNamespace($shortType));
            $arguments[]   = '@' . substr($fullNamespace, 1);
        }

        return $arguments;
    }

    /**
     * @param string $className
     * @param array $classAnnotations
     * @return mixed
     */
    protected function getServiceName($className, array $classAnnotations)
    {
        if (!array_key_exists('service', $classAnnotations)) {
            return null;
        }

        $serviceName = !empty($classAnnotations['service'])
            ? $classAnnotations['service']
            : $className;

        return strtolower($serviceName);
    }

    /**
     * @param array $classAnnotations
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    protected function getProperties(array $classAnnotations, ReflectionClass $reflectionClass)
    {
        $properties = array();

        foreach ($classAnnotations['properties'] as $propertyName => $propertyAnnotation) {
            if (!array_key_exists('autowired', $propertyAnnotation)) {
                continue;
            }

            $properties[$propertyName] = $this->getPropertyDependency($propertyName, $propertyAnnotation, $reflectionClass);
        }

        return $properties;
    }

    /**
     * @param string $propertyName
     * @param array $propertyAnnotation
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    protected function getPropertyDependency($propertyName, array $propertyAnnotation, ReflectionClass $reflectionClass)
    {
        if (is_array($propertyAnnotation['autowired'])) {
            throw new \RuntimeException(sprintf("Incorrect @autowired value in property '%s'. Expected service name (string type), array given.", $propertyName));
        }

        if (null === $propertyAnnotation['autowired'] && empty($propertyAnnotation['var'])) {
            throw new \RuntimeException(sprintf("Impossible to obtain property type in property '%s'. Set type in phpDoc: '@var Type' or write dependency name in annotation: '@autowired service.name'", $propertyName));
        }

        if (null === $propertyAnnotation['autowired']) {
            $namespace   = $reflectionClass->getFullNamespace($propertyAnnotation['var']);
            $serviceName = substr($namespace, 1);
            $property    = strtolower($serviceName);
        } else {
            $words    = explode(' ', $propertyAnnotation['autowired']);
            $property = reset($words);
        }

        return $property;
    }

    /**
     * @param array $classAnnotations
     * @return string|null
     */
    protected function getScope(array $classAnnotations)
    {
        return !empty($classAnnotations['scope'])
            ? (string)$classAnnotations['scope']
            : null;
    }

    /**
     * @param array $classAnnotations
     * @return array|null
     */
    protected function getTags(array $classAnnotations)
    {
        return !empty($classAnnotations['tags'])
            ? (array)$classAnnotations['tags']
            : null;
    }

    /**
     * @param string $dependency
     * @return string
     */
    protected function formatDependency($dependency)
    {
        return in_array($dependency[0], array('%', '#')) ? $dependency : '@' . $dependency;
    }
}
