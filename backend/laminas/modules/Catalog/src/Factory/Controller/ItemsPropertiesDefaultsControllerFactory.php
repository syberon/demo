<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\ItemsPropertiesDefaultsController;
use MtModules\Catalog\Form\ItemsPropertyDefault;
use MtModules\Catalog\Model\ItemsProperties;
use MtModules\Catalog\Model\ItemsPropertiesDefaults;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemsPropertiesDefaultsControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return ItemsPropertiesDefaultsController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName,
                             array $options = null): ItemsPropertiesDefaultsController
    {
        $form = new ItemsPropertyDefault();
        $model = $container->get(ItemsPropertiesDefaults::class);
        $form->setInputFilter($model->getInputFilter());
        return new ItemsPropertiesDefaultsController(
            $model,
            $form,
            $container->get(ItemsProperties::class)
        );
    }
}