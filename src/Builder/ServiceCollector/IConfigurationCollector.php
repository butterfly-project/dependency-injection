<?php

namespace Butterfly\Component\DI\Builder\ServiceCollector;

use Butterfly\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
interface IConfigurationCollector
{
    /**
     * @return string
     * @throws InvalidConfigurationException
     */
    public function getSection();

    /**
     * @return array
     * @throws InvalidConfigurationException
     */
    public function getConfiguration();
}
