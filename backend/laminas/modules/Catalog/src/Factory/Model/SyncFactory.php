<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Model;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtLib\SeoUrl\Service\SeoUrl;
use MtModules\Catalog\Model\Categories;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\ItemsCategoryLinker;
use MtModules\Catalog\Model\ItemsProperties;
use MtModules\Catalog\Model\ItemsPropertiesValues;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Price;
use MtModules\Catalog\Model\Stock;
use MtModules\Catalog\Model\Sync;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SyncFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Sync
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Sync
    {
        $uploadsPlugin = $container->get('ControllerPluginManager')->get('uploads');
        return new Sync(
            $container->get(Items::class),
            $container->get(Offers::class),
            $container->get(ItemsCategoryLinker::class),
            $container->get(ItemsPropertiesValues::class),
            $container->get(Price::class),
            $container->get(Stock::class),
            $container->get(OffersPropertiesValues::class),
            $container->get(Categories::class),
            $container->get(ItemsProperties::class),
            $container->get(OffersProperties::class),
            $container->get(Pictures::class),
            $container->get(SeoUrl::class),
            $container->get('config'),
            $uploadsPlugin
        );
    }
}