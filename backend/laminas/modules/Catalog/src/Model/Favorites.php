<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

/**
 * Модель работы с базой избранных товаров
 *
 */
class Favorites
{
    /**
     * Favorites constructor.
     * @param FavoritesStorageInterface $storage
     */
    public function __construct(protected FavoritesStorageInterface $storage)
    {
    }

    /**
     * Добавление товара в список избранного
     *
     * @param int $item_id
     * @return bool
     */
    public function insert(int $item_id): bool
    {
        return $this->storage->insert($item_id);
    }

    /**
     * Удаление товара из списка избарнного
     *
     * @param int $item_id
     */
    public function delete(int $item_id)
    {
        $this->storage->delete($item_id);
    }

    /**
     * Очистка списка избранных товаров
     */
    public function clear()
    {
        $this->storage->clear();
    }

    /**
     * Получение количество наименований в списке избранных товаров
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->storage->getCount();
    }

    /**
     * Получение списка избранных товаров
     *
     * @return array
     */
    public function getList(): array
    {
        return $this->storage->getList();
    }

}