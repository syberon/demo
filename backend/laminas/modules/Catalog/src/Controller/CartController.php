<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use Laminas\Json\Json;
use Laminas\Session\Container;
use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\MtAbstractActionController;
use MtModules\Catalog\Form\Order;
use MtModules\Catalog\Model\Cart;
use MtModules\Catalog\Model\Coupons;
use MtModules\Catalog\Model\Discount;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Stock;

/**
 * Class CartController
 *
 * @package MtModules\Catalog\Controller
 *
 */
class CartController extends MtAbstractActionController
{
    protected Container $container;

    /**
     * CartController constructor.
     *
     * @param Items $modelItems
     * @param Cart $modelCart
     * @param Offers $modelOffers
     * @param Pictures $modelPictures
     * @param Stock $modelStock
     * @param OffersPropertiesValues $modelOffersPropertiesValues
     * @param OffersProperties $modelOffersProperties
     * @param Discount $modelDiscount
     * @param Coupons $modelCoupons
     */
    public function __construct(
        protected Items $modelItems,
        protected Cart $modelCart,
        protected Offers $modelOffers,
        protected Pictures $modelPictures,
        protected Stock $modelStock,
        protected OffersPropertiesValues $modelOffersPropertiesValues,
        protected OffersProperties $modelOffersProperties,
        protected Discount $modelDiscount,
        protected Coupons $modelCoupons
    )
    {
        $this->container = new Container('coupon_container');
    }

    /**
     * Поиск и применение купона
     */
    public function applycouponAction(): JsonModel|bool
    {
        if ($this->getRequest()->isPost()) {
            $coupon_code = $this->params()->fromPost('coupon');
            $coupon = $this->modelCoupons->getCoupon($coupon_code);
            if (!$coupon) {
                return new JsonModel([
                    'code'    => 0,
                    'message' => 'Указанный купон не найден'
                ]);
            } else {
                switch ($coupon->type) {
                    case 'multi':
                        if (!$coupon->count) {
                            return new JsonModel([
                                'code'    => 0,
                                'message' => 'Данный купон уже использован максимальное количество раз'
                            ]);
                        }
                        break;
                    case 'date':
                        if ($coupon->date_start > date('Y-m-d') || $coupon->date_stop < date('Y-m-d')) {
                            return new JsonModel([
                                'code'    => 0,
                                'message' => 'Срок действия купона еще не наступил или уже закончился'
                            ]);
                        }

                }

                if ($coupon->discount_type == 'sum') {
                    if ($coupon->discount > $this->modelCart->getTotalPrice()) {
                        return new JsonModel([
                            'code'    => 0,
                            'message' => 'Сумма купона превышает сумму в корзине'
                        ]);
                    }
                }

                $this->container->offsetSet('coupon', $coupon);

                return new JsonModel([
                    'coupon' => $coupon,
                    'code'   => 1
                ]);
            }
        }
        return false;
    }

    public function indexAction(): array
    {
        $orderForm = new Order();

        if ($this->acl()->hasIdentity()) {
            $mainFields = [
                'display_name',
                'email',
                'phone',
                'delivery',
                'delivery-block' => [
                    'city',
                    'zip_code',
                    'address'
                ]
            ];


            foreach ($mainFields as $key => $field) {
                if (is_scalar($field)) {
                    $orderForm->get($field)->setValue($this->acl()->getIdentity()->$field);
                } else {
                    foreach ($field as $subfield) {
                        $orderForm->get($key)->get($subfield)->setValue($this->acl()->getIdentity()->$subfield);
                    }
                }
            }
        }

        return [
            'orderForm' => $orderForm
        ];
    }

    /**
     * Пересчет суммы в корзине
     */
    public function updateAction(): JsonModel
    {
        $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
        $items = $post['items'];

        foreach ($items as $token => $item) {
            $this->modelCart->update($token, $item['count']);
        }

        if ($this->config()['catalog']['options']['use-cart-discount']) {
            $sum = $this->modelCart->getTotalPrice();
            $discount = $this->modelDiscount->getDiscount($sum);
        }

        $this->updateCoupon();

        return new JsonModel([
            'discount' => $discount->discount ?? 0
        ]);
    }

    /**
     * Если сумма заказа меньше суммы купона, то отменяем купон
     */
    protected function updateCoupon()
    {
        // Отмена купона, если сумма в корзине меньше суммы купона
        if ($this->config()['catalog']['options']['use-cart-coupons']) {
            $coupon = $this->container->offsetGet('coupon');
            if ($coupon && $coupon->discount_type == 'sum') {
                $sum = $this->modelCart->getTotalPrice();

                // Если используется скидка по сумме корзины
                if ($this->config()['catalog']['options']['use-cart-discount']) {
                    $discount = $this->modelDiscount->getDiscount($sum);
                    $sum = $sum * ((100 - $discount->discount) / 100);
                }

                var_dump($sum);
                var_dump($coupon->discount);

                if ($sum < $coupon->discount) {
                    echo "unset";
                    $this->container->offsetUnset('coupon');
                }

                exit;
            }

        }
    }

    /**
     * Добавление наименования в корзину
     */
    public function addAction(): JsonModel
    {
        $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
        $item = $post['item'];

        // Проверка доступного остатка товара на складе перед добавлением
        $existItemToken = $this->modelCart->getItemToken($item['item'], $item['offer']);
        if ($existItemToken) {
            $existItem = $this->modelCart->getItem($existItemToken);
            $itemStock = $this->modelStock->getForOffer($item['offer']);
            if ($existItem['count'] >= $itemStock->count) {
                return new JsonModel(['code' => 0]);
            }
        }

        $cartItem = [
            'item'  => $item['item'],
            'offer' => $item['offer'],
            'count' => 1,
            'price' => $item['price_sale'] ?: $item['price']
        ];
        $this->modelCart->insert($cartItem);
        return new JsonModel(['code' => 1]);
    }

    /**
     * Получение статуса корзины
     */
    public function getstatusAction(): JsonModel
    {
        $status = [
            'items' => $this->modelCart->getCount(),
            'price' => $this->modelCart->getTotalPrice()
        ];

        return new JsonModel($status);
    }

    /**
     * Получение списка наименований в корзине
     */
    public function getlistAction(): JsonModel
    {
        $items = $this->modelCart->getItemsList();

        foreach ($items as &$item) {
            $item['info'] = $this->modelItems->find($item['item']);
            $item['url'] = $this->url()->fromRoute('catalogrouter', ['item' => $item['item']]);

            // Выборка и обработка фотографий
            $picture = $this->modelPictures->fetchMain($item['item']);
            if ($picture && $this->uploads()->has($picture->picture)) {
                $item['picture'] = $this->imgUrl($this->uploads()->get($picture->picture)->getPublicUrl(),
                    'catalog-item-list');
            } else {
                $item['picture'] = null;
            }

            if ($this->config()['catalog']['options']['use-offers']) {
                if ($item['offer']) {
                    $item['offer'] = $this->modelOffers->find($item['offer']);
                    $propertiesValues = $this->modelOffersPropertiesValues->fetchValuesForOffer($item['offer']->id);
                    $properties = [];
                    foreach ($propertiesValues as $code => $value) {
                        $property = $this->modelOffersProperties->getProperty($code);
                        $properties[$property->name] = $value;
                    }

                    $item['properties'] = $properties;
                }
            }

            if ($this->config()['catalog']['options']['use-stock']) {
                if ($this->config()['catalog']['options']['use-offers']) {
                    $item['stock'] = $this->modelStock->getForOffer($item['offer']->id);
                } else {
                    $item['stock'] = $this->modelStock->getForItem($item['item']);
                }
            } else {
                $item['stock']->count = 999;
            }
        }

        // Если используется скидка по сумме корзины
        if ($this->config()['catalog']['options']['use-cart-discount']) {
            $sum = $this->modelCart->getTotalPrice();
            $discount = $this->modelDiscount->getDiscount($sum);
        }

        // Если используются купоны
        if ($this->config()['catalog']['options']['use-cart-coupons']) {
            $coupon = $this->container->offsetGet('coupon');
        }

        return new JsonModel([
            'items'    => $items,
            'count'    => count($items),
            'discount' => $discount->discount ?? 0,
            'coupon'   => $coupon ?? null
        ]);
    }

    /**
     * Удаление наименования из корзины
     */
    public function deleteAction(): JsonModel
    {
        $token = $this->params()->fromRoute('token');
        if ($token) {
            $this->modelCart->delete($token);
        }
        $this->updateCoupon();

        return new JsonModel();
    }

    /**
     * Очистка корзины
     */
    public function clearAction(): JsonModel
    {
        $this->modelCart->clear();
        $this->container->offsetUnset('coupon');
        return new JsonModel();
    }

}
