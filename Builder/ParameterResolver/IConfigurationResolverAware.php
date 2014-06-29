<?php

namespace Syringe\Component\DI\Builder\ParameterResolver;

interface IConfigurationResolverAware
{
    /**
     * @param IConfigurationResolver $resolver
     */
    public function setResolver(IConfigurationResolver $resolver);
}
