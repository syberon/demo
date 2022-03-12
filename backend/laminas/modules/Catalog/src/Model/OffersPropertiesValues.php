<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use JetBrains\PhpStorm\Pure;
use MtLib\Base\Model\AbstractModel;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGatewayInterface;

/**
 * Модель работы с базой значений свойств торговых предложений
 *
 */
class OffersPropertiesValues extends AbstractModel
{
    /**
     * Конструктор класса
     *
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
     * Удаление значений для синхронизинуемых свойств
     *
     * @param int $offer_id
     */
    public function clearSyncValues(int $offer_id)
    {
        // Выборка свойств, с установленным идентификатором синхронизации
        $syncProperties = new Select();
        $syncProperties->columns(['id']);
        $syncProperties->from('catalog_offers_properties')
            ->where->notEqualTo('guid', '');

        $where = new Where();
        $where->equalTo('offer', $offer_id);
        $where->in('property', $syncProperties);
        $this->delete($where);
    }


    /**
     * Выборка уникальных значений свойства
     *
     * @param int $property
     * @return array
     */
    public function fetchUniqueValues(int $property): array
    {
        $select = new Select();
        $select->from('catalog_offers_properties_values')
            ->columns(['value'])
            ->where(['property' => $property])
            ->order([new Expression('CAST(value AS UNSIGNED)'), 'value'])
            ->quantifier('DISTINCT');

        $resultSet = $this->table->selectWith($select);

        $result = [];
        foreach ($resultSet as $item) {
            $result[] = $item->value;
        }
        return $result;
    }

    /**
     * Получение массива значений свойств для торгового предложения
     *
     * @param int $offer_id
     * @return array
     */
    public function fetchValuesForOffer(int $offer_id): array
    {
        $select = new Select();
        $select->from('catalog_offers_properties_values')
            ->columns(['value'])
            ->join('catalog_offers_properties',
                'catalog_offers_properties.id = catalog_offers_properties_values.property',
                ['code' => 'code'])
            ->where(['offer' => $offer_id]);

        $resultSet = $this->table->selectWith($select);

        $result = [];
        foreach ($resultSet as $item) {
            $result[$item->code] = $item->value;
        }
        return $result;
    }

    /**
     * Выборка уникальных значений свойств для выбранных категорий
     *
     * @param array $categories
     * @param int|null $group_id
     * @return array
     */
    public function fetchUniqueValuesForCategory(array $categories, ?int $group_id = null): array
    {
        $select = new Select();
        $select->from('catalog_offers_properties_values')
            ->columns(['property', 'value'])
            ->join('catalog_items', 'catalog_items.id = catalog_offers_properties_values.item', [])
            ->join('catalog_items_category', 'catalog_items_category.item = catalog_items.id', [])
            ->join('catalog_offers_properties',
                'catalog_offers_properties.id = catalog_offers_properties_values.property',
                ['code', 'name', 'type'])
            ->quantifier('DISTINCT');

        $select->where->in('catalog_items_category.category', $categories);
        $select->where->equalTo('catalog_offers_properties.filter', 1);
        $select->where->equalTo('catalog_items.active', 1);

        // Добавляем условие проверки наличия товара, если используется данный функционал
        if ($this->config['catalog']['options']['use-stock']) {
            $select->join('catalog_stock', 'catalog_stock.offer = catalog_offers_properties_values.offer', []);
            $select->where->greaterThan('catalog_stock.count', 0);
        }

        // Фильтрация по группам
        $subQuery = new Select();
        $subQuery->from('catalog_items')
            ->columns(['id'])
            ->join('catalog_items_properties_values', 'catalog_items_properties_values.item = catalog_items.id', [])
            ->join('catalog_items_category', 'catalog_items_category.item = catalog_items.id', [])
            ->join('catalog_items_properties', 'catalog_items_properties.id = catalog_items_properties_values.property',
                [])
            ->join('catalog_groups', 'catalog_groups.code = catalog_items_properties.code', [])
            ->quantifier('DISTINCT')
            ->where->in('catalog_items_category.category', $categories)
            ->equalTo('catalog_items.active', 1);

        if ($group_id) {
            // Выборка товаров только из указанной группы
            $subQuery->where->equalTo('catalog_groups.id', $group_id);
            $select->where->in('catalog_items.id', $subQuery);
        } else {
            // Исключение товаров из скрытых групп
            $subQuery->where->equalTo('catalog_groups.exclude', 1);
            $select->where->notIn('catalog_items.id', $subQuery);
        }

        // Сортировка по возрастанию значения свойств
        $select->order([
            'catalog_offers_properties.sort_index ASC',
            new Expression('CAST(value AS UNSIGNED)'), 'value'
        ]);

        $resultSet = $this->table->selectWith($select);

        $result = [];
        foreach ($resultSet as $item) {
            if (!isset($result[$item->property])) {
                $result[$item->property] = [
                    'id'     => $item->property,
                    'name'   => $item->name,
                    'type'   => $item->type,
                    'code'   => $item->code,
                    'values' => []
                ];
            }
            if (!in_array($item->value, $result[$item->property]['values'])) {
                $result[$item->property]['values'][] = $item->value;
            }
        }

        return $result;
    }

    /**
     * Очистка всех существующих значений свойств для торгового предложения
     *
     * @param int $offer_id
     */
    public function clearValues(int $offer_id)
    {
        $this->delete(['offer' => $offer_id]);
    }

    /**
     * Добавление значения свойства для торгового предложения
     *
     * @param int $item_id
     * @param int $offer_id
     * @param int $property_id
     * @param mixed $value
     */
    public function insertValue(int $item_id, int $offer_id, int $property_id, mixed $value)
    {
        $data = [
            'item'     => $item_id,
            'offer'    => $offer_id,
            'property' => $property_id,
            'value'    => $value
        ];
        $this->insert($data);
    }


}