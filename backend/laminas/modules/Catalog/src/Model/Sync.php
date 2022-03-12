<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;


use Exception;
use MtLib\SeoUrl\Service\SeoUrl;
use MtLib\Upload\Controller\Plugin\Uploads;
use MtLib\Upload\File\File;
use SimpleXMLElement;

/**
 * Модель работы с синхронизацие товаров с базой 1С
 *
 */
class Sync
{

    protected array $syncLog = [];

    protected array $fileLog = [];

    /**
     * Cart constructor.
     * @param Items $modelItems
     * @param Offers $modelOffers
     * @param ItemsCategoryLinker $modelItemsCategoryLinker
     * @param ItemsPropertiesValues $modelItemsPropertiesValues
     * @param Price $modelPrice
     * @param Stock $modelStock
     * @param OffersPropertiesValues $modelOffersPropertiesValues
     * @param Categories $modelCategories
     * @param ItemsProperties $modelItemsProperties
     * @param OffersProperties $modelOffersProperties
     * @param Pictures $modelPictures
     * @param SeoUrl $seoUrl
     * @param array $config
     * @param Uploads $uploadsPlugin
     */
    public function __construct(
        protected Items $modelItems,
        protected Offers $modelOffers,
        protected ItemsCategoryLinker $modelItemsCategoryLinker,
        protected ItemsPropertiesValues $modelItemsPropertiesValues,
        protected Price $modelPrice,
        protected Stock $modelStock,
        protected OffersPropertiesValues $modelOffersPropertiesValues,
        protected Categories $modelCategories,
        protected ItemsProperties $modelItemsProperties,
        protected OffersProperties $modelOffersProperties,
        protected Pictures $modelPictures,
        protected SeoUrl $seoUrl,
        protected array $config,
        protected Uploads $uploadsPlugin
    )
    {
    }

    /**
     * Загрузка фотографий из папки в базу
     *
     * @param string $dir
     * @return array
     */
    public function uploadPictures(string $dir): array
    {
        // Сканирование папки на наличие файлов изображений
        $files = [];
        $filesUsed = [];
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while ($file = readdir($handle)) {
                    if ($file != '.' && $file != '..') {
                        $files[] = $file;
                    }
                }
                closedir($handle);
            }
        }

        sort($files);

        if (count($files)) {
            $usedArticles = [];
            foreach ($files as $file) {
                preg_match('/(.*) (\([0-9]+\)).(jpg|JPG)+$/ui', $file, $matches);
                $article = null;
                if (isset($matches[1])) {
                    $article = trim($matches[1]);
                }
                if ($article && strtolower($matches[3]) == 'jpg') {
                    $item = $this->modelItems->findByArticle($article);
                    if ($item) {
                        $filesUsed[] = $file;
                        if (!in_array($article, $usedArticles)) {
                            // Удаление всех ранее привязанных фотографий
                            $this->modelPictures->deletePicturesForItem($item->id);
                            $usedArticles[] = $article;
                        }
                        $pictureData = [
                            'picture' => $this->uploadsPlugin->upload(new File($file, $dir . '/' . $file)),
                            'item'    => $item->id
                        ];
                        $this->modelPictures->insert($pictureData);
                        unlink($dir . '/' . $file);
                    } else {
                        $this->logFile($file, 'Не существующий артикул товара');
                    }
                } else {
                    $this->logFile($file, 'Не соответствует формату');
                }
            }
        }

        return [
            'count' => count($filesUsed),
            'total' => count($files),
            'log'   => $this->fileLog
        ];

    }

    /**
     * Запись лога для файла
     *
     * @param string $file
     * @param string $reason
     */
    protected function logFile(string $file, string $reason)
    {
        $this->fileLog[] = [
            'file'   => $file,
            'reason' => $reason
        ];
    }

    /**
     * Синхронизация каталога товаров
     *
     * @param SimpleXMLElement $dump
     * @return array
     * @throws Exception
     */
    public function sync(SimpleXMLElement $dump): array
    {
        // Массив всех элементов выгрузки
        $dumpItems = [];
        $totalItems = 0;

        if ($dump->count()) {
            // Обработка структуры категорий
            if ($this->config['catalog']['options']['sync']['parse-categories'] && isset($dump->categories->category) && $dump->categories->category->count()) {
                $this->processCategories($dump->categories->category);
            }

            // Обработка новых свойств наименований
            if (isset($dump->properties->item->property) && $dump->properties->item->property->count()) {
                $this->processItemsProperties($dump->properties->item);
            }

            // Обработка новых свойств торговых предложений
            if (isset($dump->properties->offer->property) && $dump->properties->offer->property->count()) {
                $this->processOffersProperties($dump->properties->offer);
            }

            if (isset($dump->items->item) && $dump->items->item->count()) {

                foreach ($dump->items->item as $item) {
                    $processError = false;
                    $totalItems++;
                    $dumpItems[] = (string)$item->attributes()->guid;

                    // Проверка на существование элемента в катлоге
                    $exist_item_id = $this->modelItems->getIdByGuid((string)$item->attributes()->guid);

                    // Добавляем/обновляем элемент каталога
                    $item_id = $this->updateItem($item, $exist_item_id);

                    // Привязка нового наименования к категориям
                    if (isset($item->categories->category) && $item->categories->category->count()) {
                        if (!$this->linkCategories($item_id, $item->categories)) {
                            $processError = $this->logError($item, 'Отсутствует привязка к категории каталога');
                        }
                    } else {
                        $processError = $this->logError($item, 'Отсутствует привязка к категории каталога');
                    }

                    if (isset($item->properties->property) && $item->properties->property->count()) {
                        if (!$this->insertItemsProperties($item_id, $item->properties)) {
                            $processError = $this->logError($item, 'Для наименования не задано ни одного свойства');
                        }
                    } else {
                        $processError = $this->logError($item, 'Для наименования не задано ни одного свойства');
                    }


                    if ($this->config['catalog']['options']['use-offers']) {
                        if (isset($item->offers->offer) && $item->offers->offer->count()) {
                            if (!$this->processOffers($item_id, $item, $item->offers)) {
                                $processError = $this->logError($item,
                                    'Для наименования не задано ни одного торгового предложения');
                            }
                        } else {
                            $processError = $this->logError($item,
                                'Для наименования не задано ни одного торгового предложения');
                        }
                    }

                    if ($processError) {
                        $this->modelItems->delete($item_id);
                        array_pop($dumpItems);
                    }
                }
            }
        }

        // Действия над несуществующими в выгрузке товарами
        foreach ($this->modelItems->getGuids() as $id => $guid) {
            if (!in_array($guid, $dumpItems)) {
                switch ($this->config['catalog']['options']['sync']['not-exist-action']) {
                    case 'delete':
                        $this->modelItems->delete(['guid' => $guid]);
                        break;
                    case 'deactivate':
                        $this->modelItems->update(['active' => ''], $id);
                        break;
                    case 'keep':
                        break;
                }
            }
        }


        return [
            'log'   => $this->syncLog,
            'count' => count($dumpItems),
            'total' => $totalItems
        ];
    }

    /**
     * Обработка структуры категорий
     *
     * @param SimpleXMLElement $categories
     * @param int $parent
     * @throws Exception
     */
    protected function processCategories(SimpleXMLElement $categories, int $parent = 1)
    {
        /** @var SimpleXMLElement $category */
        foreach ($categories as $category) {
            $existCategory = $this->modelCategories->getByGuid($category->attributes()->guid);
            if ($existCategory) {
                $categoryData = [
                    'name' => (string)$category->name,
                    'url'  => $this->seoUrl->create($category->name),
                ];
                $category_id = $existCategory->id;
                $this->modelCategories->update($categoryData, $category_id);
            } else {
                $categoryData = [
                    'name'   => (string)$category->name,
                    'url'    => $this->seoUrl->create($category->name),
                    'guid'   => (string)$category->attributes()->guid,
                    'parent' => $parent
                ];
                $category_id = $this->modelCategories->insert($categoryData);
            }

            if (isset($category->childs) && $category->childs->category->count()) {
                $this->processCategories($category->childs->category, $category_id);
            }
        }
    }

    /**
     * Обработка свойств наименований из выгрузки
     *
     * @param SimpleXMLElement $properties
     * @throws Exception
     */
    protected function processItemsProperties(SimpleXMLElement $properties)
    {
        foreach ($properties->property as $property) {
            $existProperty = $this->modelItemsProperties->getByGuid((string)$property->attributes()->guid);
            if ($existProperty) {
                $propertyData = [
                    'name' => (string)$property->name,
                    'type' => $property->type == 'Булево' ? 'checkbox' : 'text'
                ];
                $this->modelItemsProperties->update($propertyData, $existProperty->id);
            } else {
                $propertyData = [
                    'name' => (string)$property->name,
                    'code' => $this->generatePropertyCode($property->name),
                    'type' => $property->type == 'Булево' ? 'checkbox' : 'text',
                    'guid' => (string)$property->attributes()->guid
                ];
                $this->modelItemsProperties->insert($propertyData);
            }
        }
    }

    /**
     * Генерация уникального символьного имени свойства
     *
     * @param string $name
     * @param string $type
     * @return string
     * @throws Exception
     */
    protected function generatePropertyCode(string $name, string $type = 'item'): string
    {
        switch ($type) {
            case 'item':
            {
                $model = $this->modelItemsProperties;
                break;
            }
            default:
                $model = $this->modelOffersProperties;
        }

        $counter = 0;
        do {
            $code = $this->seoUrl->create($name);
            if ($counter) {
                $code .= '_' . $counter;
            }
            $counter++;
        } while ($model->getProperty($code));
        return $code;
    }

    /**
     * Обработка свойств торговых предложений из выгрузки
     *
     * @param SimpleXMLElement $properties
     * @throws Exception
     */
    protected function processOffersProperties(SimpleXMLElement $properties)
    {
        foreach ($properties->property as $property) {
            $existProperty = $this->modelOffersProperties->getByGuid((string)$property->attributes()->guid);
            if ($existProperty) {
                $propertyData = [
                    'name' => (string)$property->name,
                    'code' => $this->seoUrl->create($property->name),
                    'type' => $property->type == 'Булево' ? 'checkbox' : 'text'
                ];
                $this->modelOffersProperties->update($propertyData, $existProperty->id);
            } else {
                $propertyData = [
                    'name' => (string)$property->name,
                    'code' => $this->generatePropertyCode($property->name, 'offer'),
                    'type' => $property->type == 'Булево' ? 'checkbox' : 'text',
                    'guid' => (string)$property->attributes()->guid
                ];
                $this->modelOffersProperties->insert($propertyData);
            }
        }
    }

    /**
     * Обновление/добавление элемента в каталог
     *
     * @param SimpleXMLElement $item
     * @param int|null $item_id
     * @return int
     * @throws Exception
     */
    protected function updateItem(SimpleXMLElement $item, ?int $item_id = null): int
    {
        $itemData = [
            'name'        => trim((string)$item->name),
            'preview'     => (string)$item->preview,
            'description' => (string)$item->description,
            'article'     => trim((string)$item->article),
            'guid'        => (string)$item->attributes()->guid
        ];

        if ($item_id) {
            $this->modelItems->update($itemData, $item_id);
            return $item_id;
        } else {
            $itemData['code'] = $this->seoUrl->create($itemData['name']);
            return $this->modelItems->insert($itemData);
        }
    }

    /**
     * Привязка наименования к категориям каталога
     *
     * @param int $item_id
     * @param SimpleXMLElement $categories
     * @return bool
     */
    protected function linkCategories(int $item_id, SimpleXMLElement $categories): bool
    {
        $categoryAssigned = false;

        // Удаляем все существующие привязки к категориям
        $this->modelItemsCategoryLinker->clearLinks($item_id);

        foreach ($categories->category as $category) {
            $existCategory = $this->modelCategories->getByGuid((string)$category->attributes()->guid);
            if ($existCategory) {
                $categoryAssigned = true;
                $this->modelItemsCategoryLinker->linkItem($item_id, $existCategory->id);
            }
        }

        return $categoryAssigned;
    }

    /**
     * Запись лога для наименования
     *
     * @param SimpleXMLElement $item
     * @param string $reason
     * @return bool
     */
    protected function logError(SimpleXMLElement $item, string $reason): bool
    {
        $this->syncLog[] = [
            'guid'    => (string)$item->attributes()->guid,
            'article' => (string)$item->article,
            'name'    => (string)$item->name,
            'reason'  => $reason
        ];
        return true;
    }

    /**
     * Добавление значений свойств для наименования
     *
     * @param int $item_id
     * @param SimpleXMLElement $properties
     * @return bool
     */
    protected function insertItemsProperties(int $item_id, SimpleXMLElement $properties): bool
    {
        $propertiesAssigned = false;
        $this->modelItemsPropertiesValues->clearSyncValues($item_id);
        foreach ($properties->property as $property) {
            $itemProperty = $this->modelItemsProperties->getByGuid((string)$property->attributes()->guid);
            if ($itemProperty) {
                if (!empty($property->value)) {
                    $propertiesAssigned = true;
                    $this->modelItemsPropertiesValues->insertValue($item_id, $itemProperty->id, $property->value);
                }
                if ($this->config['catalog']['options']['sync']['allow-empty-items-properties']) {
                    $propertiesAssigned = true;
                }
            }
        }
        return $propertiesAssigned;
    }

    /**
     * Обработка торговых предложение
     *
     * @param int $item_id
     * @param SimpleXMLElement $item
     * @param SimpleXMLElement $offers
     * @return bool
     */
    protected function processOffers(int $item_id, SimpleXMLElement $item, SimpleXMLElement $offers): bool
    {

        // Массив тороговых предложений из выгрузки
        $dumpOffers = [];
        foreach ($offers->offer as $offer) {
            $exist_offer_id = $this->modelOffers->getIdByGuid($offer->attributes()->guid);

            // Обработка цены торгового предложения
            if (isset($offer->prices->price) && $offer->prices->price->count()) {
                $dumpOffers[] = $offer->attributes()->guid;

                // Добавляем/обновляем элемент каталога
                $offer_id = $this->updateOffer($offer, $item_id, $exist_offer_id);

                $priceData = [
                    'item'  => $item_id,
                    'offer' => $offer_id
                ];
                foreach ($offer->prices->price as $price) {
                    switch ($price->attributes()->type) {
                        case 'sale':
                            $price_sale = (int)preg_replace('/[^0-9]/', '', (string)$price);
                            $priceData['price_sale'] = $price_sale ?: null;
                            break;
                        case 'base':
                        default:
                            $price_base = (int)preg_replace('/[^0-9]/', '', (string)$price);
                            $priceData['price'] = $price_base ?: null;
                    }
                }
                $existPrice = $this->modelPrice->getForOffer($offer_id);
                if ($existPrice) {
                    $this->modelPrice->update($priceData, $existPrice->id);
                } else {
                    $this->modelPrice->insert($priceData);
                }

                // Обработка остатков
                if ($this->config['catalog']['options']['use-stock']) {
                    if (isset($offer->stock->count)) {
                        $existStock = $this->modelStock->getForOffer($offer_id);
                        if ($existStock) {
                            $existStock->count = preg_replace('/[^0-9]/', '', $offer->stock->count);
                            $existStock->unit = $offer->stock->unit;
                            $this->modelStock->update($existStock->getArrayCopy(), $existStock->id);
                        } else {
                            $stockData = [
                                'item'  => $item_id,
                                'offer' => $offer_id,
                                'count' => preg_replace('/[^0-9]/', '', $offer->stock->count),
                                'unit'  => $offer->stock->unit,
                            ];
                            $this->modelStock->insert($stockData);
                        }
                    } else {
                        $this->logError($item, 'Для торгового предложения не заданы остатки');
                        $this->modelOffers->delete($offer_id);
                        continue;
                    }
                }

                if (isset($offer->properties->property) && count($offer->properties->property)) {
                    if (!$this->insertOffersProperties($offer_id, $item_id, $offer->properties)) {
                        if ($this->config['catalog']['options']['sync']['delete-empty-offers']) {
                            $this->logError($item, 'Для торгового предложения не заданы свойства');
                            $this->modelOffers->delete($offer_id);
                        }
                    }
                } else {
                    if ($this->config['catalog']['options']['sync']['delete-empty-offers']) {
                        $this->logError($item, 'Для торгового предложения не заданы свойства');
                        $this->modelOffers->delete($offer_id);
                    }
                }
            } else {
                $this->logError($item, 'Для торгового предложения не задана цена');
            }
        }

        // Удалить все существующие в базе торговые предложения, которых не было в выгрузке
        $itemOffers = $this->modelOffers->fetchOffers($item_id);

        if (count($itemOffers)) {
            foreach ($itemOffers as $itemOffer) {
                if (!in_array($itemOffer->guid, $dumpOffers)) {
                    $this->modelOffers->delete($itemOffer->id);
                }
            }
        }

        if (count($dumpOffers)) {
            return true;
        }
        return false;
    }

    /**
     * Обновление/добавление торгового предложения
     *
     * @param SimpleXMLElement $offer
     * @param int $item_id
     * @param int|null $offer_id
     * @return int
     */
    protected function updateOffer(SimpleXMLElement $offer, int $item_id, ?int $offer_id = null): int
    {

        $offerData = [
            'code' => $offer->code,
            'guid' => $offer->attributes()->guid,
            'item' => $item_id
        ];
        if ($offer_id) {
            $this->modelOffers->update($offerData, $offer_id);
            return $offer_id;
        } else {
            return $this->modelOffers->insert($offerData);
        }
    }

    /**
     * Обработка свойств торгового предложения
     *
     * @param int $offer_id
     * @param int $item_id
     * @param SimpleXMLElement $properties
     * @return bool
     */
    protected function insertOffersProperties(int $offer_id, int $item_id, SimpleXMLElement $properties): bool
    {
        $propertiesAssigned = false;
        $this->modelOffersPropertiesValues->clearSyncValues($offer_id);
        foreach ($properties->property as $property) {
            $offerProperty = $this->modelOffersProperties->getByGuid((string)$property->attributes()->guid);
            if ($offerProperty) {
                $propertiesAssigned = true;
                $this->modelOffersPropertiesValues->insertValue($item_id, $offer_id, $offerProperty->id,
                    $property->value);
            }
        }
        return $propertiesAssigned;
    }

}