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
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;

class Currency extends AbstractForm
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name'       => 'code',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Код валюты',
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
                'name'       => 'course',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true
                ],
                'options'    => [
                    'label' => 'Курс к рублю',
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