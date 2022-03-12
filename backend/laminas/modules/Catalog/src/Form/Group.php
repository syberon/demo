<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use MtLib\Form\Form\AbstractForm;

class Group extends AbstractForm
{

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
                'name'    => 'exclude',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Не выводить в общем каталоге'
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