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
use MtModules\Catalog\Controller\CurrenciesController;
use MtModules\Catalog\Form\Currency;
use MtModules\Catalog\Model\Currencies;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CurrenciesControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return CurrenciesController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CurrenciesController
    {
        $form = new Currency();
        $model = $container->get(Currencies::class);
        $form->setInputFilter($model->getInputFilter());
        return new CurrenciesController($container->get(Currencies::class), $form);
    }
}