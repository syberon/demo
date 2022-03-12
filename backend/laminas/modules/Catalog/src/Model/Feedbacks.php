<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Paginator\Adapter\LaminasDb\DbSelect;
use Laminas\Paginator\Paginator;
use Laminas\Stdlib\ArrayUtils;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой отзывов о товарах
 *
 */
class Feedbacks extends AbstractModel
{

    protected ?array $defaultOrder = ['date desc'];

    /**
     * Использовать постраничный вывод
     * @var bool
     */
    protected bool $usePagination = true;

    /**
     * Получение списка активных отзывов о товаре
     * @param int $item
     * @return array|Paginator
     */
    public function fetchActive(int $item): array|Paginator
    {
        return $this->fetch([
            'active' => 1,
            'item'   => $item
        ]);
    }

    /**
     * @param array|null $params
     * @param null $order
     * @return array|Paginator
     */
    public function fetchList(?array $params, $order = null): array|Paginator
    {
        $select = new Select();
        $where = new Where();

        if ($params) {
            foreach ($params as $field => $value) {
                switch ($field) {
                    case 'active':
                        switch ($value) {
                            case '0':
                            {
                                $where->notEqualTo('catalog_feedback.active', 1);
                                break;
                            }
                            case '1':
                            {
                                $where->like('catalog_feedback.active', $value);
                                break;
                            }
                        }
                        break;
                    case 'item':
                        if ($value) {
                            $where->like('catalog_items.name', '%' . $value . '%');
                        }
                        break;
                    default:
                        if ($value) {
                            $where->like('catalog_feedback.' . $field, '%' . $value . '%');
                        }
                }
            }
        }

        $select->from('catalog_feedback')
            ->columns(['*'])
            ->where($where)
            ->order($order ?: $this->defaultOrder)
            ->join('catalog_items', 'catalog_items.id = catalog_feedback.item', ['item_name' => 'name']);

        if ($this->usePagination) {
            $paginatorAdapter = new DbSelect($select, $this->table->getAdapter(),
                $this->table->getResultSetPrototype());
            $paginator = new Paginator($paginatorAdapter);
            $paginator->setDefaultItemCountPerPage($this->itemPerPage);
            $paginator->setCurrentPageNumber($this->currentPage);
            return $paginator;
        } else {
            $resultSet = $this->table->selectWith($select);
            return ArrayUtils::iteratorToArray($resultSet, false);
        }
    }


    /**
     * Retrieve input filter
     *
     * @return InputFilter
     */
    public function getInputFilter(): InputFilter
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new InputFilter();

            $this->inputFilter->add(
                [
                    'name'     => 'rate',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'     => 'text',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'     => 'active',
                    'required' => false
                ]
            );
        }
        return $this->inputFilter;
    }
}