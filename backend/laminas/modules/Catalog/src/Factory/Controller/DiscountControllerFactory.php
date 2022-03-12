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
use MtModules\Catalog\Controller\DiscountController;
use MtModules\Catalog\Form\Discount as DiscountForm;
use MtModules\Catalog\Model\Discount;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class DiscountControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return DiscountController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DiscountController
    {
        $form = new DiscountForm();
        $model = $container->get(Discount::class);
        $form->setInputFilter($model->getInputFilter());
        return new DiscountController($container->get(Discount::class), $form);
    }
}