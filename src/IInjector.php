<?php

namespace Butterfly\Component\DI;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
interface IInjector
{
    /**
     * @param Object $object
     * @return void
     */
    public function inject($object);
}
