<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой предопределенных значений свойтств торговых предложений
 *
 */
class OffersPropertiesDefaults extends AbstractModel
{

    /**
     * Критерии сортировки по умолчанию
     */
    protected ?array $defaultOrder = ['sort_index'];

    /**
     * Получение списка предустановленных значений для свойства
     *
     * @param int $property
     * @param bool $assoc
     * @return array
     */
    public function fetchByProperty(int $property, bool $assoc = false): array
    {
        if (!$assoc) {
            return $this->fetchBy('property', $property);
        } else {
            return $this->verticalSlice('value', 'value', ['property' => $property]);
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
                    'name'     => 'value',
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