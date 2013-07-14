<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Syringe\Component\DI;

/**
 * A simple implementation of IContainerAware.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
abstract class ContainerAware implements IContainerAware
{
    /**
     * @var IContainer
     *
     * @api
     */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param IContainer $container A IContainer instance
     *
     * @api
     */
    public function setContainer(Container $container = null)
    {
        $this->container = $container;
    }
}
