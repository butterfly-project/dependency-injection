<?php

namespace Butterfly\Component\DI\Builder\ParameterResolver;

interface IConfigurationResolverAware
{
    /**
     * @param IConfigurationResolver $resolver
     */
    public function setResolver(IConfigurationResolver $resolver);
}
