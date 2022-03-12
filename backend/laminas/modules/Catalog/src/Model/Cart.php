<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use Laminas\Session\Container;

/**
 * Модель работы с корзиной
 *
 * $item[
 *     'item' => n
 *     'offer' => n | null
 *     'count' => n
 *     'price' => n
 * ]
 */
class Cart
{
    protected Container $container;

    protected array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->container = new Container(self::class);

        // Установка пустого массива, если он не создан ранее
        if (!$this->container->offsetExists('products')) {
            $this->container->offsetSet('products', []);
        }

        $this->config = $config;
    }

    /**
     * Добавление товара в корзину
     *
     * @param array $item
     */
    public function insert(array $item)
    {
        $insertItem = true;
        if ($this->config['catalog']['options']['cart']['update_exist'] &&
            $this->itemExist($item['item'], $item['offer'])) {
            // Обновляем количество товаров, если он уже добавлен ранее в корзину
            $token = $this->getItemToken($item['item'], $item['offer']);
            $existItem = $this->container['products'][$token];
            $this->update($token, $existItem['count'] + $item['count']);
            $insertItem = false;
        }

        if ($insertItem) {
            $token = sha1($item['item'] . $item['offer'] . $item['count'] . time());
            $this->container['products'][$token] = $item;
        }
    }

    /**
     * Обновление количества для товара в корзине
     *
     * @param string $token
     * @param int $count
     */
    public function update(string $token, int $count)
    {
        if (isset($this->container['products'][$token])) {
            $this->container['products'][$token]['count'] = $count;
        }
    }

    /**
     * Удаление товара из корзины
     *
     * @param string $token
     */
    public function delete(string $token)
    {
        if (isset($this->container['products'][$token])) {
            unset($this->container['products'][$token]);
        }
    }

    /**
     * Очистка корзины
     */
    public function clear()
    {
        $this->container->offsetSet('products', []);
    }

    /**
     * Получение наименования из корзины
     *
     * @param string $token
     * @return array|null
     */
    public function getItem(string $token): ?array
    {
        foreach ($this->container->offsetGet('products') as $itemToken => $item) {
            if ($token == $itemToken) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Получение количество наименований в корзине
     *
     * @return int
     */
    public function getCount(): int
    {
        $count = 0;
        foreach ($this->container['products'] as $item) {
            $count += $item['count'];
        }
        return $count;
    }

    /**
     * Получения общей цены товаров в корзине
     *
     * @return float
     */
    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->container->offsetGet('products') as $item) {
            $total += $item['price'] * $item['count'];
        }
        return $total;
    }

    /**
     * Получение списка товаров в корзине
     *
     * @return array
     */
    public function getItemsList(): array
    {
        $items = [];

        foreach ($this->container->offsetGet('products') as $token => $item) {
            $items[$token] = $item;
        }
        return $items;
    }

    /**
     * Поиск токена наименования в корзине по штрих-коду
     *
     * @param int $item_id
     * @param int|null $offer_id
     * @return null|string
     */
    public function getItemToken(int $item_id, ?int $offer_id = null): ?string
    {
        foreach ($this->container->offsetGet('products') as $token => $item) {
            if ($offer_id) {
                if ($item['item'] == $item_id && $item['offer'] == $offer_id) {
                    return $token;
                }
            }
            else {
                if ($item['item'] == $item_id) {
                    return $token;
                }
            }
        }
        return null;
    }

    /**
     * Проверка присутствия товара в корзине
     *
     * @param int $item_id
     * @param int|null $offer_id
     * @return bool
     */
    protected function itemExist(int $item_id, ?int $offer_id = null): bool
    {
        foreach ($this->container->offsetGet('products') as $item) {
            if ($offer_id) {
                if ($item['item'] == $item_id && $item['offer'] == $offer_id) {
                    return true;
                }
            }
            else {
                if ($item['item'] == $item_id) {
                    return true;
                }
            }
        }
        return false;
    }
}