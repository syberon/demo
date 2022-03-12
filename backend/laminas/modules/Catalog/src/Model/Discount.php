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
use Laminas\Db\Sql\Select;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой накопительных скидок
 *
 */
class Discount extends AbstractModel
{

    /* Критерии сортировки по умолчанию */
    protected ?array $defaultOrder = ['min'];

    /**
     * @param int $sum
     * @return ArrayObject|null
     */
    public function getDiscount(int $sum): ?ArrayObject
    {
        $select = new Select();
        $select->from('catalog_discount');
        $select->columns(['*']);
        $select->where->greaterThanOrEqualTo('max', $sum);
        $select->where->lessThanOrEqualTo('min', $sum);
        $select->order('min ASC');
        $select->limit(1);

        $resultSet = $this->getTable()->selectWith($select);
        if ($resultSet->count()) {
            return $resultSet->current();
        }
        return null;
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
                    'name'     => 'min',
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
                    'name'     => 'max',
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
                    'name'     => 'discount',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );
        }
        return $this->inputFilter;
    }
}