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
use MtModules\Catalog\Model\Stock;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class StockFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Stock
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Stock
    {
        $tableGateway = new TableGateway('catalog_stock', $container->get(Adapter::class));
        return new Stock($tableGateway);
    }
}