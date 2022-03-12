<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use MtLib\Form\Form\AbstractForm;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;

class ItemsProperty extends AbstractForm
{

    public static array $propertyTypes = [
        'text'     => 'Текст',
        'select'   => 'Список',
        'checkbox' => 'Галка'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name'       => 'name',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Название',
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'code',
                'type'    => Text::class,
                'options' => [
                    'label'         => 'Символьный код',
                    'help_block'    => 'Оставить пустым для автоматической генерации',
                    'add_on_append' => [
                        'element' => [
                            'name'       => 'generate-url-button',
                            'type'       => Button::class,
                            'attributes' => [
                                'data-target' => 'code',
                                'data-source' => 'name',
                                'title'       => 'Сгенерировать код'
                            ],
                            'options'    => [
                                'label' => '',
                                'icon'  => 'fas fa-cogs'
                            ]
                        ]
                    ]
                ]
            ]
        );
        $this->add(
            [
                'name'    => 'guid',
                'type'    => Text::class,
                'options' => [
                    'label'      => 'Внешний код',
                    'help_block' => 'Используется для связки с категориями 1С'
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'type',
                'type'       => Select::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label'         => 'Тип свойства',
                    'value_options' => self::$propertyTypes
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'multiple',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Множественный выбор',
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'filter',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Используется в фильтре',
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'show',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Отображать в карточке товара',
                ]
            ]
        );

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
}