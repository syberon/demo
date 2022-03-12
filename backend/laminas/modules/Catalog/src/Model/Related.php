<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use JetBrains\PhpStorm\Pure;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Stdlib\ArrayUtils;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой рекомендуемых товаров
 *
 */
class Related extends AbstractModel
{
    /**
     * @param TableGatewayInterface $tableGateway
     * @param array $config
     */
    #[Pure]
    public function __construct(
        TableGatewayInterface $tableGateway,
        protected array $config
    )
    {
        parent::__construct($tableGateway);
    }

    /**
     * Получение инофрмации о цене наименования
     *
     * @param int $item_id
     * @param bool $unfiltered
     * @return array
     */
    public function getForItem(int $item_id, bool $unfiltered = false): array
    {
        $select = new Select();
        $select->from('catalog_related')
            ->group('id')
            ->columns(['*'])
            ->where->equalTo('catalog_related.item', $item_id);

        $select->join('catalog_items', 'catalog_items.id = catalog_related.related', ['name', 'article', 'active']);

        if (!$unfiltered) {
            $select->where->equalTo('catalog_items.active', 1);

            // Если используется информация о наличии товара на складе
            if ($this->config['catalog']['options']['use-stock'] &&
                !$this->config['catalog']['options']['display-out-of-stock']) {
                $select->join('catalog_stock', 'catalog_items.id = catalog_stock.item', []);
                $select->where->greaterThan('catalog_stock.count', 0);
            }

            // Добавление в выборку поля с минимальной ценой наименования или торгового предложения
            $select->join('catalog_price', 'catalog_items.id = catalog_price.item', [
                'price'          => new Expression(
                    'CASE
                     WHEN MIN(catalog_price.price_sale) > 0
                        THEN LEAST(MIN(catalog_price.price), MIN(catalog_price.price_sale))
                     ELSE MIN(catalog_price.price) END'),
                'price_base'     => new Expression('MIN(catalog_price.price)'),
                'price_sale'     => new Expression('MIN(catalog_price.price_sale)'),
                'price_discount' => new Expression('MIN(catalog_price.price) - MIN(catalog_price.price_sale)')
            ]);
        }

        $resultSet = $this->table->selectWith($select);
        return ArrayUtils::iteratorToArray($resultSet, false);

    }

    /**
     * Очистка всех привязок к товару
     *
     * @param int $item_id
     */
    public function clear(int $item_id)
    {
        $this->delete(['item' => $item_id]);
    }
}