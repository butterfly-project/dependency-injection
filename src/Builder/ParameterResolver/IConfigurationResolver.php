<?php

namespace Butterfly\Component\DI\Builder\ParameterResolver;

interface IConfigurationResolver
{
    /**
     * @param array $configuration
     * @return array
     */
    public function resolve(array $configuration);
}
