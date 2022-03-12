<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use MtModules\Catalog\Controller\CartController;
use MtModules\Catalog\Model\Cart;
use MtModules\Catalog\Model\Coupons;
use MtModules\Catalog\Model\Discount;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Stock;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\NotFoundExceptionInterface;

class CartControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return CartController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CartController
    {
        return new CartController(
            $container->get(Items::class),
            $container->get(Cart::class),
            $container->get(Offers::class),
            $container->get(Pictures::class),
            $container->get(Stock::class),
            $container->get(OffersPropertiesValues::class),
            $container->get(OffersProperties::class),
            $container->get(Discount::class),
            $container->get(Coupons::class)
        );
    }
}