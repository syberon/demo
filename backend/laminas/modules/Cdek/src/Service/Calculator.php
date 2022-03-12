<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Cdek\Service;


use ArrayObject;
use Exception;

class Calculator
{
    protected ?string $access_token = null;

    protected string $auth_url = 'https://api.cdek.ru/v2/oauth/token?parameters';

    protected string $calc_url = 'https://api.cdek.ru/v2/calculator/tariff';

    protected string $city_url = 'https://api.cdek.ru/v2/location/cities';

    protected string $client_id = '';

    protected string $client_secret = '';

    protected string $grant_type = 'client_credentials';

    protected int $sender_city_id = 0;

    protected int $receiver_city_id = 0;

    protected int $tariff_id = 0;

    protected string $receiver_city_postcode = '';

    protected array $packages = [];


    /**
     * @param string $client_id
     * @param string $client_secret
     */
    public function __construct(string $client_id = '', string $client_secret = '')
    {
        if ($client_id) {
            $this->client_id = $client_id;
        }

        if ($client_secret) {
            $this->client_secret = $client_secret;
        }
    }

    /**
     * Установка URL-адреса для авторизации
     *
     * @param string $auth_url
     */
    public function setAuthUrl(string $auth_url)
    {
        $this->auth_url = $auth_url;
    }

    /**
     * Установка URL-адреса для калькулятора
     *
     * @param string $calc_url
     */
    public function setCalcUrl(string $calc_url)
    {
        $this->calc_url = $calc_url;
    }

    /**
     * Установка URL-адреса для поиска города
     *
     * @param string $city_url
     */
    public function setCityUrl(string $city_url)
    {
        $this->city_url = $city_url;
    }

    /**
     * Установка типа аутентификации
     *
     * @param string|null $grant_type
     */
    public function setGrantType(?string $grant_type)
    {
        $this->grant_type = $grant_type;
    }

    /**
     * Установка идентификатора клиента
     *
     * @param string $client_id
     */
    public function setClientId(string $client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * Установка сектретного ключа клиента
     *
     * @param string $client_secret
     */
    public function setClientSecret(string $client_secret)
    {
        $this->client_secret = $client_secret;
    }

    /**
     * Получение токена авторизации
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    /**
     * Услановка идентификатора города отправителя
     *
     * @param int $sender_city_id
     */
    public function setSenderCityId(int $sender_city_id)
    {
        $this->sender_city_id = $sender_city_id;
    }

    /**
     * Установка идентификатора города получателя
     *
     * @param int $receiver_city_id
     */
    public function setReceiverCityId(int $receiver_city_id)
    {
        $this->receiver_city_id = $receiver_city_id;
    }

    /**
     * Установка почтового кода получателя
     *
     * @param string $receiver_city_postcode
     */
    public function setReceiverCityPostcode(string $receiver_city_postcode)
    {
        $this->receiver_city_postcode = $receiver_city_postcode;
    }

    /**
     * Установака идентификатора тарифа для расчета
     *
     * @param int $tariff_id
     */
    public function setTariffId(int $tariff_id)
    {
        $this->tariff_id = $tariff_id;
    }

    /**
     * Добавление товара к списку расчета
     *
     * @param array $package
     */
    public function addPackage(array $package)
    {
        $this->packages[] = $package;
    }

    /**
     * Очистка списка товаров
     */
    public function clearPackages()
    {
        $this->packages = [];
    }

    /**
     * Расчет стоимости доставки
     *
     * @return mixed
     * @throws Exception
     */
    public function calculate(): mixed
    {
        if (!$this->access_token) {
            $this->authorize();
        }

        if (!count($this->getPackages())) {
            throw new Exception('Не задано ни одного товара для расчета стоимости доставки');
        }

        $request = [
            'type'          => 1,
            'date'          => date("Y-m-d\TH:i:sO"),
            'tariff_code'   => $this->tariff_id,
            'from_location' => new ArrayObject([
                'code' => $this->sender_city_id
            ]),
            'to_location'   => new ArrayObject([
                'code' => $this->receiver_city_id
            ]),
            'packages'      => $this->packages
        ];

        if ($this->receiver_city_postcode) {
            $request['to_location']['postal_code'] = $this->receiver_city_postcode;
        }

        $curlObj = curl_init();

        $header = [];
        $header[] = "Content-Type: application/json; charset=utf-8";
        $header[] = "Authorization: Bearer " . $this->access_token;

        curl_setopt_array($curlObj, [
            CURLOPT_URL            => $this->calc_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($request)
        ]);

        $response = curl_exec($curlObj);
        curl_close($curlObj);

        return json_decode($response);
    }

    /**
     * Авторизация на сервисе расчета стоимости доставки
     *
     * @throws Exception
     */
    public function authorize()
    {
        if (!$this->client_id || !$this->client_secret) {
            throw new Exception('Не заданы параметры авторизации');
        }


        $postFieldsAr = [
            "grant_type"    => $this->grant_type,
            "client_id"     => $this->client_id,
            "client_secret" => $this->client_secret
        ];

        $curlObj = curl_init();

        curl_setopt_array($curlObj, [
            CURLOPT_URL            => $this->auth_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($postFieldsAr)
        ]);

        $response = curl_exec($curlObj);
        curl_close($curlObj);

        $authObj = json_decode($response);
        if ($authObj === false) {
            throw new Exception('Ошибка, сервер CDEK вернул ответ не в JSON формате');
        }

        if (isset($authObj->access_token) && !empty($authObj->access_token)) {
            $this->access_token = $authObj->access_token;
        } else {
            throw new Exception('Ошибка, ответ сервера CDEK не содержит свойства access_token или этот параметр пустой');
        }
    }

    /**
     * Получение списка товаров
     *
     * @return array
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * Поиск идентификатора города по названию и почтовому коду
     *
     * @param string $city_name
     * @param string $postcode
     * @return mixed
     * @throws Exception
     */
    public function findCity(string $city_name, string $postcode): mixed
    {
        if (!$this->access_token) {
            $this->authorize();
        }

        $request = [
            'size'        => 1,
            'city'        => $city_name,
            'postal_code' => $postcode,
        ];

        $curlObj = curl_init();

        $header = [];
        $header[] = "Content-Type: application/json; charset=utf-8";
        $header[] = "Authorization: Bearer " . $this->access_token;

        curl_setopt_array($curlObj, [
            CURLOPT_URL            => $this->city_url . '?' . http_build_query($request),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $header,
        ]);

        $response = curl_exec($curlObj);
        curl_close($curlObj);

        $response = json_decode($response);
        if (!count($response)) {
            return null;
        }

        return array_pop($response);
    }
}