<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\File;
use MtLib\Form\Form\AbstractForm;
use Laminas\Form\Element\Submit;

class Picture extends AbstractForm
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name'    => 'picture',
                'type'    => File::class,
                'attributes' => [
                    'multiple' => true
                ],
                'options' => [
                    'label' => 'Изображение',
                ]
            ]
        );

        $this->add(
            [
                'type'    => Submit::class,
                'options' => [
                    'label'   => 'Добавить',
                    'exclude' => true
                ]
            ],
            [
                'priority' => -100,
            ]
        );
    }
}