<?php

namespace Butterfly\Component\DI\Builder\ServiceCollector;

use Butterfly\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException;
use Butterfly\Component\DI\Builder\ServiceVisitor\IVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceCollector implements IVisitor, IConfigurationCollector
{
    /**
     * @var array
     */
    protected $services = array();

    /**
     * @var array
     */
    protected $children = array();

    /**
     * @return void
     */
    public function clean()
    {
        $this->services = array();
        $this->children = array();
    }

    /**
     * @param string $serviceId
     * @param array $configuration
     * @return void
     */
    public function visit($serviceId, array $configuration)
    {
        if (isset($configuration['parent'])) {
            $this->children[$serviceId] = $configuration;
        } else {
            $this->services[$serviceId] = $configuration;
        }
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return 'services';
    }

    /**
     * @return array
     * @throws InvalidConfigurationException if parent service is not found
     */
    public function getConfiguration()
    {
        $this->mergeConfiguration();

        return $this->services;
    }

    /**
     * @return void
     * @throws InvalidConfigurationException if parent service is not found
     */
    protected function mergeConfiguration()
    {
        foreach ($this->children as $serviceId => $configuration) {
            if (empty($this->services[$configuration['parent']])) {
                throw new InvalidConfigurationException(sprintf(
                    "Parent service '%s' is not found",
                    $configuration['parent']
                ));
            }

            $parentConfiguration = $this->services[$configuration['parent']];

            unset($configuration['parent']);
            unset($parentConfiguration['tags']);
            unset($parentConfiguration['alias']);

            $this->services[$serviceId] = array_replace_recursive($parentConfiguration, $configuration);
        }

        unset($this->children);
    }
}
