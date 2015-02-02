<?php

namespace Butterfly\Component\DI\Compiler\ServiceVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
interface IVisitor
{
    /**
     * @param string $serviceId
     * @param array $configuration
     * @return void
     */
    public function visit($serviceId, array $configuration);

    /**
     * @return void
     */
    public function clean();
}
