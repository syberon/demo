<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use MtLib\Form\Element\ComboBox;
use MtLib\Form\Form\AbstractForm;

class Offer extends AbstractForm
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'offer-edit-form');
        $fieldset = new Fieldset('main');

        $fieldset->add(
            [
                'name'       => 'code',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Штрихкод',
                ]
            ]
        );

        $fieldset->add(
            [
                'name'    => 'picture',
                'type'    => File::class,
                'options' => [
                    'label' => 'Прикрепленное изображение',
                ]
            ]
        );

        $fieldset->add(
            [
                'name'    => 'active',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Активность'
                ]
            ]
        );

        $fieldset->setLabel('Основные параметры');

        $this->add($fieldset);

        $this->add(
            [
                'type'    => Submit::class,
                'options' => [
                    'label'   => 'Сохранить',
                    'exclude' => true
                ]
            ],
            [
                'priority' => -100,
            ]
        );
    }

    /**
     * Генерация полей свойст для торгового предложения
     *
     * @param array $data
     */
    public function generateProperties(array $data)
    {
        $fieldset = new Fieldset('properties');

        foreach ($data as $property) {
            switch ($property->type) {
                case 'text':
                    $fieldset->add(
                        [
                            'name'    => $property->code,
                            'type'    => ComboBox::class,
                            'options' => [
                                'label'    => $property->name,
                                'datalist' => $property->values
                            ]
                        ]
                    );
                    break;
                case 'select':
                    if ($property->values && count($property->values)) {
                        $property->values = ['' => ''] + $property->values;
                    }
                    $fieldset->add(
                        [
                            'name'    => $property->code,
                            'type'    => Select::class,
                            'options' => [
                                'label'         => $property->name,
                                'value_options' => $property->values
                            ]
                        ]
                    );
                    break;
                case 'checkbox':
                    $fieldset->add(
                        [
                            'name'    => $property->code,
                            'type'    => Checkbox::class,
                            'options' => [
                                'label' => $property->name
                            ]
                        ]
                    );
                    break;
            }
        }

        $fieldset->setLabel('Свойства торгового предложения');

        $this->add($fieldset);
        $inputFilter = $this->getInputFilter();
        foreach ($data as $property) {
            $this->getInputFilter()->get('properties')->get($property->code)->setRequired(false);
        }
        $this->setInputFilter($inputFilter);
    }

    /**
     * Генерация полей цены и валюты
     *
     * @param array|null $currencies
     */
    public function generatePrice(?array $currencies = null)
    {
        $fieldset = new Fieldset('price');

        $fieldset->add(
            [
                'name'    => 'price',
                'type'    => Number::class,
                'options' => [
                    'label' => 'Цена основная'
                ]
            ]
        );
        $fieldset->add(
            [
                'name'    => 'price_sale',
                'type'    => Number::class,
                'options' => [
                    'label' => 'Цена со скидкой',
                ]
            ]
        );
        if ($currencies) {
            $fieldset->add(
                [
                    'name'    => 'currency',
                    'type'    => Select::class,
                    'options' => [
                        'label'         => 'Валюта',
                        'value_options' => $currencies
                    ]
                ]
            );
        }

        $fieldset->setLabel('Цена');

        $this->add($fieldset);

        $inputFilter = $this->getInputFilter();
        $inputFilter->get('price')->get('price')->setRequired(false);
        $inputFilter->get('price')->get('price_sale')->setRequired(false);

        if ($currencies) {
            $inputFilter->get('price')->get('currency')->setRequired(false);
        }
        $this->setInputFilter($inputFilter);
    }

    /**
     * Генерация полей наличия товара на складе и единиц измерения
     *
     * @param array $units
     */
    public function generateStock(array $units)
    {
        $fieldset = new Fieldset('stock');
        $fieldset->add(
            [
                'name'    => 'count',
                'type'    => Number::class,
                'options' => [
                    'label' => 'Наличие на складе',
                ]
            ]
        );
        $fieldset->add(
            [
                'name'    => 'unit',
                'type'    => ComboBox::class,
                'options' => [
                    'label'      => 'Единица измерения',
                    'help_block' => 'Выберите из списка или укажите новую',
                    'datalist'   => $units
                ]
            ]
        );

        $fieldset->setLabel('Информация о наличии');

        $this->add($fieldset);

        $inputFilter = $this->getInputFilter();
        $inputFilter->get('stock')->get('count')->setRequired(false);
        $inputFilter->get('stock')->get('unit')->setRequired(false);
        $this->setInputFilter($inputFilter);
    }
}