<?php

namespace Syringe\Component\DI;

interface IInjector
{
    /**
     * @param Object $object
     * @return void
     */
    public function inject($object);
}
