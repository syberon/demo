<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use MtLib\Image\Imagine\Filter\FilterManager;
use MtLib\Image\Imagine\Loader\LoaderManager;
use MtLib\Image\Service\CacheManager;
use MtModules\Catalog\Entity\Category;
use MtModules\Catalog\Model\Categories;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CategoriesFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Categories
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Categories
    {
        $categoryEntity = new Category();
        $categoryEntity->setViewHelperManager($container->get('ViewHelperManager'));
        $categoryEntity->setConfig($container->get('configuration'));
        $categoryEntity->setUploadPlugin($container->get('ControllerPluginManager')->get('uploads'));
        $categoryEntity->setCacheManager($container->get(CacheManager::class));
        $categoryEntity->setFilterManager($container->get(FilterManager::class));
        $categoryEntity->setLoaderManager($container->get(LoaderManager::class));

        $uploadsPlugin = $container->get('ControllerPluginManager')->get('uploads');

        $resultSet = new HydratingResultSet(new ReflectionHydrator(), $categoryEntity);
        $tableGateway = new TableGateway('catalog_categories', $container->get(Adapter::class), null, $resultSet);
        $model = new Categories($tableGateway, $uploadsPlugin);
        $model->setEventManager($container->get('EventManager'));
        $model->getEventManager()->attach('insert.post', [$model, 'regenerateIndexes']);
        $model->getEventManager()->attach('insert.post', [$model, 'regenerateUrl']);
        $model->getEventManager()->attach('update.post', [$model, 'regenerateIndexes']);
        $model->getEventManager()->attach('update.post', [$model, 'regenerateUrl']);
        $model->getEventManager()->attach('delete.post', [$model, 'regenerateIndexes']);
        return $model;
    }
}