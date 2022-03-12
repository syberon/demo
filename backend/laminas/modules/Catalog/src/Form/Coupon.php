<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use MtLib\Form\Form\AbstractForm;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;

class Coupon extends AbstractForm
{
    public static array $type = [
        'multi' => 'По количеству раз использования',
        'date'  => 'По дате действия'
    ];

    public static array $discount_type = [
        'percent' => 'Процент',
        'sum'     => 'Сумма'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'couponsFormApp');

        $this->add(
            [
                'name'       => 'code',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Промо-код',
                ]
            ]
        );
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
                'name'       => 'type',
                'type'       => Select::class,
                'attributes' => [
                    'required' => true,
                    'v-model' => 'type'
                ],
                'options'    => [
                    'label'         => 'Тип',
                    'value_options' => self::$type
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'count',
                'type'       => Number::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Оставшееся количество использований',
                    'row-attributes' => [
                        'v-if' => 'display.count'
                    ]
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'discount_type',
                'type'       => Select::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label'         => 'Тип скидки',
                    'value_options' => self::$discount_type
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'discount',
                'type'       => Number::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Сумма/процент скидки',
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'date_start',
                'type'       => Date::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Дата начала действия',
                    'row-attributes' => [
                        'v-if' => 'display.date_start'
                    ]
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'date_stop',
                'type'       => Date::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Дата окончания действия',
                    'row-attributes' => [
                        'v-if' => 'display.date_stop'
                    ]
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'active',
                'type'       => Checkbox::class,
                'options'    => [
                    'label' => 'Активен',
                ]
            ]
        );
        $this->get('active')->setValue(1);

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