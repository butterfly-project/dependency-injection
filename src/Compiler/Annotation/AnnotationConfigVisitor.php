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
    protected $services;

    public function __construct()
    {
        $this->clean();
    }

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

        $this->resolveService($className, $classAnnotations);
    }

    /**
     * @param string $className
     * @param array $classAnnotation
     */
    protected function resolveService($className, array $classAnnotation)
    {
        $configuration = array();

        if (null !== $classAnnotation['class']['service']) {
            $serviceName = $classAnnotation['class']['service'];
            $configuration['alias'] = strtolower($className);
        } else {
            $serviceName = $className;
        }

        $serviceName = strtolower($serviceName);

        $configuration['class'] = $className;

        $reflectionClass = new ReflectionClass($className);

        foreach ($classAnnotation['properties'] as $propertyName => $propertyAnnotation) {
            if (!array_key_exists('autowired', $propertyAnnotation)) {
                continue;
            }

            if (is_array($propertyAnnotation['autowired'])) {
                throw new \RuntimeException(sprintf("Incorrect @autowired value in %s property. Expected service name (string type), array given.", $propertyName));
            } elseif (null === $propertyAnnotation['autowired']) {
                $namespace = $reflectionClass->getFullNamespace($propertyAnnotation['var']);
                $innerServiceName = substr($namespace, 1);
                $configuration['properties'][$propertyName] = strtolower($innerServiceName);
            } else {
                $words = explode(' ', $propertyAnnotation['autowired']);
                $innerServiceName = reset($words);
                $configuration['properties'][$propertyName] = $innerServiceName;
            }
        }

        foreach ($classAnnotation['methods'] as $methodName => $methodAnnotation) {
            if (!array_key_exists('autowired', $methodAnnotation)) {
                continue;
            }

            $arguments = null;
            if (null === $methodAnnotation['autowired']) {
                $reflectionMethod = $reflectionClass->getMethod($methodName);

                $arguments = $this->getMethodTypesForNative($reflectionMethod->getParameters());

                if (null === $arguments && !empty($methodAnnotation['param'])) {
                    $arguments = array();
                    foreach ($methodAnnotation['param'] as $value) {
                        $words         = array_filter(explode(' ', $value));
                        $shortType     = array_shift($words);
                        $fullNamespace = strtolower($reflectionClass->getFullNamespace($shortType));
                        $arguments[]   = '@' . substr($fullNamespace, 1);
                    }
                }
            } elseif (is_array($methodAnnotation['autowired'])) {
                $arguments = array();

                foreach ($methodAnnotation['autowired'] as $dependency) {
                    $arguments[] = ('%' != $dependency[0]) ? '@' . $dependency : $dependency;
                }
            }

            if (null !== $arguments) {
                if ('__construct' == $methodName) {
                    $configuration['arguments'] = $arguments;
                } else {
                    $configuration['calls'][] = array($methodName, $arguments);
                }
            }
        }

        if (!empty($classAnnotation['class']['scope'])) {
            $configuration['scope'] = (string)$classAnnotation['class']['scope'];
        }


        if (!empty($classAnnotation['class']['tags'])) {
            $configuration['tags'] = (array)$classAnnotation['class']['tags'];
        }

        $this->services[$serviceName] = $configuration;
    }

    /**
     * @param \ReflectionParameter[] $reflectionParameters
     * @return array|null
     */
    protected function getMethodTypesForNative(array $reflectionParameters)
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
}
