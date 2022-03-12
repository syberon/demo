<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\PicturesController;
use MtModules\Catalog\Form\Picture;
use MtModules\Catalog\Model\Pictures;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class PicturesControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return PicturesController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PicturesController
    {
        $form = new Picture();
        /** @var Pictures $model */
        $model = $container->get(Pictures::class);
        $form->setInputFilter($model->getInputFilter());
        return new PicturesController($model, $form);
    }
}