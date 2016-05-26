<?php

namespace Butterfly\Component\DI\Compiler\ServiceCollector;

use Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException;
use Butterfly\Component\DI\Compiler\ServiceVisitor\IVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class AliasCollector implements IVisitor, IConfigurationCollector
{
    /**
     * @var array
     */
    protected static $availableConstructions = array(
        'alias',
    );

    /**
     * @var array
     */
    protected $aliases = array();

    /**
     * @return void
     */
    public function clean()
    {
        $this->aliases = array();
    }

    /**
     * @param string $serviceId
     * @param array $configuration
     * @return void
     * @throws InvalidConfigurationException
     */
    public function visit($serviceId, array $configuration)
    {
        if (!$this->isAliasConfiguration($configuration)) {
            return;
        }

        $pointTo = $configuration['alias'];

        $existingAlias = array_search($pointTo, $this->aliases);
        if (false !== $existingAlias) {
            throw new InvalidConfigurationException(sprintf(
                "Two aliases: '%s', '%s' point to service '%s'",
                $existingAlias,
                $serviceId,
                $pointTo
            ));
        }

        $this->aliases[$serviceId] = $pointTo;
    }

    /**
     * @param array $configuration
     * @return bool
     */
    protected function isAliasConfiguration(array $configuration)
    {
        if (empty($configuration['alias']) || !is_string($configuration['alias'])) {
            return false;
        }

        $otherConstructions = array_diff(array_keys($configuration), self::$availableConstructions);
        if (!empty($otherConstructions)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return 'aliases';
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->aliases;
    }
}
