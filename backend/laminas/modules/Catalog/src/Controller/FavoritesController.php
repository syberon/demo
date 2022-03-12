<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\MtAbstractActionController;
use MtModules\Catalog\Model\Favorites;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Price;
use MtModules\Catalog\Model\Stock;

/**
 * Class FavoritesController
 *
 * @package MtModules\Catalog\Controller
 *
 */
class FavoritesController extends MtAbstractActionController
{
    /**
     * CartController constructor.
     *
     * @param Items $modelItems
     * @param Favorites $modelFavorites
     * @param Pictures $modelPictures
     * @param Stock $modelStock
     * @param Price $modelPrice
     */
    public function __construct(
        protected Items $modelItems,
        protected Favorites $modelFavorites,
        protected Pictures $modelPictures,
        protected Stock $modelStock,
        protected Price $modelPrice
    )
    {
    }

    /**
     * Просмотр списка избранных товаров
     */
    public function indexAction(): array
    {
        return [];
    }

    /**
     * Добавление наименования избаранное
     */
    public function addAction(): JsonModel
    {
        $item = $this->params()->fromRoute('id');
        $this->modelFavorites->insert($item);
        return new JsonModel(['code' => 1]);
    }

    /**
     * Получение статуса избранного
     */
    public function getstatusAction(): JsonModel
    {
        $status = [
            'items' => $this->modelFavorites->getCount()
        ];

        return new JsonModel($status);
    }

    /**
     * Получение списка наименований в избранном
     */
    public function getlistAction(): JsonModel
    {
        $favoriteItems = $this->modelFavorites->getList();

        $items = [];

        foreach ($favoriteItems as $item_id) {
            $item = $this->modelItems->find($item_id);
            if ($item && $item->active && $this->modelStock->isItemAvailable($item_id)) {
                $item->link = $this->url()->fromRoute('catalogrouter', ['item' => $item_id]);
                // Выборка и обработка фотографий
                $picture = $this->modelPictures->fetchMain($item_id);
                if ($picture && $this->uploads()->has($picture->picture)) {
                    $item->picture = $this->imgUrl($this->uploads()->get($picture->picture)->getPublicUrl(),
                        'catalog-item-list');
                } else {
                    $item->picture = null;
                }

                $price = $this->modelPrice->getMinForItem($item_id);
                $item->price = $price->price;
                $item->price_sale = $price->price_sale;
                $item->price_base = $price->price_base;
                $item->price_discount = $price->price_discount;

                $items[] = $item;
            } else {
                $this->modelFavorites->delete($item_id);
            }
        }

        return new JsonModel([
            'items' => $items
        ]);
    }

    /**
     * Удаление наименования из избранного
     */
    public function deleteAction(): JsonModel
    {
        $id = $this->params()->fromRoute('id');
        if ($id) {
            $this->modelFavorites->delete($id);
        }
        return new JsonModel();
    }

    /**
     * Очистка списка избранного
     */
    public function clearAction(): JsonModel
    {
        $this->modelFavorites->clear();
        return new JsonModel();
    }

}
