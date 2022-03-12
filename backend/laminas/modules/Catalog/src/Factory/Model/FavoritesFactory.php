<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Model;

use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Model\Favorites;
use MtModules\Catalog\Model\FavoritesStorageDb;
use MtModules\Catalog\Model\FavoritesStorageSession;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class FavoritesFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Favorites
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Favorites
    {
        /** @var AuthenticationService $authService */
        $authService = $container->get('AuthenticationService');

        if ($authService->hasIdentity()) {
            $tableGateway = new TableGateway('catalog_favorites', $container->get(Adapter::class));
            $storage = new FavoritesStorageDb($tableGateway);
            $storage->setUserId($authService->getIdentity()->id);
        } else {
            $storage = new FavoritesStorageSession();
        }

        return new Favorites($storage);
    }
}