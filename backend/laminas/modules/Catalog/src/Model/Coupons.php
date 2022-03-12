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
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\InputFilter\InputFilter;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой промо-кодов
 *
 */
class Coupons extends AbstractModel
{

    /**
     * Критерии сортировки по умолчанию
     */
    protected ?array $defaultOrder = ['id'];

    /**
     * Поиск заданного купона в базе данных
     *
     * @param string $code
     * @return ArrayObject|null
     */
    public function getCoupon(string $code): ?ArrayObject
    {
        return $this->fetchOneBy('code', $code);
    }

    /**
     * Обновление параметров купона
     *
     * @param int $id
     */
    public function updateCoupon(int $id)
    {
        $coupon = $this->find($id);
        $this->update(['count' => $coupon->count - 1], $id);
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
                    'name'     => 'code',
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
                    'name'     => 'count',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ],
                        [
                            'name' => ToNull::class
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
            $this->inputFilter->add(
                [
                    'name'     => 'date_start',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ],
                        [
                            'name' => ToNull::class
                        ]
                    ]
                ]
            );
            $this->inputFilter->add(
                [
                    'name'     => 'date_stop',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ],
                        [
                            'name' => ToNull::class
                        ]
                    ]
                ]
            );

        }
        return $this->inputFilter;
    }
}