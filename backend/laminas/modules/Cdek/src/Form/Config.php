<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Cdek\Form;

use MtLib\Form\Form\AbstractForm;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;

class Config extends AbstractForm
{

    protected array $tariffs = [
        136 => 'Посылка склад-склад',
        137 => 'Посылка склад-дверь',
        138 => 'Посылка дверь-склад',
        139 => 'Посылка дверь-дверь',
        233 => 'Экономичная посылка склад-дверь',
        234 => 'Экономичная посылка склад-склад'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'cdek-config');
        $this->setAttribute('class', 'cdek-config');

        $this->add(
            [
                'name'       => 'account',
                'type'       => Text::class,
                'options'    => [
                    'label'      => 'Account',
                    'help_block' => 'Аккаунт для доступа к сервису расчета'
                ],
                'attributes' => [
                    'required' => true
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'password',
                'type'       => Text::class,
                'options'    => [
                    'label'      => 'Secure password',
                    'help_block' => 'Пароль'
                ],
                'attributes' => [
                    'required' => true
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'sender_city_name',
                'type'       => Text::class,
                'options'    => [
                    'label' => 'Город отправления по базе СДЭК'
                ],
                'attributes' => [
                    'v-model' => 'input.city_name',
                    'ref'     => 'city_name'
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'sender_city_id',
                'type'       => Hidden::class,
                'attributes' => [
                    'v-model' => 'input.city_id',
                    'ref'     => 'city_id'
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'tariff_id',
                'type'    => Select::class,
                'options' => [
                    'label'         => 'Тариф для расчета',
                    'value_options' => $this->tariffs
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'free_sum',
                'type'    => Number::class,
                'options' => [
                    'label' => 'Пороговая сумма для бесплатной доставки',
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'parcel_weight',
                'type'       => Number::class,
                'options'    => [
                    'label' => 'Общий вес места (в килограммах)'
                ],
                'attributes' => [
                    'required' => true
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'parcel_length',
                'type'       => Number::class,
                'options'    => [
                    'label' => 'Длина места (в сантиметрах)'
                ],
                'attributes' => [
                    'required' => true
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'parcel_width',
                'type'       => Number::class,
                'options'    => [
                    'label' => 'Ширина места (в сантиметрах)'
                ],
                'attributes' => [
                    'required' => true
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'parcel_height',
                'type'       => Number::class,
                'options'    => [
                    'label' => 'Высота места (в сантиметрах)'
                ],
                'attributes' => [
                    'required' => true
                ]
            ]
        );

        $this->add(
            [
                'type'    => Submit::class,
                'options' => [
                    'label'   => 'Применить',
                    'exclude' => true
                ]
            ],
            [
                'priority' => -100,
            ]
        );
    }
}