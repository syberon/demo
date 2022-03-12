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
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\Pictures;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemsFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Items
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Items
    {
        $config = $container->get('config');
        $tableGateway = new TableGateway('catalog_items', $container->get(Adapter::class));

        $model = new Items($tableGateway, $config);
        $model->setEventManager($container->get('EventManager'));

        // Удаление прикрепленных изображений для наименования
        /** @var Pictures $modelPictures */
        $modelPictures = $container->get(Pictures::class);
        $model->getEventManager()->attach('delete.pre', [$modelPictures, 'deletePictures']);

        // Удаление прикрепленных изображений для торговых предложений
        /** @var Offers $modelOffers */
        $modelOffers = $container->get(Offers::class);
        $model->getEventManager()->attach('delete.pre', [$modelOffers, 'deletePictures']);

        return $model;
    }
}