<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Form;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Form\User;
use Psr\Container\ContainerInterface;

class UserFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return User
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): User
    {
        return new User();
    }
}