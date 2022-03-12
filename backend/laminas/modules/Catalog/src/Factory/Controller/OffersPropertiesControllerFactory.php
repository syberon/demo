<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\OffersPropertiesController;
use MtModules\Catalog\Form\OffersProperty;
use MtModules\Catalog\Model\OffersProperties;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OffersPropertiesControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return OffersPropertiesController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName,
                             array $options = null): OffersPropertiesController
    {
        $form = new OffersProperty();
        $model = $container->get(OffersProperties::class);
        $form->setInputFilter($model->getInputFilter());
        return new OffersPropertiesController($model, $form);
    }
}