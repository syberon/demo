<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use Exception;
use Laminas\Http\Response;
use MtLib\Base\Controller\MtAbstractActionController;
use MtModules\Catalog\Form\Offer;
use MtModules\Catalog\Model\Currencies;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Price;
use MtModules\Catalog\Model\Stock;

/**
 * Class OffersController
 *
 * @package MtModules\Catalog\Controller
 */
class OffersController extends MtAbstractActionController
{
    protected ?string $redirectRoute = 'catalog/items/edit';

    /**
     * ItemsController constructor.
     *
     * @param Offer $form
     * @param Offers $modelOffers
     * @param OffersProperties $modelOffersProperties
     * @param OffersPropertiesValues $modelOffersPropertiesValues
     * @param Items $modelItems
     * @param Stock $modelStock
     * @param Currencies $modelCurrencies
     * @param Price $modelPrice
     * @param Pictures $modelPictures
     */
    public function __construct(
        protected Offer $form,
        protected Offers $modelOffers,
        protected OffersProperties $modelOffersProperties,
        protected OffersPropertiesValues $modelOffersPropertiesValues,
        protected Items $modelItems,
        protected Stock $modelStock,
        protected Currencies $modelCurrencies,
        protected Price $modelPrice,
        protected Pictures $modelPictures
    )
    {
    }

    /**
     * @throws Exception
     */
    public function addAction(): Response|array
    {
        $item_id = $this->params()->fromRoute('item');
        $item = $this->modelItems->find($item_id);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $post = array_merge_recursive($post, $this->getRequest()->getFiles()->toArray());

            $this->form->setData($post);

            if ($this->form->isValid()) {
                $data = $this->form->getData();

                // ?????????????????? ?????????? ???????????????? ?????????????????????? ????????????
                $main = $data['main'];
                $main['item'] = $item_id;

                $uploadFile = $this->params()->fromFiles('main')['picture'];
                if ($uploadFile['name']) {
                    $main['picture'] = $this->uploads()->upload($uploadFile);
                }

                $offer_id = $this->modelOffers->insert($main);

                // ?????????????????? ???????????????? ??????????????
                $properties = $data['properties'];
                foreach ($properties as $property_code => $property_value) {
                    if ($property_value) {
                        $property = $this->modelOffersProperties->getProperty($property_code);
                        $this->modelOffersPropertiesValues->insertValue($item_id, $offer_id, $property->id,
                            $property_value);
                    }
                }

                // ?????????????????? ????????
                $price = $data['price'];
                if (!$price['price']) {
                    $price['price'] = 0;
                }
                if (!$price['price_sale']) {
                    $price['price_sale'] = null;
                }
                $price['item'] = $item_id;
                $price['offer'] = $offer_id;
                $this->modelPrice->insert($price);

                // ?????????????????? ??????????????
                if ($this->config()['catalog']['options']['use-stock']) {
                    $stock = $data['stock'];
                    if (!$stock['count']) {
                        $stock['count'] = 0;
                    }
                    if (!$stock['unit']) {
                        unset($stock['unit']);
                    }
                    $stock['item'] = $item_id;
                    $stock['offer'] = $offer_id;
                    $this->modelStock->insert($stock);
                }

                $this->fm('???????????????? ?????????????????????? ?????????????? ??????????????????');
                return $this->redirect()->toRoute($this->redirectRoute, ['id' => $item_id], ['fragment' => 'offers']);
            }
        }

        return [
            'item' => $item,
            'form' => $this->form
        ];
    }

    /**
     * ???????????????? ????????????
     *
     */
    public function deleteAction(): Response
    {
        $id = $this->params()->fromRoute('id');

        // ?????????????? ?????????????????????????? ????????
        $record = $this->modelOffers->find($id);
        if ($record->picture && $this->uploads()->has($record->picture)) {
            $this->uploads()->delete($record->picture);
        }

        if ($this->modelOffers->delete($id) !== false) {
            $this->fm('???????????? ?????????????? ??????????????');
        } else {
            $this->fm('???????????? ???????????????? ????????????', 'error');
        }

        return $this->redirect()->toRoute($this->redirectRoute, ['id' => $record->item], ['fragment' => 'offers']);
    }

    /**
     * ???????????????????????????? ????????????????????????
     *
     * @throws Exception
     */
    public function editAction(): Response|array
    {
        $offer_id = $this->params()->fromRoute('id');
        $offer = $this->modelOffers->find($offer_id);
        $item = $this->modelItems->find($offer->item);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $this->form->setData($post);

            if ($this->form->isValid()) {
                $data = $this->form->getData();

                // ?????????????????? ?????????? ???????????????? ?????????????????????? ????????????
                $main = $data['main'];

                $uploadFile = $this->params()->fromFiles('main')['picture'];
                if ($uploadFile['name']) {
                    $main['picture'] = $this->uploads()->upload($uploadFile);
                }

                $this->modelOffers->update($main, $offer_id);

                // ?????????????????? ???????????????? ??????????????
                $properties = $data['properties'];
                $this->modelOffersPropertiesValues->clearValues($offer_id);
                foreach ($properties as $property_code => $property_value) {
                    if ($property_value) {
                        $property = $this->modelOffersProperties->getProperty($property_code);
                        $this->modelOffersPropertiesValues->insertValue($item->id, $offer_id, $property->id,
                            $property_value);
                    }
                }

                // ?????????????????? ????????
                $offerPrice = $this->modelPrice->getForOffer($offer_id);
                $offerPrice->price = $data['price']['price'] ?: 0;
                $offerPrice->price_sale = $data['price']['price_sale'] ?: null;
                $this->modelPrice->update($offerPrice->getArrayCopy(), $offerPrice->id);

                // ?????????????????? ??????????????
                if ($this->config()['catalog']['options']['use-stock']) {
                    $offerStock = $this->modelStock->getForOffer($offer_id);
                    $offerStock->count = $data['stock']['count'] ?: 0;
                    // ???????? ???????????? ?????????????? ??????????????????
                    if (isset($data['stock']['unit'])) {
                        $offerStock->unit = $data['stock']['unit'];
                    }
                    $this->modelStock->update($offerStock->getArrayCopy(), $offerStock->id);
                }

                $this->fm('???????????????????? ?? ???????????????? ???????????????????? ?????????????? ??????????????????');
                return $this->redirect()->toRoute($this->redirectRoute, ['id' => $offer->item],
                    ['fragment' => 'offers']);
            }
        } else {
            $data = [
                'main'       => $offer->getArrayCopy(),
                'properties' => $this->modelOffersPropertiesValues->fetchValuesForOffer($offer_id)
            ];

            // ?????????????????? ????????
            $data['price'] = $this->modelPrice->getForOffer($offer_id);

            // ?????????????????? ??????????????
            if ($this->config()['catalog']['options']['use-stock']) {
                $data['stock'] = $this->modelStock->getForOffer($offer_id);
            }

            $this->form->setData($data);
        }

        return [
            'item'  => $item,
            'offer' => $offer,
            'form'  => $this->form,
        ];
    }
}
