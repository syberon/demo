<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\GroupsController;
use MtModules\Catalog\Form\Group;
use MtModules\Catalog\Model\Groups;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GroupsControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return GroupsController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GroupsController
    {
        $form = new Group();
        $model = $container->get(Groups::class);
        $form->setInputFilter($model->getInputFilter());
        return new GroupsController($container->get(Groups::class), $form);
    }
}