<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Cdek\Controller;

use Exception;
use MtCms\Params\Model\System;
use MtLib\Base\Controller\MtAbstractActionController;
use MtModules\Cdek\Form\Config;
use Laminas\Json\Json;
use Laminas\View\Model\JsonModel;
use MtModules\Cdek\Service\Calculator;


/**
 * Class IndexController
 *
 * @package MtModules\Cdek\Controller
 */
class IndexController extends MtAbstractActionController
{
    protected Calculator $cdekCalculator;

    /**
     * IndexController constructor.
     *
     * @param System $modelParams
     */
    public function __construct(
        protected System $modelParams
    )
    {
        $params = $this->modelParams->getParams('cdek');
        $this->cdekCalculator = new Calculator($params['account'], $params['password']);

    }

    /**
     * Страница конфигурации калькулятора СДЭК
     *
     */
    public function configAction(): array
    {
        $config = [];
        $form = new Config();
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost()->toArray());
            if ($form->isValid()) {
                $data = $form->getData();
                $this->modelParams->processParams($data, 'cdek');
                $this->fm('Параметры успешно сохранены');
            }
        } else {
            $config = $this->modelParams->getParams('cdek');
            $form->setData($config);
        }

        return [
            'config' => $config,
            'form'   => $form
        ];
    }

    /**
     * Получение списка городов для автокомплита
     */
    public function getAutocompleteAction(): JsonModel
    {
        $city_name = $this->params()->fromPost('city');
        $url = $this->config()['mt-cdek']['autocomplete-url'] . '?q=' . $city_name;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        try {
            $data = curl_exec($ch);
        } catch (Exception) {
            return new JsonModel([
                'code' => 0
            ]);

        }
        curl_close($ch);

        $cities = Json::decode($data);

        return new JsonModel(['cities' => $cities]);
    }

    /**
     * Получение информации о городе, если не задан
     *
     * @throws Exception
     */
    public function findCityAction(): JsonModel
    {
        $city_name = $this->params()->fromPost('city_name');
        $zip_code = $this->params()->fromPost('zip_code');

        $response = $this->cdekCalculator->findCity($city_name, $zip_code);

        if ($response) {
            return new JsonModel([
                'status'  => 1,
                'city_id' => $response->code
            ]);
        }

        return new JsonModel(['status' => 0]);
    }

    /**
     * Рассчет стоимости отправки заказа
     * @throws Exception
     */
    public function calculateAction(): JsonModel
    {
        $city_id = $this->params()->fromPost('city_id');
        $zip_code = $this->params()->fromPost('zip_code');
        $total_price = $this->params()->fromPost('total_price');
        $params = $this->modelParams->getParams('cdek');

        if ($params['sender_city_id'] == $city_id) {
            return new JsonModel([
                'status'     => 2,
                'price'      => 0,
                'selfpickup' => true
            ]);
        }

        $this->cdekCalculator->setSenderCityId($params['sender_city_id']);
        $this->cdekCalculator->setTariffId($params['tariff_id']);
        $this->cdekCalculator->addPackage([
            'weight' => $params['parcel_weight'],
            'height' => $params['parcel_height'],
            'width'  => $params['parcel_width'],
            'length' => $params['parcel_length']
        ]);

        if ($city_id) {
            $this->cdekCalculator->setReceiverCityId($city_id);
        }

        if ($zip_code) {
            $this->cdekCalculator->setReceiverCityPostCode($zip_code);
        }

        try {
            $response = $this->cdekCalculator->calculate();
        }
        catch (Exception) {
            return new JsonModel([
                'status'     => 0,
                'selfpickup' => false,
                'price'      => -1
            ]);
        }

        $delivery = [
            'status'     => 1,
            'selfpickup' => false,
            'price'      => $response->total_sum,
            'min'        => $response->period_min,
            'max'        => $response->period_max
        ];

        if ($total_price >= $params['free_sum']) {
            // Если достигнута сумма заказа, для бесплатной доставки
            $delivery['price'] = 0;
        }

        return new JsonModel($delivery);
    }
}
