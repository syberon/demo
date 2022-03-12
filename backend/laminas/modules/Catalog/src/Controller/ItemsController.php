<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use ArrayObject;
use Exception;
use Laminas\Http\Response;
use Laminas\Json\Json;
use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\MtAbstractActionController;
use MtLib\SeoUrl\Service\SeoUrl;
use MtModules\Catalog\Form\Item;
use MtModules\Catalog\Form\Picture;
use MtModules\Catalog\Model\Categories;
use MtModules\Catalog\Model\Currencies;
use MtModules\Catalog\Model\Groups;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\ItemsCategoryLinker;
use MtModules\Catalog\Model\ItemsProperties;
use MtModules\Catalog\Model\ItemsPropertiesValues;
use MtModules\Catalog\Model\Offers;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesValues;
use MtModules\Catalog\Model\Pictures;
use MtModules\Catalog\Model\Price;
use MtModules\Catalog\Model\Related;
use MtModules\Catalog\Model\Stock;
use MtModules\Catalog\Model\Sync;

/**
 * Class ItemsController
 *
 * @package MtModules\Catalog\Controller
 */
class ItemsController extends MtAbstractActionController
{
    protected string $redirectRoute = 'catalog/items';

    /**
     * @param Item $form
     * @param Items $modelItems
     * @param ItemsProperties $modelItemsProperties
     * @param ItemsPropertiesValues $modelItemsPropertiesValues
     * @param Offers $modelOffers
     * @param OffersProperties $modelOffersProperties
     * @param OffersPropertiesValues $modelOffersPropertiesValues
     * @param Stock $modelStock
     * @param Currencies $modelCurrencies
     * @param SeoUrl $seoUrl
     * @param ItemsCategoryLinker $modelItemsCategoryLinker
     * @param Price $modelPrice
     * @param Pictures $modelPictures
     * @param Categories $modelCategories
     * @param Groups $modelGroups
     * @param Related $modelRelated
     * @param Sync $modelSync
     */
    public function __construct(
        protected Item $form,
        protected Items $modelItems,
        protected ItemsProperties $modelItemsProperties,
        protected ItemsPropertiesValues $modelItemsPropertiesValues,
        protected Offers $modelOffers,
        protected OffersProperties $modelOffersProperties,
        protected OffersPropertiesValues $modelOffersPropertiesValues,
        protected Stock $modelStock,
        protected Currencies $modelCurrencies,
        protected SeoUrl $seoUrl,
        protected ItemsCategoryLinker $modelItemsCategoryLinker,
        protected Price $modelPrice,
        protected Pictures $modelPictures,
        protected Categories $modelCategories,
        protected Groups $modelGroups,
        protected Related $modelRelated,
        protected Sync $modelSync
    )
    {
    }

    /**
     * Отображение списка товаров в панели администрирования
     */
    public function indexAction(): array
    {
        $itemsProperties = $this->generatePropertiesList();
        if ($this->config()['catalog']['options']['use-offers']) {
            $offersProperties = $this->generatePropertiesList('offers');
        }

        return [
            'itemsProperties'      => $itemsProperties,
            'offersProperties'     => $offersProperties ?? null,
            'itemsPropertiesJson'  => Json::encode($itemsProperties),
            'offersPropertiesJson' => isset($offersProperties) ? Json::encode($offersProperties) : null
        ];

    }

    /**
     * Генерация полей свойств наименований со значениями
     *
     * @param string|null $type
     * @return array
     */
    protected function generatePropertiesList(?string $type = 'items'): array
    {
        $modelProperties = null;
        $modelValues = null;
        switch ($type) {
            case 'items':
                $modelProperties = $this->modelItemsProperties;
                $modelValues = $this->modelItemsPropertiesValues;
                break;
            case 'offers':
                $modelProperties = $this->modelOffersProperties;
                $modelValues = $this->modelOffersPropertiesValues;
                break;
        }

        $properties = $modelProperties->fetchAll();
        foreach ($properties as $key => $property) {
            $values = $modelValues->fetchUniqueValues($property->id);
            if ($values) {
                switch ($property->type) {
                    case 'checkbox':
                        $property->values = [
                            ''   => '',
                            '1'  => 'Да',
                            '!1' => 'Нет'
                        ];
                        break;
                    default:
                        array_unshift($values, '');
                        $property->values = $values;
                }
            } else {
                unset($properties[$key]);
            }
        }
        return $properties;
    }

    /**
     * Удаление наименования
     */
    public function deleteAction(): JsonModel
    {
        $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
        $items = $post['id'];

        if (is_scalar($items)) {
            $this->modelItems->delete($items);
        } elseif (is_array($items)) {
            foreach ($items as $item_id) {
                $this->modelItems->delete($item_id);
            }
        }

        return new JsonModel([
            'status' => 1
        ]);
    }

    /**
     * Получение страницы списка товаров для администрирования
     */
    public function getlistAction(): JsonModel
    {
        $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
        $filterData = $post['filter'];
        $paramsData = $post['params'];

        // Выборка списка вложенных категорий для фильтрации
        $filterCategories = $this->modelCategories->getChildCategories($filterData['category'], true);
        $filterData['category'] = $filterCategories;
        $filterData['unfiltered'] = true;

        $this->modelItems->setItemsPerPage($paramsData['pageSize'] ?:
            $this->config()['catalog']['pagination']['items.admin']);
        $this->modelItems->setCurrentPage($filterData['page']);

        $items = $this->modelItems->getFilteredList(
            filterData: $filterData,
            orderData: $paramsData['order']
        );

        return new JsonModel([
            'items'      => $items->getCurrentItems(),
            'pagination' => [
                'current' => $items->getCurrentPageNumber(),
                'count'   => $items->count()
            ]
        ]);
    }

    /**
     * Получение страницы списка товаров для основного раздела
     */
    public function getitemsAction(): JsonModel
    {
        $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
        $filterData = $post['filter'];
        $paramsData = $post['params'];

        // Выборка списка вложенных категорий для фильтрации
        $filterCategories = $this->modelCategories->getChildCategories($filterData['category'], true);
        $filterData['category'] = $filterCategories;

        $this->modelItems->setItemsPerPage($paramsData['pageSize'] ?:
            $this->config()['catalog']['pagination']['items.admin']);
        $this->modelItems->setCurrentPage($filterData['page']);

        // Преобразование параметров фильтрации к требуемому для выборки виду
        $this->modelItems->transformFilterArray($filterData['properties']);

        // Выборка товаров только из заданной группы
        $group = $this->params()->fromRoute('group');
        $groupInfo = null;
        if ($group) {
            $filterData['properties']['items'][$group] = 1;
            $groupInfo = $this->modelGroups->findByCode($group);
        }

        // Выборка групп для исключения из списка вывода
        $excludedGroups = $this->modelGroups->fetchExcluded();
        if ($excludedGroups) {
            $filterData['exclude'] = $excludedGroups;
        }

        $items = $this->modelItems->getFilteredList(
            filterData: $filterData,
            orderData: $paramsData['order']
        );

        // Обработка наименования перед выводом
        foreach ($items as $item) {
            $this->prepareItem($item, $groupInfo);
        }

        return new JsonModel([
            'items'      => $items->getCurrentItems(),
            'pagination' => [
                'current' => $items->getCurrentPageNumber(),
                'count'   => $items->count()
            ]
        ]);
    }

    /**
     * Заполнение наименования полями
     *
     * @param ArrayObject $item
     * @param ArrayObject|null $group
     */
    protected function prepareItem(ArrayObject $item, ?ArrayObject $group = null)
    {
        // Выборка и обработка фотографий
        $picture = $this->modelPictures->fetchMain($item->id);
        if ($picture && $this->uploads()->has($picture->picture)) {
            $item->picture = $this->imgUrl($this->uploads()->get($picture->picture)->getPublicUrl(),
                'catalog-item-list');
        } else {
            $item->picture = null;
        }

        if ($group) {
            $item->link = $this->url()->fromRoute('catalogrouter', ['item' => $item->id, 'group' => $group->id]);
        } else {
            $item->link = $this->url()->fromRoute('catalogrouter', ['item' => $item->id]);
        }

        // Выборка свойств и их значений
        $properties = $this->modelItemsPropertiesValues->fetchValuesForItem($item->id);
        $item->properties = $properties;
    }

    /**
     * Просмотр выбранной категории каталога
     */
    public function listAction(): array
    {
        $category_id = $this->params()->fromRoute('category');
        $category = $this->modelCategories->find($category_id);
        $group_id = $this->params()->fromRoute('group');
        $this->generateBreadcrumbs($category->id, $group_id);

        $categories = $this->modelCategories->getChildCategories($category_id, true);
        $properties['items'] = $this->modelItemsPropertiesValues->fetchUniqueValuesForCategory($categories, $group_id);
        $properties['offers'] = $this->modelOffersPropertiesValues->fetchUniqueValuesForCategory($categories, $group_id);

        // Добавляем группу к фильтрации, если задана
        $group = $this->modelGroups->find($group_id);
        $subCategories = $this->getSubcategories($category_id, $group, true);

        $priceRange = $this->getMinMaxPrice($category_id, $group);

        return [
            'subCategories' => $subCategories,
            'category'      => $category,
            'priceRange'    => $priceRange,
            'group'         => $group,
            'properties'    => $properties
        ];
    }

    /**
     * Генерация хлебных крошек
     *
     * @param int $category_id
     * @param int|null $group_id
     * @param ArrayObject|null $item
     */
    protected function generateBreadcrumbs(int $category_id, ?int $group_id = null, ?ArrayObject $item = null)
    {
        $path = [];

        $this->modelCategories->getPath($category_id, $this->modelGroups->find($group_id), $path);

        // Добавление группы в хлебные крошки, если задана
        if ($group_id) {
            $group = $this->modelGroups->find($group_id);
            array_push($path, [
                'name'    => $group->name,
                'fullurl' => $this->url()->fromRoute('catalogrouter', ['group' => $group_id])
            ]);
        }

        $path = array_reverse($path);
        if ($item) {
            array_push($path, [
                'name'    => $item->name,
                'fullurl' => ''
            ]);
        }

        foreach ($path as $page) {
            $this->page()->addPage($page['name'], $page['fullurl'], false, true);
        }
    }

    /**
     * Получение списка подкатегорий с фильтрацией по наличию
     *
     * @param int $parent
     * @param ArrayObject|null $group
     * @param bool $filter
     * @return array
     */
    protected function getSubcategories(int $parent, ?ArrayObject $group = null, bool $filter = false): array
    {
        $categories = $this->modelCategories->fetchByParent($parent);
        // Фильтрация по наличию наименований в подкатегории
        if ($filter) {
            foreach ($categories as $key => $category) {
                if (!$this->hasItems($category->id, $group)) {
                    unset($categories[$key]);
                }
            }
        }
        return $categories;
    }

    /**
     * Получение списка наименований для выбранной категории
     *
     * @param int $category_id
     * @param ArrayObject|null $group
     * @return bool
     */
    protected function hasItems(int $category_id, ?ArrayObject $group = null): bool
    {
        // Выборка списка вложенных категорий для фильтрации
        $filterCategories = $this->modelCategories->getChildCategories($category_id, true);
        $filterData = [
            'category' => $filterCategories
        ];

        $excludedGroups = $this->modelGroups->fetchExcluded();
        if ($excludedGroups) {
            $filterData['exclude'] = $excludedGroups;
        }

        // Если задана группа товаров, то добавляем в фильтр наименований поле группы
        if ($group) {
            $filterData['properties']['items'][$group->code] = 1;
        }

        return (bool)count($this->modelItems->getFilteredList($filterData));
    }

    /**
     * Получение минимальной и максимальной цены в выбранной категории и группе
     *
     * @param int $category_id
     * @param null $group
     * @return ArrayObject|null
     */
    public function getMinMaxPrice(int $category_id, $group = null): ?ArrayObject
    {
        // Выборка списка вложенных категорий для фильтрации
        $filterCategories = $this->modelCategories->getChildCategories($category_id, true);
        $filterData = [
            'category' => $filterCategories
        ];

        $excludedGroups = $this->modelGroups->fetchExcluded();
        if ($excludedGroups) {
            $filterData['exclude'] = $excludedGroups;
        }

        // Если задана группа товаров, то добавляем в фильтр наименований поле группы
        if ($group) {
            $filterData['properties']['items'][$group->code] = 1;
        }

        return $this->modelItems->getPriceRange($filterData);
    }

    /**
     * Просмотр выбранного наименования
     *
     */
    public function viewAction(): array
    {
        $item_id = $this->params()->fromRoute('item');
        $category_id = $this->params()->fromRoute('category');
        $group_id = $this->params()->fromRoute('group');

        $item = $this->modelItems->find($item_id);
        $category = $this->modelCategories->find($category_id);

        if ($group_id) {
            $group = $this->modelGroups->find($group_id);
        } else {
            $group = null;
        }

        $this->prepareItem($item, $group);

        $item->info_properties = $this->modelItemsPropertiesValues->fetchInfoValuesForItem($item->id);

        $pictures = $this->modelPictures->fetchForItem($item_id);
        foreach ($pictures as $picture) {
            if ($this->uploads()->has($picture->picture)) {
                $file = $this->uploads()->get($picture->picture)->getPublicUrl();
                $picture->thumb = $this->imgUrl($file, 'catalog-item-additional');
                $picture->main = $this->imgUrl($file, 'catalog-item-main');
                $picture->big = $this->imgUrl($file, 'catalog-item-big');
            }
        }

        // Если используется привяка брендов к товарам
        if ($this->config()['catalog']['options']['use-brands']) {
            if (isset($item->properties['brand'])) {
                $brand = $this->getFeedItem()->byTitle($item->properties['brand'], 'brands');
                $item->brand = $brand;
            }
        }

        // Если используются торговые предложения
        if ($this->config()['catalog']['options']['use-offers']) {
            $item->offers = $this->modelOffers->fetchOffers($item->id);
            foreach ($item->offers as $offer) {
                // Заполнение торговых предложений свойствами и их значениями
                $properties_values = $this->modelOffersPropertiesValues->fetchValuesForOffer($offer->id);
                $properties = [];
                foreach ($properties_values as $code => $value) {
                    $property = $this->modelOffersProperties->getProperty($code);
                    if ($property->offerselect) {
                        $properties[] = [
                            'name'  => $property->name,
                            'code'  => $code,
                            'value' => $value
                        ];
                    }
                }

                // Заполнение торгового предложения ценами
                $offer->price = $this->modelPrice->getForOffer($offer->id);

                // Заполнение торгового предложения информацией о наличии
                if ($this->config()['catalog']['options']['use-stock']) {
                    $offer->stock = $this->modelStock->getForOffer($offer->id);
                }

                $offer->properties = $properties;
            }
        }

        // Если используется привязка товаров
        if ($this->config()['catalog']['options']['use-related']) {
            $related = Json::encode($this->getRelated($item->id));
        }

        $this->generateBreadcrumbs($category_id, $group_id, $item);

        return [
            'item'     => $item,
            'category' => $category,
            'group'    => $group,
            'related'  => $related ?? null,
            'pictures' => Json::encode($pictures),
            'offers'   => Json::encode($item->offers)
        ];
    }

    /**
     * Выборка списка рекомендуемых товаров
     *
     * @param int $item_id
     * @param bool $unfiltered
     * @return array
     */
    protected function getRelated(int $item_id, ?bool $unfiltered = false): array
    {
        $related = $this->modelRelated->getForItem($item_id, $unfiltered);

        foreach ($related as $item) {
            $item->link = $this->url()->fromRoute('catalogrouter', ['item' => $item->related]);
            $item->related_id = $item->id;

            // Выборка и обработка фотографий
            $picture = $this->modelPictures->fetchMain($item->related);
            if ($picture && $this->uploads()->has($picture->picture)) {
                $item->picture = $this->imgUrl($this->uploads()->get($picture->picture)->getPublicUrl(),
                    'catalog-item-list');
            } else {
                $item->picture = null;
            }
        }

        return $related;
    }

    /**
     * Автоматическая синхронизация каталога
     *
     * @throws Exception
     */
    public function autosyncAction(): array
    {
        $dumpResult = [
            'count' => 0,
            'total' => 0
        ];

        $fileProcessed = false;

        if (file_exists('public_html/sync/dump.xml')) {
            $xml = simplexml_load_file('public_html/sync/dump.xml');
            $dumpResult = $this->modelSync->sync($xml);
            if ($this->config()['catalog']['options']['sync']['rename-processed-file']) {
                @unlink('public_html/sync/dump.xml.processed');
                rename('public_html/sync/dump.xml', 'public_html/sync/dump.xml.processed');
            }
            $fileProcessed = true;
        }

        $fileResult = $this->modelSync->uploadPictures('public_html/sync/pictures');

        if ($fileProcessed) {
            $logFile = fopen('sync.log', 'w+');
            fwrite($logFile, serialize([
                'dump' => $dumpResult,
                'file' => $fileResult
            ]));
            fclose($logFile);
        }

        return [
            'dumpResult' => $dumpResult,
            'fileResult' => $fileResult
        ];
    }

    /**
     * Просмотр последнего лога сингхронизации
     */
    public function synclogAction(): array
    {
        $dump = file_get_contents('sync.log');
        $dump = unserialize($dump);

        return [
            'dumpResult' => $dump['dump'],
            'fileResult' => $dump['file']
        ];
    }

    /**
     * Поиск товаров в каталоге
     */
    public function searchAction(): JsonModel|array
    {
        if ($this->getRequest()->isPost()) {
            $query = $this->params()->fromQuery('query');

            $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
            $params = $post['params'];

            $this->modelItems->setItemsPerPage($params['pageSize']);
            $this->modelItems->setCurrentPage($params['page']);

            if ($query) {
                $items = $this->modelItems->search($query, $params['order']['value']);
                foreach ($items as $item) {
                    $this->prepareItem($item);
                }

                return new JsonModel([
                    'items'      => $items->getCurrentItems(),
                    'pagination' => [
                        'current' => $items->getCurrentPageNumber(),
                        'count'   => $items->count()
                    ],
                    'count'      => $items->getTotalItemCount()
                ]);
            }
        } else {
            return [];
        }
        return [];

    }

    /**
     * Добавление рекомендуемого товара
     */
    public function addRelatedAction(): JsonModel
    {
        $item_id = $this->params()->fromPost('item');
        $article = $this->params()->fromPost('article');

        $related = $this->modelItems->findByArticle($article);

        if ($related) {
            $this->modelRelated->insert([
                'item'    => $item_id,
                'related' => $related->id
            ]);

            $relatedList = $this->getRelated($item_id);

            return new JsonModel([
                'code'    => 1,
                'related' => $relatedList
            ]);
        } else {
            return new JsonModel(['code' => 0]);
        }
    }

    public function deleteRelatedAction(): JsonModel
    {
        $id = $this->params()->fromRoute('id');
        $related_record = $this->modelRelated->find($id);
        $this->modelRelated->delete($id);
        $relatedList = $this->getRelated($related_record->item);
        return new JsonModel([
            'related' => $relatedList,
            'code'    => 1
        ]);
    }

    /**
     * @throws Exception
     */
    public function addAction(): Response|array
    {
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $post = array_merge_recursive($post, $this->getRequest()->getFiles()->toArray());

            $this->form->setData($post);

            if ($this->form->isValid()) {
                $data = $this->form->getData();

                // Обработка блока основных параметрова товара
                $main = $data['main'];
                $main['code'] = $main['code'] ?: $this->seoUrl->create($main['name']);

                // Запоминаем выбранные категории
                $categories = explode(",", $main['categories']);
                unset($main['categories']);


                // Обработка главной фотографии, если была задана
                $uploadPictures = $main['picture'][0]['tmp_name'];
                unset($main['picture']);

                $item_id = $this->modelItems->insert($main);

                if ($uploadPictures) {
                    $uploadFile = $this->params()->fromFiles('main')['picture'];
                    foreach ($uploadFile as $item) {
                        if ($item['name']) {
                            $uploadId = $this->uploads()->upload($item);
                            $pictureData = [
                                'item'    => $item_id,
                                'picture' => $uploadId
                            ];
                            $this->modelPictures->insert($pictureData);
                        }
                    }
                }

                // Выполняем привязку добавленного товара к выбранным категориям
                foreach ($categories as $category) {
                    $this->modelItemsCategoryLinker->linkItem($item_id, $category);
                }

                $properties = $data['properties'];
                foreach ($properties as $property_code => $property_value) {
                    if ($property_value) {
                        $property = $this->modelItemsProperties->getProperty($property_code);
                        if (is_array($property_value)) {
                            foreach ($property_value as $value) {
                                $this->modelItemsPropertiesValues->insertValue($item_id, $property->id, $value);
                            }
                        } else {
                            $this->modelItemsPropertiesValues->insertValue($item_id, $property->id, $property_value);
                        }
                    }
                }

                if (!$this->config()['catalog']['options']['use-offers']) {
                    // Обработка цены
                    $price = $data['price'];
                    if (!$price['price']) {
                        $price['price'] = 0;
                    }
                    if (!$price['price_sale']) {
                        $price['price_sale'] = null;
                    }
                    $price['item'] = $item_id;
                    $this->modelPrice->insert($price);

                    // Обработка наличия
                    if ($this->config()['catalog']['options']['use-stock']) {
                        $stock = $data['stock'];
                        if (!$stock['count']) {
                            $stock['count'] = 0;
                        }
                        if (!$stock['unit']) {
                            unset($stock['unit']);
                        }
                        $stock['item'] = $item_id;
                        $this->modelStock->insert($stock);
                    }
                }

                $this->fm('Наименование успешно добавлено');
                if ($this->config()['catalog']['options']['use-offers']) {
                    return $this->redirect()->toRoute('catalog/items/edit', ['id' => $item_id],
                        ['fragment' => 'offers']);
                } else {
                    return $this->redirect()->toRoute($this->redirectRoute);
                }

            }
        }

        return [
            'form' => $this->form
        ];
    }

    /**
     * Редактирование наименования
     *
     * @throws Exception
     */
    public function editAction(): Response|array
    {
        $item_id = $this->params()->fromRoute('id');
        $item = $this->modelItems->find($item_id);
        $this->form->prepareEdit();

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $this->form->setData($post);

            $data = $post;

            if ($this->form->isValid()) {
                $data = $this->form->getData();

                // Обработка блока основных параметрова товара
                $main = $data['main'];
                $main['code'] = $main['code'] ?: $this->seoUrl->create($main['name']);

                // Запоминаем выбранные категории
                $categories = explode(",", $main['categories']);
                unset($main['categories']);

                $this->modelItems->update($main, $item_id);

                // Выполняем привязку добавленного товара к выбранным категориям
                $this->modelItemsCategoryLinker->clearLinks($item_id);
                foreach ($categories as $category) {
                    $this->modelItemsCategoryLinker->linkItem($item_id, $category);
                }

                // Обработка значений свойств
                $properties = $data['properties'];
                $this->modelItemsPropertiesValues->clearValues($item_id);
                foreach ($properties as $property_code => $property_value) {
                    if ($property_value) {
                        $property = $this->modelItemsProperties->getProperty($property_code);
                        if (is_array($property_value)) {
                            foreach ($property_value as $value) {
                                $this->modelItemsPropertiesValues->insertValue($item_id, $property->id, $value);
                            }
                        } else {
                            $this->modelItemsPropertiesValues->insertValue($item_id, $property->id, $property_value);
                        }
                    }
                }

                if (!$this->config()['catalog']['options']['use-offers']) {
                    // Обработка цены
                    $itemPrice = $this->modelPrice->getForItem($item_id);
                    $itemPrice->price = $data['price']['price'] ?: 0;
                    $itemPrice->price_sale = $data['price']['price_sale'] ?: null;
                    $this->modelPrice->update($itemPrice->getArrayCopy(), $itemPrice->id);

                    // Обработка наличия
                    if ($this->config()['catalog']['options']['use-stock']) {
                        $itemStock = $this->modelStock->getForItem($item_id);
                        $itemStock->count = $data['stock']['count'] ?: 0;
                        // Если заданы единицы измерения
                        if (isset($data['stock']['unit'])) {
                            $itemStock->unit = $data['stock']['unit'];
                        }
                        $this->modelStock->update($itemStock->getArrayCopy(), $itemStock->id);
                    }
                }

                $this->fm('Информация о товаре успешно обновлена');
                return $this->redirect()->toRoute($this->redirectRoute);
            }
        } else {
            $data = [
                'main'       => $item->getArrayCopy(),
                'properties' => $this->modelItemsPropertiesValues->fetchValuesForItem($item_id)
            ];

            // Выборка цены и информации о наличии, если не используются торговые предложения
            if (!$this->config()['catalog']['options']['use-offers']) {
                // Обработка цены
                $data['price'] = $this->modelPrice->getForItem($item_id);

                // Обработка наличия
                if ($this->config()['catalog']['options']['use-stock']) {
                    $data['stock'] = $this->modelStock->getForItem($item_id);
                }
            }

            $this->form->setData($data);

            // Выборка привязанных категорий
            $data['main']['categories'] = $this->modelItemsCategoryLinker->getLinkedCategories($item_id);
        }

        // Формирование списка торговых предложений
        $offers = $this->getOffers($item_id);

        // Выборка и обработка прикрепленных изображений
        $pictures = $this->modelPictures->fetchForItem($item_id);
        $data['pictures'] = [];
        foreach ($pictures as $picture) {
            if ($this->uploads()->has($picture->picture)) {
                $picture->processed = $this->imgUrl($this->uploads()->get($picture->picture)->getPublicUrl(),
                    'catalog-items-edit');
                $data['pictures'][] = $picture;
            } else {
                $this->modelPictures->delete($picture->id);
            }
        }

        // Выборка рекомендуемых товаров
        if ($this->config()['catalog']['options']['use-related']) {
            $data['related'] = $this->getRelated($item_id, true);
        }

        $formItemsPictures = new Picture();
        $formItemsPictures->setAttribute('action', $this->url()->fromRoute('catalog/pictures/add',
            ['item' => $item_id]));

        return [
            'data'             => Json::encode($data, true),
            'offers'           => $offers,
            'form'             => $this->form,
            'formItemsPicture' => $formItemsPictures
        ];
    }

    /**
     * Формирование списка торговых предложений со свойствами
     *
     * @param int $item_id
     * @return array|null
     */
    protected function getOffers(int $item_id): ?array
    {
        $item = $this->modelItems->find($item_id);
        $offers = $this->modelOffers->fetchOffers($item_id);
        foreach ($offers as $offer) {
            $properties = [];
            $properties_values = $this->modelOffersPropertiesValues->fetchValuesForOffer($offer->id);
            foreach ($properties_values as $code => $value) {
                $property = $this->modelOffersProperties->getProperty($code);
                $properties[] = $property->name . ': ' . $value;
            }

            $offer->name = sprintf("%s (%s)", $item->name, implode('; ', $properties));
            $price = $this->modelPrice->getForOffer($offer->id);
            $offer->price = $price->price;
            $offer->price_sale = $price->price_sale;
            $stock = $this->modelStock->getForOffer($offer->id);
            $offer->count = $stock->count;
        }

        return $offers;
    }

    protected function syncUpdateItem()
    {

    }

    protected function syncInsertOffersProperties()
    {

    }
}
