<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой валют
 *
 */
class Currencies extends AbstractModel
{
    /**
     * Получение списка валют
     */
    public function getList(): array
    {
        return $this->verticalSlice('name', 'id');
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
                    'name'     => 'course',
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