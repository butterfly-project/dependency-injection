<?php

namespace Syringe\Component\DI\Builder\ServiceCollector;

use Syringe\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException;

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
