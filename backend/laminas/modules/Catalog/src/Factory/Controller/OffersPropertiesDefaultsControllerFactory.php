<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\OffersPropertiesDefaultsController;
use MtModules\Catalog\Form\OffersPropertyDefault;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesDefaults;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OffersPropertiesDefaultsControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return OffersPropertiesDefaultsController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName,
                             array $options = null): OffersPropertiesDefaultsController
    {
        $form = new OffersPropertyDefault();
        $model = $container->get(OffersPropertiesDefaults::class);
        $form->setInputFilter($model->getInputFilter());
        return new OffersPropertiesDefaultsController(
            $model,
            $form,
            $container->get(OffersProperties::class)
        );
    }
}