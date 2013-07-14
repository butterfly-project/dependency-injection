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
 * IContainerAware should be implemented by classes that depends on a Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface IContainerAware
{
    /**
     * Sets the Container.
     *
     * @param Container|null $container A IContainer instance or null
     *
     * @api
     */
    public function setContainer(Container $container = null);
}
