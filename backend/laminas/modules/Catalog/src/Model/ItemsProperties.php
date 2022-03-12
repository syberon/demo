<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use ArrayObject;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой свойств товара
 *
 */
class ItemsProperties extends AbstractModel
{

    /**
     * Критерии сортировки по умолчанию
     */
    protected ?array $defaultOrder = ['sort_index'];


    /**
     * Поиск свойства в базе по символьному коду
     *
     * @param string $code
     * @return ArrayObject|null
     */
    public function getProperty(string $code): ?ArrayObject
    {
        return $this->fetchOneBy('code', $code);
    }

    /**
     * Поиск свойства в базе по внешнему коду синхронизации
     *
     * @param string $guid
     * @return ArrayObject|null
     */
    public function getByGuid(string $guid): ?ArrayObject
    {
        return $this->fetchOneBy('guid', $guid);
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
                    'name'       => 'code',
                    'required'   => false,
                    'filters'    => [
                        [
                            'name' => StringTrim::class
                        ]
                    ],
                    'validators' => [
                        [
                            'name'    => StringLength::class,
                            'options' => [
                                'min' => 2,
                                'max' => 255
                            ]
                        ],
                        [
                            'name'    => Regex::class,
                            'options' => [
                                'pattern'  => '/^[a-zA-Z][a-zA-Z0-9_\-]*$/',
                                "messages" => [
                                    "regexNotMatch" => "Значение может состоять только из латинских букв, цифр, символа
                                     подчеркивания"
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }
        return $this->inputFilter;
    }
}