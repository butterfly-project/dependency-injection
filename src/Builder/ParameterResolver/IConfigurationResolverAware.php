<?php

namespace Butterfly\Component\DI\Builder\ParameterResolver;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
interface IConfigurationResolverAware
{
    /**
     * @param IConfigurationResolver $resolver
     */
    public function setResolver(IConfigurationResolver $resolver);
}
