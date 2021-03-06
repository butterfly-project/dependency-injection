<?php

namespace Butterfly\Component\DI\Compiler\ServiceCollector;

use Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException;
use Butterfly\Component\DI\Compiler\ServiceVisitor\IVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceCollector implements IVisitor, IConfigurationCollector
{
    /**
     * @var array
     */
    protected static $commonSections = array(
        'class',
        'factoryStaticMethod',
        'factoryMethod',
        'parent'
    );

    /**
     * @var array
     */
    protected static $unionSectionKeys = array(
        'calls',
        'properties',
        'preTriggers',
        'postTriggers',
    );

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
        if (!$this->checkCommonSections($configuration)) {
            return;
        }

        if (isset($configuration['parent'])) {
            $this->children[$serviceId] = $configuration;
        } else {
            $this->services[$serviceId] = $configuration;
        }
    }

    /**
     * @param array $configuration
     * @return bool
     */
    protected function checkCommonSections(array $configuration)
    {
        $commonSections = array_intersect(array_keys($configuration), self::$commonSections);

        return !empty($commonSections);
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

            $this->services[$serviceId] = $this->doMergeConfiguration($parentConfiguration, $configuration);
        }

        unset($this->children);
    }

    /**
     * @param array $parent
     * @param array $current
     * @return array
     */
    protected function doMergeConfiguration(array $parent, array $current)
    {
        $result = array_replace_recursive($parent, $current);

        foreach (self::$unionSectionKeys as $sectionKey) {
            $parentSection  = isset($parent[$sectionKey]) ? $parent[$sectionKey] : array();
            $currentSection = isset($current[$sectionKey]) ? $current[$sectionKey] : array();

            $result[$sectionKey] = array_merge($parentSection, $currentSection);
        }

        return $result;
    }
}
