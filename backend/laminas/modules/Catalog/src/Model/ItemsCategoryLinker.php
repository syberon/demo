<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой привязки товара к категориям
 *
 */
class ItemsCategoryLinker extends AbstractModel
{
    /**
     * Привязка товара к категории
     *
     * @param int $item_id
     * @param int $category_id
     */
    public function linkItem(int $item_id, int $category_id)
    {
        $this->insert([
            'item'     => $item_id,
            'category' => $category_id
        ]);
    }

    /**
     * Получение списка привязанных к наименованию категорий
     *
     * @param int $item_id
     * @return array
     */
    public function getLinkedCategories(int $item_id): array
    {
        $links = $this->fetchBy('item', $item_id);
        $result = [];
        foreach ($links as $link) {
            $result[] = $link->category;
        }
        return $result;
    }

    /**
     * Удаление всех привязок товара к категориям
     *
     * @param int $item_id
     */
    public function clearLinks(int $item_id)
    {
        $this->delete(['item' => $item_id]);
    }
}