<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use ArrayObject;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\InputFilter\InputFilter;
use Laminas\Paginator\Adapter\LaminasDb\DbSelect;
use Laminas\Paginator\Paginator;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\EmailAddress;
use MtLib\Base\Model\AbstractModel;
use Traversable;

/**
 * Модель работы с базой заказов
 *
 */
class Orders extends AbstractModel
{

    /**
     * Критерии сортировки по умолчанию
     */
    protected ?array $defaultOrder = ['date DESC'];

    /**
     * Использовать постраничный вывод
     */
    protected bool $usePagination = true;

    /**
     * Выборка заказов пользователя
     *
     * @param int $user_id
     * @param array $params
     * @param array|string|Expression $order
     * @return Traversable|ArrayObject
     */
    public function fetchByUser(int $user_id, array $params = [],
                                Expression|array|string $order = ''): Traversable|ArrayObject
    {
        $params['user'] = $user_id;
        $orders = $this->fetchList($params, $order);
        return $orders->getIterator();

    }

    /**
     * @param array $params
     * @param Expression|array|string|null $order
     * @return array|Paginator
     */
    public function fetchList(array $params, Expression|array|string|null $order = ''): array|Paginator
    {
        $select = new Select();
        $where = new Where();

        $identicalFields = [
            'user',
            'status'
        ];

        if ($params) {
            foreach ($params as $field => $value) {
                if ($value) {
                    if (in_array($field, $identicalFields)) {
                        $where->equalTo($field, $value);
                    } else {
                        $where->like($field, '%' . $value . '%');
                    }
                }
            }
        }

        $select->from('catalog_orders')
            ->columns(['*'])
            ->where($where)
            ->order($order ?: $this->defaultOrder);

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
                    'name'     => 'name',
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
                    'name'     => 'phone',
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
                    'name'     => 'price_delivery',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => ToNull::class
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'       => 'email',
                    'required'   => true,
                    'filters'    => [
                        [
                            'name' => StringTrim::class
                        ]
                    ],
                    'validators' => [
                        [
                            'name' => EmailAddress::class
                        ]
                    ]
                ]
            );
        }
        return $this->inputFilter;
    }
}