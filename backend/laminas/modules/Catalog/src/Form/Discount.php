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
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;

class Discount extends AbstractForm
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name'       => 'min',
                'type'       => Number::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Минимальная сумма',
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'max',
                'type'       => Number::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Максимальная сумма',
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'discount',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Скидка, %',
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