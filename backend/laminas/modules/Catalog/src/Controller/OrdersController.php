<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use ArrayObject;
use DateTime;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Json\Json;
use Laminas\Session\Container;
use Laminas\View\Model\JsonModel;
use Laminas\View\Renderer\PhpRenderer;
use MtCms\User\Model\Users;
use MtLib\Base\Controller\AbstractCrudController;
use MtModules\Catalog\Form\Order;
use MtModules\Catalog\Form\Payment;
use MtModules\Catalog\Model\Cart;
use MtModules\Catalog\Model\Coupons;
use MtModules\Catalog\Model\Discount;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Orders;
use MtModules\Catalog\Model\Price;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class OrdersController
 * @package MtModules\Catalog\Controller
 *
 */
class OrdersController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/orders';

    /**
     * Используется ли разбиение на страницы
     */
    protected bool $usePagination = true;

    protected int $paginationCount = 100;

    protected Container $container;

    /**
     * AbstractCrudController constructor.
     *
     * @param Orders $model
     * @param Items $modelItems
     * @param Cart $modelCart
     * @param PHPMailer $mailService
     * @param PhpRenderer $viewRenderer
     * @param Offers $modelOffers
     * @param OffersProperties $modelOffersProperties
     * @param OffersPropertiesValues $modelOffersPropertiesValues
     * @param Price $modelPrice
     * @param Users $modelUsers
     * @param Discount $modelDiscount
     * @param Coupons $modelCoupons
     * @param Order $form
     */
    public function __construct(
        Orders                           $model,
        Order                            $form,
        protected Items                  $modelItems,
        protected Cart                   $modelCart,
        protected PHPMailer              $mailService,
        protected PhpRenderer            $viewRenderer,
        protected Offers                 $modelOffers,
        protected OffersProperties       $modelOffersProperties,
        protected OffersPropertiesValues $modelOffersPropertiesValues,
        protected Price                  $modelPrice,
        protected Users                  $modelUsers,
        protected Discount               $modelDiscount,
        protected Coupons                $modelCoupons
    )
    {
        $this->container = new Container('coupon_container');
        $this->model = $model;
        $this->form = $form;
    }

    /**
     * Вывод списка записей
     *
     * @return array
     */
    public function indexAction(): array
    {
        $orders = $this->model->fetchByUser($this->acl()->getIdentity()->id);
        // Распаковка информации о товарах заказа
        foreach ($orders as $order) {
            $order->items = unserialize($order->items);
        }

        return [
            'orders'   => $orders,
            'delivery' => $this->form->delivery_method,
            'payment'  => $this->form->payment_method,
            'status'   => $this->form->order_status
        ];
    }

    /**
     * Просмотр информации о заказе
     */
    public function viewAction(): array
    {
        $order = $this->model->find($this->params()->fromRoute('id'));
        $order->items = unserialize($order->items);
        $payForm = $this->generatePayForm($order->id);
        return [
            'order'           => $order,
            'payForm'         => $payForm,
            'delivery_method' => $this->form->delivery_method,
            'payment_method'  => $this->form->payment_method,
            'order_status'    => $this->form->order_status

        ];
    }

    /**
     * Генерация формы для оплаты заказа
     *
     * @param int $order_id
     * @return Payment
     */
    protected function generatePayForm(int $order_id): Payment
    {
        $order = $this->model->find($order_id);

        $sign = sprintf("%s:%s:%s:%s", $this->config()['catalog']['payment']['login'],
            (int)$order->price + (int)$order->price_delivery, $order->id, $this->config()['catalog']['payment']['password1']);
        $crc = md5($sign);

        $payForm = new Payment();
        $payForm->setAttribute('action', $this->config()['catalog']['payment']['payUrl']);
        $payForm->get('MerchantLogin')->setValue($this->config()['catalog']['payment']['login']);
        $payForm->get('OutSum')->setValue((int)$order->price + (int)$order->price_delivery);
        $payForm->get('InvDesc')->setValue("Оплата заказа №" . $order->id);
        $payForm->get('InvId')->setValue($order->id);
        $payForm->get('SignatureValue')->setValue($crc);

        if ($this->config()['catalog']['payment']['payTest']) {
            $payForm->get('IsTest')->setValue(1);
        } else {
            $payForm->remove('IsTest');
        }
        return $payForm;
    }

    /**
     * Вывод списка заказов для администрирования
     *
     */
    public function listAction(): array
    {
        return [];
    }

    /**
     * Получение списка заказов в формате JSON для администрирования
     */
    public function getitemsAction(): JsonModel
    {
        $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
        $filterData = $post['filter'];
        $paramsData = $post['params'];

        $this->model->setItemsPerPage($paramsData['pageSize'] ?: 50);
        $this->model->setCurrentPage($filterData['page']);

        $orderField = null;
        if ($paramsData['order']['field']) {
            $orderField = $paramsData['order']['field'] . ' ' . $paramsData['order']['direction'];
        }

        $orders = $this->model->fetchList($filterData['fields'], $orderField);

        foreach ($orders as $order) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $order->date);
            $order->format_date = $date->format("d.m.Y H:i:s");
            $order->status_text = $this->form->order_status[$order->status];
        }

        return new JsonModel([
            'pagination' => [
                'current' => $orders->getCurrentPageNumber(),
                'count'   => $orders->count()
            ],
            'items'      => $orders->getCurrentItems(),

        ]);
    }

    public function getdataAction(): JsonModel
    {
        $order_id = $this->params()->fromRoute('id');
        $order = $this->model->find($order_id);
        $order->items = unserialize($order->items);
        array_walk($order->items, function ($item) {
            $item->link = $this->url()->fromRoute('catalogrouter', ['item' => $item->id]);
        });

        return new JsonModel([
            'order' => $order
        ]);
    }

    /**
     * Добавление заказа
     * @throws Exception
     */
    public function addAction(): JsonModel|bool
    {

        if ($this->getRequest()->isPost()) {
            $this->form->setData($this->getRequest()->getPost());
            if ($this->form->isValid()) {
                $data = $this->form->getData();

                // Сохранение информации для доставки, если выбрана галка
                if ($data['save_delivery']) {
                    // Если выбрана доставка по России, то обновляем данные доставки
                    if ($data['delivery'] == 1) {
                        $profileData = $data['delivery-block'];
                    }
                    $profileData['phone'] = $data['phone'];
                    $profileData['delivery'] = $data['delivery'];
                    unset($profileData['price_delivery']);
                    $this->modelUsers->update($profileData, $this->acl()->getIdentity()->id);
                }

                $data = array_merge($data, $data['delivery-block']);
                unset($data['save_delivery']);
                unset($data['city_code']);
                unset($data['delivery-block']);

                $data['price'] = $this->modelCart->getTotalPrice();
                $price_orig = $this->modelCart->getTotalPrice();


                // Применение скидки по общей сумме
                if ($this->config()['catalog']['options']['use-cart-discount']) {
                    $discount = $this->modelDiscount->getDiscount($data['price']);
                    if ($discount) {
                        $data['price_discount'] = $price_orig - $price_orig * (100 - $discount->discount) / 100;
                        $data['price'] -= $data['price_discount'];
                    }
                }

                // Применение скидки по общей сумме
                if ($this->config()['catalog']['options']['use-cart-coupons']) {
                    $coupon = $this->container->offsetGet('coupon');
                    if ($coupon) {
                        $data['price_coupon'] = match ($coupon->discount_type) {
                            'sum' => $coupon->discount,
                            'percent' => $price_orig - $price_orig * (100 - $coupon->discount) / 100,
                        };

                        // Уменьшаем доступное количестов раз использования купона
                        if ($coupon->type == 'multi') {
                            $this->modelCoupons->updateCoupon($coupon->id);
                        }

                        $data['price'] -= $data['price_coupon'];
                    }
                }

                $data['count'] = $this->modelCart->getCount();
                $cartItems = $this->modelCart->getItemsList();

                // Обработка всех наименований (выборка цен и свойств)
                $items = [];
                foreach ($cartItems as $cartItem) {
                    $item = $this->prepareItem($cartItem['item'], $cartItem['offer']);
                    $item->price = $cartItem['price'];
                    $item->count = $cartItem['count'];
                    $items[] = $item;
                }
                $data['items'] = serialize($items);

                // Установка статуса заказа в зависимости от выбранного способа оплаты
                $data['status'] = match ($data['payment']) {
                    1 => 1,
                    2 => 2,
                };

                $data['user'] = $this->acl()->getIdentity()->id;
                $id = $this->model->insert($data);

                if ($id !== false) {
                    $this->sendMessage($id);
                    $this->modelCart->clear();
                    $this->container->offsetUnset('coupon');
                    return new JsonModel([
                        'status' => 1,
                        'id'     => $id
                    ]);
                } else {
                    return new JsonModel([
                        'status' => 0
                    ]);
                }
            }
        }
        return false;
    }

    /**
     * Подготовар наименования (заполнение свойств)
     *
     * @param int $item_id
     * @param int|null $offer_id
     * @return ArrayObject
     */
    protected function prepareItem(int $item_id, ?int $offer_id = null): ArrayObject
    {
        $item = $this->modelItems->find($item_id);
        if ($this->config()['catalog']['options']['use-offers'] && $offer_id) {
            $offer = $this->modelOffers->find($offer_id);
            $propertiesValues = $this->modelOffersPropertiesValues->fetchValuesForOffer($offer->id);
            $properties = [];
            foreach ($propertiesValues as $code => $value) {
                $property = $this->modelOffersProperties->getProperty($code);
                $properties[$property->name] = $value;
            }
            $item->link = $this->url()->fromRoute('catalogrouter', ['item' => $item->id]);
            $item->offer = $offer;
            $item->properties = $properties;
        }

        return $item;
    }

    /**
     * Отправка сообщения об успешном заполнении формы
     *
     * @param mixed $id
     * @return false|void
     * @throws Exception
     */
    protected function sendMessage(mixed $id)
    {
        $order = $this->model->find($id);
        $order->items = unserialize($order->items);

        $recepients_manager = explode(",", $this->appParams('managers', 'core-mail'));
        $recepient_user = $order->email;

        array_walk($recepients_manager, function (&$value) {
            $value = trim($value);
        });

        $template = [
            'user'  => 'mt-modules/catalog/email/neworder-user',
            'admin' => 'mt-modules/catalog/email/neworder-admin'
        ];

        $this->mailService->Body = $this->viewRenderer->partial($template['user'], [
            "order"           => $order,
            'delivery_method' => $this->form->delivery_method,
            'payment_method'  => $this->form->payment_method,
        ]);

        $this->mailService->Subject = $this->config()['catalog']['order_subject'];
        $this->mailService->setFrom($this->appParams('from', 'core-mail'));
        $this->mailService->addAddress($recepient_user);
        try {
            $this->mailService->send();
        } catch (Exception) {
            echo "Message could not be sent. Mailer Error: {$this->mailService->ErrorInfo}";
            return false;
        }

        $this->mailService->Body = $this->viewRenderer->partial($template['admin'], [
            "order"           => $order,
            'delivery_method' => $this->form->delivery_method,
            'payment_method'  => $this->form->payment_method,
        ]);
        $this->mailService->clearAddresses();
        foreach ($recepients_manager as $recepient) {
            $this->mailService->addAddress($recepient);
        }

        try {
            $this->mailService->send();
        } catch (Exception) {
            echo "Message could not be sent. Mailer Error: {$this->mailService->ErrorInfo}";
            return false;
        }
    }

    /**
     * Добавление наименования к заказу
     */
    public function additemAction(): JsonModel
    {
        $offer_code = $this->params()->fromRoute('id');
        $offer = $this->modelOffers->getByCode($offer_code);
        if ($offer) {
            $item = $this->prepareItem($offer->item, $offer->id);
            $item->count = 1;
            $price = $this->modelPrice->getForOffer($offer->id);
            $item->price = $price->price_sale ?: $price->price;
            return new JsonModel([
                'code' => 1,
                'item' => Json::encode($item)
            ]);
        } else {
            return new JsonModel(['code' => 0]);
        }
    }

    /**
     * Обновление информации о заказе после внесенных изменений
     * @throws Exception
     */
    public function updateAction(): JsonModel
    {
        $order_id = $this->params()->fromRoute('id');
        if ($this->getRequest()->isPost()) {
            $oldData = $this->model->find($order_id);
            $data = $this->params()->fromPost();
            $items = Json::decode($data['items']);
            $itemsCount = 0;
            foreach ($items as $item) {
                $itemsCount += $item->count;
            }
            $data['items'] = serialize($items);
            $data['count'] = $itemsCount;
            if (isset($data['delivery-block'])) {
                $data = array_merge($data, $data['delivery-block']);
            }

            unset($data['delivery-block']);
            unset($data['city_code']);
            $this->model->update($data, $order_id);

            if ($oldData->status != $data['status']) {
                $this->sendStatusMessage($order_id);
            }

            return new JsonModel([
                'code' => 1
            ]);
        }

        return new JsonModel([
            'code' => 0
        ]);
    }

    /**
     * Отправка сообщения об изменившемся статусе заказа
     *
     * @param $id
     * @throws Exception
     */
    protected function sendStatusMessage($id)
    {
        $order = $this->model->find($id);
        $order->items = unserialize($order->items);

        $this->mailService->Body = $this->viewRenderer->partial('mt-modules/catalog/email/changestatus-user', [
            "order"           => $order,
            "order_status"    => $this->form->order_status,
            'delivery_method' => $this->form->delivery_method,
            'payment_method'  => $this->form->payment_method,
        ]);

        $this->mailService->Subject = 'Изменение статуса заказа №' . $order->id;
        $this->mailService->setFrom($this->appParams('from', 'core-mail'));
        $this->mailService->addAddress($order->email);

        try {
            $this->mailService->send();
        } catch (Exception) {
            echo "Message could not be sent. Mailer Error: {$this->mailService->ErrorInfo}";
        }
    }

    /**
     * Удаление записи
     */
    public function deleteAction(): JsonModel
    {
        $id = $this->params()->fromRoute('id');
        $this->model->delete($id);
        return new JsonModel([
            'status' => 1
        ]);
    }

    /**
     * Редактирование заказа
     *
     */
    public function editAction(): array
    {
        $this->form->prepareEdit();
        $order = $this->model->find($this->params()->fromRoute('id'));
        $orderData = $order->getArrayCopy();

        $fields = ['city', 'zip_code', 'address', 'price_delivery'];
        foreach ($fields as $field) {
            $orderData['delivery-block'][$field] = $orderData[$field];
        }
        $this->form->setData($orderData);

        return [
            'form'  => $this->form,
            'order' => $order,
        ];
    }

    /**
     * Страница ошибки оплаты
     */
    public function payfailAction()
    {
    }

    /**
     * Генерация формы оплаты заказа
     */
    public function payAction(): array
    {
        $order = $this->model->find($this->params()->fromRoute('id'));
        $order->items = unserialize($order->items);
        $payForm = $this->generatePayForm($order->id);
        return [
            'order'           => $order,
            'payForm'         => $payForm,
            'delivery_method' => $this->form->delivery_method,
            'payment_method'  => $this->form->payment_method,
            'order_status'    => $this->form->order_status

        ];
    }

    /**
     * Страница успешной оплаты
     */
    public function paysuccessAction()
    {
    }

    /**
     * Уведомление о проведенной оплате
     * @throws Exception
     */
    #[NoReturn]
    public function resultAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (isset($data['OutSum']) && isset($data['InvId']) && isset($data['SignatureValue'])) {
                $sign = sprintf("%s:%s:%s", $data['OutSum'], $data['InvId'],
                    $this->config()['catalog']['payment']['password2']);
                $crc = strtoupper(md5($sign));
                if ($crc == $data['SignatureValue']) {
                    $order = $this->model->find($data['InvId']);
                    $orderData = [
                        'status' => 2
                    ];
                    $this->model->update($orderData, $order->id);
                    $this->sendPaymentMessage($order->id);
                    echo "OK" . $data['InvId'];
                }
            }
        }
        exit;
    }

    /**
     * Отправка сообщения об изменившемся статусе заказа
     *
     * @param $id
     * @throws Exception
     */
    protected function sendPaymentMessage($id)
    {
        $order = $this->model->find($id);
        $order->items = unserialize($order->items);
        $recepients_manager = explode(",", $this->appParams('managers', 'core-mail'));
        array_walk($recepients_manager, function (&$value) {
            $value = trim($value);
        });

        $this->mailService->Body = $this->viewRenderer->partial('mt-modules/catalog/email/payment-admin', [
            "order"           => $order,
            "order_status"    => $this->form->order_status,
            'delivery_method' => $this->form->delivery_method,
            'payment_method'  => $this->form->payment_method,
        ]);

        $this->mailService->Subject = "По заказу №'$order->id поступил платеж";
        $this->mailService->setFrom($this->appParams('from', 'core-mail'));
        foreach ($recepients_manager as $recepient) {
            $this->mailService->addAddress($recepient);
        }
        try {
            $this->mailService->send();
        } catch (Exception) {
            echo "Message could not be sent. Mailer Error: {$this->mailService->ErrorInfo}";
        }
    }

    /**
     * Страница с успешный оформлением заказа
     */
    public function successAction(): array
    {
        $order = $this->model->find($this->params()->fromRoute('id'));
        $payForm = $this->generatePayForm($order->id);
        return [
            'payForm' => $payForm,
            'order'   => $order
        ];
    }
}
