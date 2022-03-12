<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\View\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\View\Helper\CatalogItemsWidget;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CatalogItemsWidgetFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return CatalogItemsWidget
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CatalogItemsWidget
    {
        return new CatalogItemsWidget(
            $container->get(Items::class),
            $container->get(Pictures::class),
            $container->get('config')
        );
    }
}