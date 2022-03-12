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
use MtLib\SeoUrl\Service\SeoUrl;
use MtModules\Catalog\Controller\CategoriesController;
use MtModules\Catalog\Form\Category;
use MtModules\Catalog\Model\Categories;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CategoriesControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return CategoriesController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CategoriesController
    {
        $form = new Category();
        $model = $container->get(Categories::class);
        $form->setInputFilter($model->getInputFilter());
        return new CategoriesController(
            $container->get(Categories::class),
            $form,
            $container->get(SeoUrl::class)
        );
    }
}