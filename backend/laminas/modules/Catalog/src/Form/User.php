<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;

class User extends \MtCms\User\Form\User
{

    /**
     * Подготовка формы для редактирования профиля
     */
    public function prepareEdit()
    {
        $this->add(
            [
                'name'    => 'phone',
                'type'    => Text::class,
                'options' => [
                    'label' => 'Контактный телефон'
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'city',
                'type'    => Text::class,
                'options' => [
                    'label' => 'Город'
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'zip_code',
                'type'    => Text::class,
                'options' => [
                    'label' => 'Почтовый индекс'
                ]
            ]
        );

        $this->add(
            [
                'name'    => 'address',
                'type'    => Textarea::class,
                'options' => [
                    'label' => 'Адрес доставки'
                ]
            ]
        );

        $this->setInputFilter($this->getInputFilter());

    }
}