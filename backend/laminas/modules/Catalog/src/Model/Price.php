<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use ArrayObject;
use MtLib\Base\Model\AbstractModel;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Stdlib\ArrayUtils;

/**
 * Модель работы с базой цен товаров
 *
 */
class Price extends AbstractModel
{
    /**
     * Получение инофрмации о цене наименования
     *
     * @param $item_id
     * @return ArrayObject|null
     */
    public function getForItem($item_id): ?ArrayObject
    {
        return $this->fetchOneBy('item', $item_id);
    }

    /**
     * Получение минимальной цены для наименования
     *
     * @param int $item_id
     * @return ArrayObject|null
     */
    public function getMinForItem(int $item_id): ?ArrayObject
    {
        $select = new Select();
        $select->from('catalog_price')
            ->columns([
                'price'          => new Expression(
                    'CASE
                     WHEN MIN(catalog_price.price_sale) > 0
                        THEN LEAST(MIN(catalog_price.price), MIN(catalog_price.price_sale))
                     ELSE MIN(catalog_price.price) END'),
                'price_base'     => new Expression('MIN(catalog_price.price)'),
                'price_sale'     => new Expression('MIN(catalog_price.price_sale)'),
                'price_discount' => new Expression('MIN(catalog_price.price) - MIN(catalog_price.price_sale)')
            ])
            ->limit(1)
            ->where->equalTo('item', $item_id);

        $resultSet = $this->table->selectWith($select);
        $result = ArrayUtils::iteratorToArray($resultSet, false);
        return count($result) ? $result[0] : null;
    }

    /**
     * Получение инофрмации о цене торгового предложения
     *
     * @param $offer_id
     * @return ArrayObject|null
     */
    public function getForOffer($offer_id): ?ArrayObject
    {
        return $this->fetchOneBy('offer', $offer_id);
    }
}