<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\CouponsController;
use MtModules\Catalog\Form\Coupon;
use MtModules\Catalog\Model\Coupons;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CouponsControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return CouponsController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CouponsController
    {
        $form = new Coupon();
        $model = $container->get(Coupons::class);
        $form->setInputFilter($model->getInputFilter());
        return new CouponsController($container->get(Coupons::class), $form);
    }
}