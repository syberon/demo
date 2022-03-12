<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Model\ItemsPropertiesDefaults;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemsPropertiesDefaultsFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return ItemsPropertiesDefaults
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName,
                             array $options = null): ItemsPropertiesDefaults
    {
        $tableGateway = new TableGateway('catalog_items_properties_default_values', $container->get(Adapter::class));
        return new ItemsPropertiesDefaults($tableGateway);
    }
}