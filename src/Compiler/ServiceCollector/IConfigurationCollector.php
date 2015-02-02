<?php

namespace Butterfly\Component\DI\Compiler\ServiceCollector;

use Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException;

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
