<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Form;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use MtModules\Catalog\Form\Item;
use MtModules\Catalog\Model\Currencies;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\ItemsProperties;
use MtModules\Catalog\Model\ItemsPropertiesDefaults;
use MtModules\Catalog\Model\ItemsPropertiesValues;
use MtModules\Catalog\Model\Stock;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Item
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Item
    {
        $config = $container->get('config');

        /** @var ItemsProperties $modelItemsProperties */
        $modelItemsProperties = $container->get(ItemsProperties::class);

        /** @var ItemsPropertiesDefaults $modelItemsPropertiesDefaults */
        $modelItemsPropertiesDefaults = $container->get(ItemsPropertiesDefaults::class);

        /** @var ItemsPropertiesValues $modelItemsPropertiesValues */
        $modelItemsPropertiesValues = $container->get(ItemsPropertiesValues::class);

        /** @var Currencies $modelCurrencies */
        $modelCurrencies = $container->get(Currencies::class);

        /** @var Stock $modelStock */
        $modelStock = $container->get(Stock::class);

        $form = new Item();

        /** @var Items $model */
        $model = $container->get(Items::class);
        $form->setInputFilter($model->getInputFilter());


        // Генерация полей свойств товара
        $properties = $modelItemsProperties->fetchAll();
        foreach ($properties as $property) {
            if ($property->type == 'select') {
                $property->values = $modelItemsPropertiesDefaults->fetchByProperty($property->id, true);
            }
            if ($property->type == 'text') {
                $property->values = $modelItemsPropertiesValues->fetchUniqueValues($property->id);
            }
        }
        $form->generateProperties($properties);


        // Если не используются торговые предложения
        if (!$config['catalog']['options']['use-offers']) {

            // Если используется валюта
            if ($config['catalog']['options']['use-currency']) {
                $currencies = $modelCurrencies->getList();
                $form->generatePrice($currencies);
            } else {
                $form->generatePrice();
            }

            // Если используется база наличия
            if ($config['catalog']['options']['use-stock']) {
                $units = $modelStock->getUnits();
                $form->generateStock($units);
            }
        }

        // Если не используется краткое описание
        if (!$config['catalog']['options']['items-use-preview']) {
            $form->get('main')->remove('preview');
        }

        // Если не используется поле с артикулом
        if (!$config['catalog']['options']['items-use-article']) {
            $form->get('main')->remove('article');
        }

        return $form;
    }
}