<?php

namespace Butterfly\Component\DI\Compiler;

use Butterfly\Component\DI\Compiler\PreProcessing\IFilter;
use Butterfly\Component\DI\Compiler\PreProcessing\ParameterResolver\Resolver;
use Butterfly\Component\DI\Compiler\ServiceCollector\ServiceCollector;
use Butterfly\Component\DI\Compiler\ServiceCollector\TagCollector;
use Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException;
use Butterfly\Component\DI\Compiler\ServiceVisitor\IVisitor;
use Butterfly\Component\DI\Compiler\ServiceCollector\IConfigurationCollector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ConfigCompiler
{
    const SECTION_SERVICES   = 'services';
    const SECTION_INTERFACES = 'interfaces';
    const SECTION_PARAMETERS = 'parameters';

    /**
     * @var Resolver
     */
    protected $resolver = null;

    /**
     * @var IVisitor[]
     */
    protected $visitors = array();

    /**
     * @var IFilter[]
     */
    protected $filters = array();

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @param array $configuration
     * @return array
     */
    public static function compile(array $configuration)
    {
        return static::createInstance()->compileConfig($configuration);
    }

    /**
     * @return static
     */
    public static function createInstance()
    {
        return new static(array(
            new Resolver(),
            new ServiceCollector(),
            new TagCollector(),
        ));
    }

    /**
     * @param IFilter[] $filters
     */
    public function __construct(array $filters = array())
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function addFilter(IFilter $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @param array $configuration
     * @return array
     * @throws InvalidConfigurationException if incorrect interface configuration
     * @throws InvalidConfigurationException if IoCC configuration is invalid
     */
    public function compileConfig(array $configuration)
    {
        foreach ($this->filters as $filter) {
            $configuration = $filter->filter($configuration);
        }


//        $configuration = $this->prepareConfiguration($configuration);
//        $configuration = $this->prepareInterfaceConfiguration($configuration);

        $this->cleanVisitors($this->visitors);
        $this->runServiceVisits($this->visitors, $configuration);

        return array_merge($configuration, $this->resolveServiceConfiguration($this->visitors));
    }

    /**
     * @param IVisitor[] $visitors
     */
    protected function cleanVisitors(array $visitors)
    {
        foreach ($visitors as $visitor) {
            $visitor->clean();
        }
    }

    /**
     * @param array $configuration
     * @param string $name
     * @return array
     */
    private function getSection(array $configuration, $name)
    {
        return isset($configuration[$name]) ? $configuration[$name] : array();
    }

    /**
     * @param array $configuration
     * @return array
     * @throws InvalidConfigurationException if incorrect interface configuration
     */
    protected function prepareInterfaceConfiguration(array $configuration)
    {
        $interfaces = array();
        $aliases    = array();

        foreach ($configuration['interfaces'] as $interfaceName => $interfaceConfig) {
            $interfaces[$interfaceName] = $this->getInterfaceImplementation($interfaceName, $interfaceConfig);
            $interfaceAliases           = $this->getInterfaceAliases($interfaceConfig);

            foreach ($interfaceAliases as $interfaceAlias) {
                $aliases[$interfaceAlias] = $interfaceName;
            }
        }

        $configuration['interfaces']         = $interfaces;
        $configuration['interfaces_aliases'] = $aliases;

        return $configuration;
    }

    /**
     * @param string $name
     * @param mixed $config
     * @return string
     * @throws InvalidConfigurationException if incorrect interface configuration
     */
    protected function getInterfaceImplementation($name, $config)
    {
        if (!is_array($config)) {
            return (string)$config;
        }

        if (!array_key_exists('service', $config)) {
            throw new InvalidConfigurationException(sprintf(
                "Incorrect '%s' interface configuration: no 'service' key in %s",
                $name, var_export($config, true)
            ));
        }

        return $config['service'];
    }

    /**
     * @param mixed $config
     * @return array
     */
    protected function getInterfaceAliases($config)
    {
        if (!is_array($config) || !array_key_exists('alias', $config)) {
            return array();
        }

        return (array)$config['alias'];
    }

    /**
     * @param IVisitor[] $visitors
     * @param array $configuration
     * @throws InvalidConfigurationException if IoCC configuration is invalid
     */
    protected function runServiceVisits(array $visitors, array $configuration)
    {
        $errors = array();

        foreach ($configuration as $serviceId => $serviceConfiguration) {
            foreach ($visitors as $visitor) {
                try {
                    $visitor->visit($serviceId, $serviceConfiguration);
                } catch (InvalidConfigurationException $e) {
                    $errors[] = $e;
                }
            }
        }

        if (!empty($errors)) {
            throw new InvalidConfigurationException('IoCC configuration is invalid', $errors);
        }
    }

    /**
     * @param IVisitor[] $visitors
     * @return array
     */
    protected function resolveServiceConfiguration(array $visitors)
    {
        $containerConfiguration = array();

        foreach ($visitors as $visitor) {
            if (!($visitor instanceof IConfigurationCollector)) {
                continue;
            }

            if ($visitor->getSection()) {
                $containerConfiguration[$visitor->getSection()] = $visitor->getConfiguration();
            } else {
                $containerConfiguration = array_merge($containerConfiguration, $visitor->getConfiguration());
            }
        }

        return $containerConfiguration;
    }
}
