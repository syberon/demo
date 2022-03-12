<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой избранных товаров для авторизованных пользователей
 *
 */
class FavoritesStorageDb extends AbstractModel implements FavoritesStorageInterface
{
    protected int $user_id;

    /**
     * Установка текущего пользователя
     *
     * @param int $user_id
     */
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Добавление товара в список избранного
     *
     * @param mixed $data
     * @return bool
     */
    public function insert($data): bool
    {
        if (!$this->itemExist($data)) {
            return parent::insert([
                'item' => $data,
                'user' => $this->user_id
            ]);
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
        $item = $this->fetchOne([
            'user' => $this->user_id,
            'item' => $item_id
        ]);

        return (bool)$item;
    }

    /**
     * Удаление товара из списка избарнного
     *
     * @param mixed $whereOrId
     * @param bool $sql
     * @return int
     */
    public function delete($whereOrId, bool $sql = false): int
    {
        return parent::delete([
            'user' => $this->user_id,
            'item' => $whereOrId
        ]);
    }

    /**
     * Очистка списка избранных товаров
     */
    public function clear()
    {
        parent::delete([
            'user' => $this->user_id
        ]);
    }

    /**
     * Получение количество наименований в списке избранных товаров
     *
     * @return int
     */
    public function getCount(): int
    {
        return count($this->getList());
    }

    /**
     * Получение списка избранных товаров
     *
     * @return array
     */
    public function getList(): array
    {
        return $this->verticalSlice('item', 'id', ['user' => $this->user_id]);
    }
}