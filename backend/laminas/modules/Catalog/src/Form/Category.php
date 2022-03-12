<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\File;
use MtLib\Form\Element\CkEditor;
use MtLib\Form\Form\AbstractForm;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;

class Category extends AbstractForm
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
                    'label' => 'Название категории',
                ]
            ]
        );
        $this->add(
            [
                'name'    => 'url',
                'type'    => Text::class,
                'options' => [
                    'label'         => 'Символьный код',
                    'help_block'    => 'Оставить пустым для автоматической генерации.',
                    'add_on_append' => [
                        'element' => [
                            'name'       => 'generate-url-button',
                            'type'       => Button::class,
                            'attributes' => [
                                'data-target' => 'url',
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
                'name'       => 'description',
                'type'       => CkEditor::class,
                'attributes' => [
                    'data-editor-height' => '200'
                ],
                'options'    => [
                    'label' => 'Описание категории'
                ]
            ]
        );
        $this->add(
            [
                'name'    => 'picture',
                'type'    => File::class,
                'options' => [
                    'label' => 'Изображение',
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