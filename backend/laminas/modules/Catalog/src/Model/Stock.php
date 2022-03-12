<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use ArrayObject;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой наличния товара на складе
 *
 */
class Stock extends AbstractModel
{
    /**
     * Выборка существующих в базе единиц измерения
     *
     * @return array
     */
    public function getUnits(): array
    {
        return $this->verticalSlice('unit', 'unit');
    }

    /**
     * Получение инофрмации о наличии наименования
     *
     * @param $item_id
     * @return ArrayObject|null
     */
    public function getForItem($item_id): ?ArrayObject
    {
        return $this->fetchOneBy('item', $item_id);
    }

    /**
     * Проверка доступности наименования
     *
     * @param $item_id
     * @return bool
     */
    public function isItemAvailable($item_id): bool
    {
        $stock = $this->fetchOne(['item' => $item_id], 'count desc');
        if ($stock && $stock->count > 0) {
            return true;
        }
        return false;
    }

    /**
     * Получение инофрмации о наличии торгового предложения
     *
     * @param $offer_id
     * @return ArrayObject|null
     */
    public function getForOffer($offer_id): ?ArrayObject
    {
        return $this->fetchOneBy('offer', $offer_id);
    }
}