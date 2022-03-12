<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtCms\User\Model\Users;
use MtModules\Catalog\Controller\OrdersController;
use MtModules\Catalog\Form\Order;
use MtModules\Catalog\Model\Cart;
use MtModules\Catalog\Model\Coupons;
use MtModules\Catalog\Model\Discount;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Orders;
use MtModules\Catalog\Model\Price;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OrdersControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return OrdersController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OrdersController
    {
        $form = new Order();
        $model = $container->get(Orders::class);
        $form->setInputFilter($model->getInputFilter());
        return new OrdersController(
            $container->get(Orders::class),
            $form,
            $container->get(Items::class),
            $container->get(Cart::class),
            $container->get('MailService'),
            $container->get('ViewRenderer'),
            $container->get(Offers::class),
            $container->get(OffersProperties::class),
            $container->get(OffersPropertiesValues::class),
            $container->get(Price::class),
            $container->get(Users::class),
            $container->get(Discount::class),
            $container->get(Coupons::class)
        );
    }
}