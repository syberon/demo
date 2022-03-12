<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\ItemsPropertiesController;
use MtModules\Catalog\Form\ItemsProperty;
use MtModules\Catalog\Model\ItemsProperties;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemsPropertiesControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return ItemsPropertiesController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName,
                             array $options = null): ItemsPropertiesController
    {
        $form = new ItemsProperty();
        $model = $container->get(ItemsProperties::class);
        $form->setInputFilter($model->getInputFilter());
        return new ItemsPropertiesController($model, $form);
    }
}