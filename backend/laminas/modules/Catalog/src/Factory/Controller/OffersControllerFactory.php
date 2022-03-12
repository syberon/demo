<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\OffersController;
use MtModules\Catalog\Form\Offer;
use MtModules\Catalog\Model\Currencies;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Price;
use MtModules\Catalog\Model\Stock;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OffersControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return OffersController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OffersController
    {
        /** @var Offer $form */
        $form = $container->get(Offer::class);
        $model = $container->get(Offers::class);
        $form->setInputFilter($model->getInputFilter());

        return new OffersController(
            $form,
            $model,
            $container->get(OffersProperties::class),
            $container->get(OffersPropertiesValues::class),
            $container->get(Items::class),
            $container->get(Stock::class),
            $container->get(Currencies::class),
            $container->get(Price::class),
            $container->get(Pictures::class)
        );
    }
}