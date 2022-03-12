<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use MtLib\Form\Element\CkEditor;
use MtLib\Form\Element\ComboBox;
use MtLib\Form\Form\AbstractForm;

class Item extends AbstractForm
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('v-on:submit', 'submitForm');
        $this->setAttribute('id', 'itemForm');

        $fieldset = new Fieldset('main');

        $fieldset->add(
            [
                'name'       => 'name',
                'type'       => Text::class,
                'attributes' => [
                    'required' => true,
                    'v-model'  => 'input.name'
                ],
                'options'    => [
                    'label' => 'Название',
                ]
            ]
        );

        $fieldset->add(
            [
                'name'       => 'code',
                'type'       => Text::class,
                'attributes' => [
                    'v-model' => 'input.code'
                ],
                'options'    => [
                    'label'         => 'Символьный код',
                    'help_block'    => 'Оставить пустым для автоматической генерации',
                    'add_on_append' => [
                        'element' => [
                            'name'       => 'generate-url-button',
                            'type'       => Button::class,
                            'attributes' => [
                                'title'      => 'Сгенерировать код',
                                'v-on:click' => 'generateCode'
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

        $fieldset->add(
            [
                'name'    => 'article',
                'type'    => Text::class,
                'options' => [
                    'label' => 'Артикул',
                ]
            ]
        );

        $fieldset->add(
            [
                'name'       => 'picture',
                'type'       => File::class,
                'attributes' => [
                    'multiple' => true
                ],
                'options'    => [
                    'label' => 'Прикрепленные изображения',
                ]
            ]
        );

        $fieldset->add(
            [
                'name'       => 'preview',
                'type'       => CkEditor::class,
                'attributes' => [
                    'data-editor-height' => '200'
                ],
                'options'    => [
                    'label' => 'Краткое описание'
                ]
            ]
        );

        $fieldset->add(
            [
                'name'       => 'description',
                'type'       => CkEditor::class,
                'attributes' => [
                    'data-editor-height' => '500'
                ],
                'options'    => [
                    'label' => 'Полное описание'
                ]
            ]
        );

        $fieldset->add(
            [
                'name'    => 'active',
                'type'    => Checkbox::class,
                'options' => [
                    'label' => 'Активность'
                ]
            ]
        );

        $fieldset->add(
            [
                'name'       => 'categories',
                'type'       => Hidden::class,
                'attributes' => [
                    'v-model' => 'input.categories'
                ]

            ]
        );

        $this->add($fieldset);

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

    /**
     * Генерация полей свойст для наименования
     *
     * @param array $data
     */
    public function generateProperties(array $data)
    {
        $fieldset = new Fieldset('properties');

        foreach ($data as $property) {
            switch ($property->type) {
                case 'text':
                    $fieldset->add(
                        [
                            'name'    => $property->code,
                            'type'    => ComboBox::class,
                            'options' => [
                                'label'    => $property->name,
                                'datalist' => $property->values
                            ]
                        ]
                    );
                    break;
                case 'select':
                    if ($property->values && count($property->values)) {
                        $property->values = ['' => ''] + $property->values;
                    }
                    $fieldset->add(
                        [
                            'name'       => $property->code,
                            'type'       => Select::class,
                            'attributes' => [
                                'multiple' => (bool)$property->multiple
                            ],
                            'options'    => [
                                'label'         => $property->name,
                                'value_options' => $property->values
                            ]
                        ]
                    );
                    break;
                case 'checkbox':
                    $fieldset->add(
                        [
                            'name'    => $property->code,
                            'type'    => Checkbox::class,
                            'options' => [
                                'label' => $property->name
                            ]
                        ]
                    );
                    break;
            }
        }

        $this->add($fieldset);
        $inputFilter = $this->getInputFilter();
        foreach ($data as $property) {
            $this->getInputFilter()->get('properties')->get($property->code)->setRequired(false);
        }
        $this->setInputFilter($inputFilter);
    }

    /**
     * Генерация полей цены и валюты
     *
     * @param array|null $currencies
     */
    public function generatePrice(?array $currencies = null)
    {
        $fieldset = new Fieldset('price');

        $fieldset->add(
            [
                'name'    => 'price',
                'type'    => Number::class,
                'options' => [
                    'label' => 'Цена основная'
                ]
            ]
        );
        $fieldset->add(
            [
                'name'    => 'price_sale',
                'type'    => Number::class,
                'options' => [
                    'label' => 'Цена со скидкой',
                ]
            ]
        );
        if ($currencies) {
            $fieldset->add(
                [
                    'name'    => 'currency',
                    'type'    => Select::class,
                    'options' => [
                        'label'         => 'Валюта',
                        'value_options' => $currencies
                    ]
                ]
            );
        }

        $this->add($fieldset);

        $inputFilter = $this->getInputFilter();
        $inputFilter->get('price')->get('price')->setRequired(false);
        $inputFilter->get('price')->get('price_sale')->setRequired(false);
        $inputFilter->get('price')->get('currency')->setRequired(false);
        $this->setInputFilter($inputFilter);
    }

    /**
     * Генерация полей наличия товара на складе и единиц измерения
     *
     * @param array $units
     */
    public function generateStock(array $units)
    {
        $fieldset = new Fieldset('stock');
        $fieldset->add(
            [
                'name'    => 'count',
                'type'    => Number::class,
                'options' => [
                    'label' => 'Наличие на складе',
                ]
            ]
        );
        $fieldset->add(
            [
                'name'    => 'unit',
                'type'    => ComboBox::class,
                'options' => [
                    'label'      => 'Единица измерения',
                    'help_block' => 'Выберите из списка или укажите новую',
                    'datalist'   => $units
                ]
            ]
        );
        $this->add($fieldset);

        $inputFilter = $this->getInputFilter();
        $inputFilter->get('stock')->get('count')->setRequired(false);
        $inputFilter->get('stock')->get('unit')->setRequired(false);
        $this->setInputFilter($inputFilter);
    }

    /**
     * Подготовака полей формы к редактированию
     */
    public function prepareEdit()
    {
        $this->get('main')->remove('picture');
        $this->generatePicturesForm();
        $this->generateOffersForm();
    }

    /**
     * Генерация полей для добавления фотографий к товары при редактировании
     */
    public function generatePicturesForm()
    {
        $fieldset = new Fieldset('pictures');
        $fieldset->add(
            [
                'name'       => 'picture[]',
                'type'       => File::class,
                'attributes' => [
                    'multiple'    => true,
                    'id'          => 'pictures-input',
                    'v-on:change' => 'checkPicturesSelected'
                ],
                'options'    => [
                    'label' => 'Изображение',
                ]
            ]
        );
        $fieldset->add(
            [
                'name'       => 'savePictures',
                'type'       => Button::class,
                'attributes' => [
                    'class'           => 'save-pictures-button',
                    'v-on:click'      => 'uploadPictures',
                    'v-bind:disabled' => '!upload_available'

                ],
                'options'    => [
                    'label' => 'Добавить изображения',
                ]
            ]
        );

        $this->add($fieldset);
        $inputFilter = $this->getInputFilter();
        $inputFilter->get('pictures')->get('picture[]')->setRequired(false);
        $this->setInputFilter($inputFilter);
    }

    /**
     * Генерация полей для добавления торгового предложения
     */
    public function generateOffersForm()
    {
        $fieldset = new Fieldset('offers');
        $fieldset->add(
            [
                'name'       => 'addOffer',
                'type'       => Button::class,
                'attributes' => [
                    'class'      => 'add-offer-button',
                    'v-on:click' => 'addOffer',
                ],
                'options'    => [
                    'label' => 'Добавить предложение',
                ]
            ]
        );
        $this->add($fieldset);
    }
}