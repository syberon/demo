<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Cdek\Factory\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use MtCms\Params\Model\System;
use MtModules\Cdek\Controller\IndexController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return IndexController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IndexController
    {
        return new IndexController(
            $container->get(System::class)
        );
    }
}