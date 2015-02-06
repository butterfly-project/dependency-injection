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
        if (isset($configuration['alias'])) {
            $aliases = (array)$configuration['alias'];
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $this->aliases)) {
                    throw new InvalidConfigurationException(sprintf(
                        "Two services: '%s', '%s' have the same alias '%s'",
                        $this->aliases[$alias],
                        $serviceId,
                        $alias
                    ));
                }
                $this->aliases[$alias] = $serviceId;
            }
        }
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
