<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use MtLib\Form\Form\AbstractForm;

class Feedback extends AbstractForm
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'item',
                'type' => Hidden::class
            ]
        );
        $this->add(
            [
                'name'       => 'rate',
                'type'       => Number::class,
                'attributes' => [
                    'required' => true,
                    'min'      => 1,
                    'max'      => 5
                ],
                'options'    => [
                    'label' => 'Оценка',
                ]
            ]
        );
        $this->add(
            [
                'name'    => 'username',
                'type'    => Text::class,
                'options' => [
                    'label' => 'Имя пользователя',
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'text',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Отзыв',
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'active',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Активен',
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