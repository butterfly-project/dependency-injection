<?php

namespace Butterfly\Component\DI\Compiler\PreProcessing;

interface IFilter
{
    /**
     * @param array $configuration
     * @return array
     */
    public function filter(array $configuration);
}
