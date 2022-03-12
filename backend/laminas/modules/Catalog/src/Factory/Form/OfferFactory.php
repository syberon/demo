<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Form;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Form\Offer;
use MtModules\Catalog\Model\Currencies;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesDefaults;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Stock;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OfferFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return Offer
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Offer
    {
        $config = $container->get('config');

        /** @var OffersProperties $modelOffersProperties */
        $modelOffersProperties = $container->get(OffersProperties::class);

        /** @var OffersPropertiesDefaults $modelOffersPropertiesDefaults */
        $modelOffersPropertiesDefaults = $container->get(OffersPropertiesDefaults::class);

        /** @var OffersPropertiesValues $modelOffersPropertiesValues */
        $modelOffersPropertiesValues = $container->get(OffersPropertiesValues::class);

        /** @var Currencies $modelCurrencies */
        $modelCurrencies = $container->get(Currencies::class);

        /** @var Stock $modelStock */
        $modelStock = $container->get(Stock::class);

        $form = new Offer();

        /** @var Items $model */
        $model = $container->get(Offers::class);
        $form->setInputFilter($model->getInputFilter());


        // Генерация полей свойств торгового предложения
        $properties = $modelOffersProperties->fetchAll();
        foreach ($properties as $property) {
            if ($property->type == 'select') {
                $property->values = $modelOffersPropertiesDefaults->fetchByProperty($property->id, true);
            }
            if ($property->type == 'text') {
                $property->values = $modelOffersPropertiesValues->fetchUniqueValues($property->id);
            }
        }

        $form->generateProperties($properties);

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

        return $form;
    }
}