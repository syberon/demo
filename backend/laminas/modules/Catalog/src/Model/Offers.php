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
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\Size;
use MtLib\Base\Model\AbstractModel;
use MtLib\Upload\Controller\Plugin\Uploads;

/**
 * Модель работы с базой торговых предложений
 *
 */
class Offers extends AbstractModel
{
    /* Критерии сортировки по умолчанию */
    protected ?array $defaultOrder = ['id'];

    /**
     * Offers constructor.
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
     * Поиск торгового предложения по коду
     *
     * @param int|string $code
     * @return ArrayObject|null
     */
    public function getByCode(int|string $code): ?ArrayObject
    {
        return $this->fetchOneBy('code', $code);
    }

    /**
     * Поиск торгового предложения по внешнему коду
     *
     * @param string $guid
     * @return ArrayObject|null
     */
    public function getByGuid(string $guid): ?ArrayObject
    {
        return $this->fetchOneBy('guid', $guid);
    }

    /**
     * Поиск торгового предложения по внешнему коду
     *
     * @param string $guid
     * @return int|null
     */
    public function getIdByGuid(string $guid): ?int
    {
        $item = $this->fetchOneBy('guid', $guid);
        if ($item) {
            return $item->id;
        }
        return null;
    }

    /**
     * Выборка всех тороговых предложений для наименования в виде массива внешних кодов
     *
     * @param int $item_id
     * @return array
     */
    public function fetchOffersGuid(int $item_id): array
    {
        return $this->verticalSlice('guid', 'id', ['item' => $item_id]);
    }

    /**
     * Удаление фотогарфий торговых предложений
     *
     * @param Event|null $event
     */
    public function deletePictures(Event $event = null)
    {
        $item_id = $event->getParam('id');
        $offers = $this->fetchOffers($item_id);

        foreach ($offers as $offer) {
            if ($offer->picture && $this->uploadsPlugin->has($offer->picture)) {
                $this->uploadsPlugin->delete($offer->picture);
            }
        }
    }

    /**
     * Выборка всех торговых предложений для наименования
     *
     * @param int $item_id
     * @return array
     */
    public function fetchOffers(int $item_id): array
    {
        return $this->fetchBy('item', $item_id);
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
                    'name'     => 'code',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'       => 'picture',
                    'required'   => false,
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