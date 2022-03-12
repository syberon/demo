<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;


/**
 * Интерфейс работы с базой избранных товаров
 *
 */
interface FavoritesStorageInterface
{
    /**
     * Добавление товара в список избранного
     *
     * @param int $data
     * @return bool
     */
    public function insert(int $data): bool;

    /**
     * Проверка присутствия товара в списке избранных товаров
     *
     * @param int $item_id
     * @return bool
     */
    public function itemExist(int $item_id): bool;

    /**
     * Удаление товара из списка избарнного
     *
     * @param int $whereOrId
     * @param bool $sql
     */
    public function delete(int $whereOrId, bool $sql = false);

    /**
     * Очистка списка избранных товаров
     */
    public function clear();

    /**
     * Получение количество наименований в списке избранных товаров
     *
     * @return int
     */
    public function getCount(): int;

    /**
     * Получение списка избранных товаров
     *
     * @return array
     */
    public function getList(): array;
}