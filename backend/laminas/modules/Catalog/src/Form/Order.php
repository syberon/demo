<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use MtLib\Form\Element\Content;
use MtLib\Form\Form\AbstractForm;

class Order extends AbstractForm
{

    /**
     * Методы доставки
     */
    public array $delivery_method = [
        1 => 'Доставка по России',
        2 => 'Самовывоз'
    ];

    /**
     * Способ оплаты
     */
    public array $payment_method = [
        1 => 'On-line оплата',
        2 => 'При получении в точке выдачи'
    ];

    /**
     * Статусы заказа
     */
    public array $order_status = [
        1 => 'Оформлен, ожидет оплаты',
        2 => 'Подтвержден',
        3 => 'Передан в службу доставки',
        4 => 'Выполнен',
        5 => 'Отменен'
    ];


    public function __construct()
    {
        parent::__construct();
        $this->setAttributes([
            'method'              => 'post',
            'class'               => 'order-form',
            'id'                  => 'order-form',
            'v-on:submit.prevent' => 'makeOrder'
        ]);

        $this->add(
            [
                'name'       => 'display_name',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Ф.И.О.'
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'phone',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Контактный телефон*'
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'email',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'E-mail адрес'
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'payment',
                'type'       => Select::class,
                'attributes' => [
                    'required' => true,
                    'class'    => 'payment-select',
                    'v-model'  => 'input.payment'
                ],
                'options'    => [
                    'label'         => 'Способ оплаты',
                    'value_options' => $this->payment_method
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'delivery',
                'type'       => Select::class,
                'attributes' => [
                    'required' => true,
                    'v-model'  => 'input.delivery'
                ],
                'options'    => [
                    'label'         => 'Доставка заказа',
                    'value_options' => $this->delivery_method
                ]
            ]
        );

        $deliveryFieldset = new Fieldset('delivery-block');
        $deliveryFieldset->setAttributes([
            'class' => 'delivery-fieldset',
            'v-if'  => 'show_delivery_address'

        ]);

        $deliveryFieldset->add(
            [
                'name'       => 'price_delivery',
                'type'       => Hidden::class,
                'attributes' => [
                    'v-model' => 'input.price_delivery'
                ]

            ]
        );

        $deliveryFieldset->add(
            [
                'name'       => 'city',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true,
                    'v-model'  => 'input.city_name',
                    'ref'      => 'city_name'
                ],
                'options'    => [
                    'label' => 'Город*'
                ]
            ]
        );

        $deliveryFieldset->add(
            [
                'name'       => 'city_autocomplete',
                'type'       => Content::class,
                'options'    => [
                    'content' => '<autocomplete :input="input" :city_name="city_name" @set-active-city="setActiveCity"></autocomplete>'
                ]
            ]
        );

        $deliveryFieldset->add(
            [
                'name'       => 'city_code',
                'type'       => Hidden::class,
                'attributes' => [
                    'v-model' => 'input.city_id'
                ]

            ]
        );

        $deliveryFieldset->add(
            [
                'name'       => 'zip_code',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true,
                    'v-model'  => 'input.zip_code'
                ],
                'options'    => [
                    'label' => 'Почтовый индекс*'
                ]
            ]
        );

        $deliveryFieldset->add(
            [
                'name'       => 'address',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true,
                ],
                'options'    => [
                    'label' => 'Адрес*'
                ]
            ]
        );

        $this->add($deliveryFieldset);

        $this->add(
            [
                'name'    => 'save_delivery',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Сохранить данные доставки для следующих заказов'
                ]
            ]
        );

        $this->get('save_delivery')->setValue(1);

        $this->add(
            [
                'name'    => 'description',
                'type'    => Textarea::class,
                'options' => [
                    'label' => 'Комментарий к заказу'
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'submit',
                'type'       => Submit::class,
                'options'    => [
                    'label'   => 'Оформить заказ',
                    'exclude' => true
                ],
                'attributes' => [
                    'class'           => 'btn-success',
                    'v-bind:class'    => '{disabled: (!order_button_active || !success)}',
                    'v-bind:disabled' => '!(order_button_active && success)'
                ]

            ],
            [
                'priority' => -100,
            ]
        );
    }

    public function prepareEdit()
    {
        $this->removeAttribute('v-on:submit.prevent');
        $this->remove('submit');
        $this->get('delivery-block')->remove('price_delivery');
        $this->remove('save_delivery');

        $this->get('delivery-block')->add(
            [
                'name'       => 'price_delivery',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Стоимость доставки'
                ]
            ]
        );


        $this->add(
            [
                'name'       => 'status',
                'type'       => Select::class,
                'attributes' => [
                    'required' => true,
                ],
                'options'    => [
                    'label'         => 'Статус заказа',
                    'value_options' => $this->order_status
                ]
            ]
        );


        $this->add(
            [
                'name'       => 'submit',
                'type'       => Button::class,
                'options'    => [
                    'label'   => 'Сохранить изменения',
                    'exclude' => true
                ],
                'attributes' => [
                    'class'      => 'btn-success float-end',
                    'v-on:click' => 'saveChanges'
                ]

            ],
            [
                'priority' => -100,
            ]
        );
    }
}