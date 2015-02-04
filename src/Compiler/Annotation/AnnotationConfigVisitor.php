<?php

namespace Butterfly\Component\DI\Compiler\Annotation;

use Butterfly\Component\Annotations\Visitor\IAnnotationVisitor;

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
        if (!array_key_exists('service', $classAnnotations['class'])) {
            return;
        }

        $this->convertAnnotationsToConfig($className, $classAnnotations);
    }

    /**
     * @param string $className
     * @param array $classAnnotations
     */
    protected function convertAnnotationsToConfig($className, array $classAnnotations)
    {
        $config = array(
            'class' => $className,
        );

        $reflectionClass = new ReflectionClass($className);

        list($arguments, $calls) = $this->getMethods($classAnnotations, $reflectionClass);
        $config = $this->addSectionIfNotEmpty($config, 'arguments', $arguments);
        $config = $this->addSectionIfNotEmpty($config, 'calls', $calls);

        $config = $this->addSectionIfNotEmpty($config, 'properties', $this->getProperties($classAnnotations, $reflectionClass));
        $config = $this->addSectionIfNotEmpty($config, 'alias', $this->getAliases($classAnnotations, $className));
        $config = $this->addSectionIfNotEmpty($config, 'scope', $this->getScope($classAnnotations));
        $config = $this->addSectionIfNotEmpty($config, 'tags', $this->getTags($classAnnotations));

        $serviceName = $this->getServiceName($className, $classAnnotations);

        $this->services[$serviceName] = $config;
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
                $arguments[] = ('%' != $dependency[0]) ? '@' . $dependency : $dependency;
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
        $serviceName = $className;

        if (!empty($classAnnotations['class']['service'])) {
            $serviceName = $classAnnotations['class']['service'];
        }

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
     * @param string $className
     * @return array
     */
    protected function getAliases(array $classAnnotations, $className)
    {
        $aliases = array();

        if (null !== $classAnnotations['class']['service']) {
            $aliases[] = strtolower($className);
        }

        return $aliases;
    }

    /**
     * @param array $classAnnotations
     * @return string|null
     */
    protected function getScope(array $classAnnotations)
    {
        return !empty($classAnnotations['class']['scope'])
            ? (string)$classAnnotations['class']['scope']
            : null;
    }

    /**
     * @param array $classAnnotations
     * @return array|null
     */
    protected function getTags(array $classAnnotations)
    {
        return !empty($classAnnotations['class']['tags'])
            ? (array)$classAnnotations['class']['tags']
            : null;
    }
}
