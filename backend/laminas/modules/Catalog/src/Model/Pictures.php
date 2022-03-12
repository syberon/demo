<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use ArrayObject;
use JetBrains\PhpStorm\Pure;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\EventManager\Event;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\Size;
use MtLib\Base\Model\AbstractModel;
use MtLib\Upload\Controller\Plugin\Uploads;

/**
 * Модель работы с базой прикрепленных к товару изображений
 *
 */
class Pictures extends AbstractModel
{

    /* Критерии сортировки по умолчанию */
    protected ?array $defaultOrder = ['sort_index'];

    /**
     * @param TableGatewayInterface $tableGateway
     * @param Uploads $uploadsPlugin
     */
    #[Pure]
    public function __construct(
        TableGatewayInterface $tableGateway,
        protected Uploads $uploadsPlugin
    )
    {
        parent::__construct($tableGateway);
    }

    /**
     * Получение главной фотографии для товара
     *
     * @param int $item_id
     * @return ArrayObject|null
     */
    public function fetchMain(int $item_id): ?ArrayObject
    {
        return $this->fetchOneBy('item', $item_id);
    }

    /**
     * Удаление приклепленных к наименованию фотографий
     * @param Event|null $event
     */
    public function deletePictures(Event $event = null)
    {
        $item_id = $event->getParam('id');
        $pictures = $this->fetchForItem($item_id);

        foreach ($pictures as $picture) {
            if ($picture->picture && $this->uploadsPlugin->has($picture->picture)) {
                $this->uploadsPlugin->delete($picture->picture);
            }
        }
    }

    /**
     * Получение списка фотографий для товара
     *
     * @param int $item_id
     * @return array
     */
    public function fetchForItem(int $item_id): array
    {
        return $this->fetchBy('item', $item_id);
    }

    /**
     * Удаление приклепленных к наименованию фотографий
     *
     * @param int $item_id
     */
    public function deletePicturesForItem(int $item_id)
    {
        $pictures = $this->fetchForItem($item_id);

        foreach ($pictures as $picture) {
            if ($picture->picture && $this->uploadsPlugin->has($picture->picture)) {
                $this->uploadsPlugin->delete($picture->picture);
            }
            $this->delete($picture->id);
        }
    }

    /**
     * Retrieve input filter
     *
     * @return InputFilter
     */
    public function getInputFilter(): InputFilter
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new InputFilter();

            $this->inputFilter->add(
                [
                    'name'       => 'picture',
                    'required'   => true,
                    'validators' => [
                        [
                            'name'    => Extension::class,
                            'options' => [
                                'extension' => 'png,gif,jpg,jpeg'
                            ]
                        ],
                        [
                            'name'    => Size::class,
                            'options' => [
                                'max' => '3MB'
                            ]
                        ]
                    ]
                ]
            );
        }
        return $this->inputFilter;
    }
}