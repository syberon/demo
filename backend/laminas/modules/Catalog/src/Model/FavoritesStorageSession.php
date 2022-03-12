<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use Laminas\Session\Container;

/**
 * Модель работы с базой избранных товаров для неавторизованных пользователей
 */
class FavoritesStorageSession implements FavoritesStorageInterface
{
    const CONTAINER_INDEX = 'favorites';


    /** @var Container */
    protected Container $container;

    /** @var int */
    protected int $user_id;

    /**
     * FavoritesSession constructor.
     */
    public function __construct()
    {
        $this->container = new Container(self::class);
    }

    /**
     * Добавление товара в список избранного
     *
     * @param int $data
     * @return bool
     */
    public function insert(int $data): bool
    {
        if (!$this->container->offsetExists(self::CONTAINER_INDEX)) {
            $this->clear();
        }
        if (!$this->itemExist($data)) {
            $this->container[self::CONTAINER_INDEX][] = $data;
            return true;
        }
        return false;
    }

    /**
     * Проверка присутствия товара в списке избранных товаров
     *
     * @param int $item_id
     * @return bool
     */
    public function itemExist(int $item_id): bool
    {
        foreach ($this->container->offsetGet(self::CONTAINER_INDEX) as $item) {
            if ($item == $item_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Удаление товара из списка избарнного
     *
     * @param int $whereOrId
     * @param bool $sql
     */
    public function delete(int $whereOrId, bool $sql = false)
    {
        if ($this->itemExist($whereOrId)) {
            foreach ($this->container->offsetGet(self::CONTAINER_INDEX) as $key => $item) {
                if ($whereOrId == $item) {
                    unset($this->container[self::CONTAINER_INDEX][$key]);
                    break;
                }
            }
        }
    }

    /**
     * Очистка списка избранных товаров
     */
    public function clear()
    {
        $this->container->offsetSet(self::CONTAINER_INDEX, []);
    }

    /**
     * Получение количество наименований в списке избранных товаров
     *
     * @return int
     */
    public function getCount(): int
    {
        if ($this->container->offsetExists(self::CONTAINER_INDEX)) {
            return count($this->container->offsetGet(self::CONTAINER_INDEX));
        }
        return 0;
    }

    /**
     * Получение списка избранных товаров
     *
     * @return array
     */
    public function getList(): array
    {
        return $this->container->offsetGet(self::CONTAINER_INDEX);
    }
}