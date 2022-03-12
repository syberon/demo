<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtLib\SeoUrl\Service\SeoUrl;
use MtModules\Catalog\Controller\ItemsController;
use MtModules\Catalog\Form\Item;
use MtModules\Catalog\Model\Categories;
use MtModules\Catalog\Model\Currencies;
use MtModules\Catalog\Model\Groups;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\ItemsCategoryLinker;
use MtModules\Catalog\Model\ItemsProperties;
use MtModules\Catalog\Model\ItemsPropertiesValues;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Price;
use MtModules\Catalog\Model\Related;
use MtModules\Catalog\Model\Stock;
use MtModules\Catalog\Model\Sync;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemsControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return ItemsController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ItemsController
    {
        /** @var Item $form */
        $form = $container->get(Item::class);
        $model = $container->get(Items::class);
        $form->setInputFilter($model->getInputFilter());

        return new ItemsController(
            $form,
            $model,
            $container->get(ItemsProperties::class),
            $container->get(ItemsPropertiesValues::class),
            $container->get(Offers::class),
            $container->get(OffersProperties::class),
            $container->get(OffersPropertiesValues::class),
            $container->get(Stock::class),
            $container->get(Currencies::class),
            $container->get(SeoUrl::class),
            $container->get(ItemsCategoryLinker::class),
            $container->get(Price::class),
            $container->get(Pictures::class),
            $container->get(Categories::class),
            $container->get(Groups::class),
            $container->get(Related::class),
            $container->get(Sync::class)
        );
    }
}