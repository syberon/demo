<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Model;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use MtModules\Catalog\Model\Groups;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\NotFoundExceptionInterface;

class GroupsFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Groups
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Groups
    {
        $tableGateway = new TableGateway('catalog_groups', $container->get(Adapter::class));
        return new Groups($tableGateway);
    }
}