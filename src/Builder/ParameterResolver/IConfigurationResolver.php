<?php

namespace Butterfly\Component\DI\Builder\ParameterResolver;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
interface IConfigurationResolver
{
    /**
     * @param array $configuration
     * @return array
     */
    public function resolve(array $configuration);
}
