<?php

namespace Butterfly\Component\DI\Compiler\PreProcessing;

use Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException;
use Butterfly\Component\Form\Transform\ITransformer;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceFilter implements ITransformer
{
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
     * @param mixed $value
     * @return mixed
     * @throws \InvalidArgumentException if incorrect value type
     */
    public function transform($value)
    {
        $this->services = array();
        $this->children = array();

        foreach ($value as $serviceId => $serviceConfiguration) {
            if (isset($serviceConfiguration['parent'])) {
                $this->children[$serviceId] = $serviceConfiguration;
            } else {
                $this->services[$serviceId] = $serviceConfiguration;
            }
        }

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
