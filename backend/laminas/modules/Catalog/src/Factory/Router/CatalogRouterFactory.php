<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Router;

use MtModules\Catalog\Model\Categories;
use MtModules\Catalog\Model\Groups;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Router\CatalogRouter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CatalogRouterFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param  $requestedName
     * @param null|array $options
     * @return object
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): object
    {
        $router = CatalogRouter::factory($options);
        $router->setModelCategories($container->get(Categories::class));
        $router->setModelItems($container->get(Items::class));
        $router->setModelGroups($container->get(Groups::class));
        return $router;
    }
}