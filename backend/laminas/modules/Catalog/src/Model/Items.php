<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use ArrayObject;
use JetBrains\PhpStorm\Pure;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Paginator\Adapter\LaminasDb\DbSelect;
use Laminas\Paginator\Paginator;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use MtLib\Base\Model\AbstractModel;

/**
 * Модель работы с базой товаров
 *
 */
class Items extends AbstractModel
{

    /**
     * Использовать постраничный вывод
     * @var bool
     */
    protected bool $usePagination = true;

    /**
     * Items constructor.
     * @param TableGatewayInterface $tableGateway
     * @param array $config
     */
    #[Pure]
    public function __construct(
        TableGatewayInterface $tableGateway,
        protected array $config
    )
    {
        parent::__construct($tableGateway);
    }

    /**
     * Получение списка товаров отфильтрованного по заданным критериям
     *
     * @param array $filterData
     * @param array|null $orderData
     * @param int|null $limit
     * @return array|Paginator
     */
    public function getFilteredList(array $filterData, ?array $orderData = null, int $limit = null): array|Paginator
    {
        $select = new Select();
        $where = new Where();

        $select->group('id');
        $select->from('catalog_items')
            ->columns(['*']);

        if ($limit) {
            $select->limit($limit);
        }

        // Фильтрация товара по выбранным категориям
        if (isset($filterData['category']) && $filterData['category']) {
            $select->join('catalog_items_category', 'catalog_items.id = catalog_items_category.item', []);
            if (is_scalar($filterData['category'])) {
                $where->equalTo('catalog_items_category.category', $filterData['category']);
            } else {
                $where->in('catalog_items_category.category', $filterData['category']);
            }
        }

        // Обработка фильтрации по основным полям наименования
        if (isset($filterData['fields'])) {
            foreach ($filterData['fields'] as $field => $value) {
                if ($value) {
                    /** @noinspection PhpExpressionResultUnusedInspection */
                    $where->and;
                    $where->like($field, '%' . $value . '%');
                }
            }
        }

        // Генерация правил исключения товаров указанных групп из выборки
        if (isset($filterData['exclude'])) {
            $this->generateExcludeProperties($filterData);
        }

        // Генерация правил исключения отдельных товаров выборки
        if (isset($filterData['exclude_items'])) {
            if (is_array($filterData['exclude_items'])) {
                foreach ($filterData['exclude_items'] as $item_id) {
                    $where->notEqualTo('catalog_items.id', $item_id);
                }
            }
        }

        // Генерация фильтрации на основе свойств товаров и торговых предложений
        $this->generateProperties($where, $filterData);
        if ($this->config['catalog']['options']['use-offers']) {
            $this->generateProperties($where, $filterData, 'offers');
        }

        // Если не отключена пользовательская фильтрация
        if (!isset($filterData['unfiltered']) || !$filterData['unfiltered']) {
            // Выборка только активных товаров
            $where->equalTo('active', 1);

            // Если используется информация о наличии товара на складе
            if ($this->config['catalog']['options']['use-stock'] &&
                !$this->config['catalog']['options']['display-out-of-stock']) {
                $select->join('catalog_stock', 'catalog_items.id = catalog_stock.item', []);
                $where->greaterThan('catalog_stock.count', 0);
            }

            // Добавление в выборку поля с минимальной ценой наименования или торгового предложения
            $select->join('catalog_price', 'catalog_items.id = catalog_price.item', [
                'price'          => new Expression(
                    'CASE
                     WHEN MIN(catalog_price.price_sale) > 0
                        THEN LEAST(MIN(catalog_price.price), MIN(catalog_price.price_sale))
                     ELSE MIN(catalog_price.price) END'),
                'price_base'     => new Expression('MIN(catalog_price.price)'),
                'price_sale'     => new Expression('MIN(catalog_price.price_sale)'),
                'price_discount' => new Expression('MIN(catalog_price.price) - MIN(catalog_price.price_sale)')
            ]);
        }

        if (isset($filterData['price'])) {
            $select->having->greaterThanOrEqualTo('price', $filterData['price'][0]);
            /** @noinspection PhpExpressionResultUnusedInspection */
            $select->having->and;
            $select->having->lessThanOrEqualTo('price', $filterData['price'][1]);
        }

        if ($orderData && $orderData['field']) {
            $select->order(new Expression($orderData['field'] . ' ' . $orderData['direction']));
        }

        $select->where($where);

        if ($this->usePagination) {
            $paginatorAdapter = new DbSelect($select, $this->table->getAdapter(),
                $this->table->getResultSetPrototype());
            $paginator = new Paginator($paginatorAdapter);
            $paginator->setDefaultItemCountPerPage($this->itemPerPage);
            $paginator->setCurrentPageNumber($this->currentPage);
            return $paginator;
        } else {
            $resultSet = $this->table->selectWith($select);
            return ArrayUtils::iteratorToArray($resultSet, false);
        }
    }

    /**
     * Генерация правил исключения товаров, попадающих под соответствующие группы
     *
     * @param array $filterData
     */
    protected function generateExcludeProperties(array &$filterData)
    {
        foreach ($filterData['exclude'] as $property) {
            if (!isset($filterData['properties']['items'][$property])) {
                $filterData['properties']['items'][$property] = '!1';
            }
        }
    }

    /**
     * Генерация условий фильтрации на основе значенией свойств
     *
     * @param Where $where
     * @param array $filterData
     * @param string|null $type
     */
    protected function generateProperties(Where $where, array $filterData, ?string $type = 'items')
    {
        if (isset($filterData['properties'][$type])) {
            foreach ($filterData['properties'][$type] as $code => $value) {
                if ($value && $value !== '') {
                    $where = $where->and;
                    $where = $where->nest();

                    if (is_scalar($value)) {
                        // Если указано всего лишь одно значение для фильтрации
                        $expression = 'EXISTS?';
                        if (str_starts_with($value, '!')) {
                            $expression = 'NOT EXISTS?';
                            $value = substr($value, 1);
                        }
                        $where->expression($expression, [$this->createPropertySubquery($type, $code, $value)]);
                    } else {
                        // Если задан массив значенией свойства для фильтрации
                        foreach ($value as $current) {
                            $expression = 'EXISTS?';
                            if (str_starts_with($current, '!')) {
                                $expression = 'NOT EXISTS?';
                                $current = substr($current, 1);
                            }

                            $where->expression($expression, [$this->createPropertySubquery($type, $code, $current)]);
                            /** @noinspection PhpExpressionResultUnusedInspection */
                            $where->or;
                        }
                    }
                    $where = $where->unnest();
                }
            }
        }
    }

    /**
     * Генерация подзапроса для фильтрации по значению свойства
     *
     * @param string $type
     * @param string $code
     * @param string $value
     * @return Select
     */
    protected function createPropertySubquery(string $type, string $code, string $value): Select
    {
        $subQuery = new Select();
        $subQuery->from('catalog_' . $type . '_properties_values');
        $subQuery->join('catalog_' . $type . '_properties', 'catalog_' . $type . '_properties.id = catalog_' . $type .
            '_properties_values.property');
        $subQuery->where->equalTo('catalog_' . $type . '_properties.code', $code);
        $subQuery->where->expression('catalog_' . $type . '_properties_values.item = catalog_items.id', []);
        $subQuery->where->equalTo('catalog_' . $type . '_properties_values.value', $value);
        return $subQuery;
    }

    /**
     * Поиск по каталогу товаров
     *
     * @param string $query
     * @param string $order
     * @param int|null $count
     * @return array|Paginator
     */
    public function search(string $query, string $order, int $count = null): array|Paginator
    {
        $query = $this->prepareQuery($query);

        $select = new Select();

        $select->from('catalog_items')
            ->columns(['*', 'rel' => new Expression("MATCH (catalog_items.name, catalog_items.description,
                catalog_items.preview, catalog_items.article) AGAINST(? IN BOOLEAN MODE)", [$query])]);

        $select->group('id');

        // Если используется информация о наличии товара на складе
        if ($this->config['catalog']['options']['use-stock'] &&
            !$this->config['catalog']['options']['display-out-of-stock']) {
            $select->join('catalog_stock', 'catalog_items.id = catalog_stock.item', []);
            $select->where->greaterThan('catalog_stock.count', 0);
        }

        // Добавление в выборку поля с минимальной ценой наименования или торгового предложения
        $select->join('catalog_price', 'catalog_items.id = catalog_price.item', [
            'price'          => new Expression(
                'CASE
                     WHEN MIN(catalog_price.price_sale) > 0
                        THEN LEAST(MIN(catalog_price.price), MIN(catalog_price.price_sale))
                     ELSE MIN(catalog_price.price) END'),
            'price_base'     => new Expression('MIN(catalog_price.price)'),
            'price_sale'     => new Expression('MIN(catalog_price.price_sale)'),
            'price_discount' => new Expression('MIN(catalog_price.price) - MIN(catalog_price.price_sale)')
        ]);

        $select->where->equalTo('catalog_items.active', 1)
            ->expression("MATCH (catalog_items.name, catalog_items.description, catalog_items.preview,
                     catalog_items.article) AGAINST(? IN BOOLEAN MODE)", [$query]);

        $select->order([$order]);

        if ($count) {
            $select->limit($count);
        }

        if ($this->usePagination) {
            $paginatorAdapter = new DbSelect($select, $this->table->getAdapter(),
                $this->table->getResultSetPrototype());
            $paginator = new Paginator($paginatorAdapter);
            $paginator->setDefaultItemCountPerPage($this->itemPerPage);
            $paginator->setCurrentPageNumber($this->currentPage);
            return $paginator;
        } else {
            $resultSet = $this->table->selectWith($select);
            return ArrayUtils::iteratorToArray($resultSet, false);
        }

    }

    /**
     * Подготовка поискового запроса
     *
     * @param string $query
     * @return string
     */
    private function prepareQuery(string $query): string
    {
        $query = strip_tags($query);
        $query = htmlspecialchars($query);
        $query = str_replace(",", " ", $query);
        $query = trim($query);

        do {
            $query = str_replace('  ', ' ', $query, $count);
        } while ($count);

        $words = explode(" ", $query);
        foreach ($words as $key => $value) {
            $words[$key] = strpos($value, '-') ? $words[$key] = "\"$value\"" : $words[$key] = "+$value*";
        }

        return implode(" ", $words);
    }

    /**
     * Преобразование массива параметров фильтрации
     *
     * @param array $filter
     */
    public function transformFilterArray(array &$filter)
    {
        $types = ['items', 'offers'];
        foreach ($types as $type) {
            if (isset($filter[$type]) && count($filter[$type])) {
                $transformed = [];
                foreach ($filter[$type] as $code => $values) {
                    foreach ($values as $value => $active) {
                        if ($active == 'true') {
                            $transformed[$code][] = $value;
                        }
                    }
                }
                $filter[$type] = $transformed;
            }
        }
    }

    /**
     * Получение минимальной и максимальной цены для указанных условий отбора
     *
     * @param array $filterData
     * @return ArrayObject|null
     */
    public function getPriceRange(array $filterData): ?ArrayObject
    {
        $select = new Select();
        $where = new Where();

        $select->from('catalog_items')
            ->columns([
                'min' => new Expression(
                    'CASE
                     WHEN MIN(catalog_price.price_sale) > 0
                        THEN LEAST(MIN(catalog_price.price), MIN(catalog_price.price_sale))
                     ELSE MIN(catalog_price.price) END'),
                'max' => new Expression(
                    'CASE
                     WHEN MAX(catalog_price.price_sale) > 0
                        THEN GREATEST(MAX(catalog_price.price), MAX(catalog_price.price_sale))
                     ELSE MAX(catalog_price.price) END')
            ]);


        // Фильтрация товара по выбранным категориям
        if ($filterData['category']) {
            $select->join('catalog_items_category', 'catalog_items.id = catalog_items_category.item', []);
            if (is_scalar($filterData['category'])) {
                $where->equalTo('catalog_items_category.category', $filterData['category']);
            } else {
                $where->in('catalog_items_category.category', $filterData['category']);
            }
        }

        // Генерация правил исключения товаров указанных групп из выборки
        if (isset($filterData['exclude'])) {
            $this->generateExcludeProperties($filterData);
        }

        // Генерация фильтрации на основе свойств товаров и торговых предложений
        $this->generateProperties($where, $filterData);

        // Выборка только активных товаров
        $where->equalTo('active', 1);

        // Если используется информация о наличии товара на складе
        if ($this->config['catalog']['options']['use-stock'] &&
            !$this->config['catalog']['options']['display-out-of-stock']) {
            $select->join('catalog_stock', 'catalog_items.id = catalog_stock.item', []);
            $where->greaterThan('catalog_stock.count', 0);
        }

        // Добавление в выборку поля с минимальной ценой наименования или торгового предложения
        $select->join('catalog_price', 'catalog_items.id = catalog_price.item', []);
        $select->where($where);

        $resultSet = $this->table->selectWith($select);

        /** @var ResultSet $resultSet */
        return $resultSet->current();
    }

    /**
     * Поиск записи по символьному коду
     *
     * @param string $code
     * @param int $category_id
     * @return ArrayObject|null
     */
    public function findByCode(string $code, int $category_id): ?ArrayObject
    {
        $select = new Select();
        $select->from('catalog_items');
        $select->columns(['*']);
        $select->join('catalog_items_category', 'catalog_items_category.item = catalog_items.id', []);
        $select->where->equalTo('code', $code);
        $select->where->equalTo('catalog_items_category.category', $category_id);

        /** @var ResultSet $resultSet */
        $resultSet = $this->table->selectWith($select);
        return $resultSet->current();
    }

    /**
     * Поиск наименования по внешнему коду
     *
     * @param string $guid
     * @return int|null
     */
    public function getIdByGuid(string $guid): ?int
    {
        $item = $this->fetchOneBy('guid', $guid);
        if ($item) {
            return $item->id;
        }
        return null;
    }

    /**
     * Выборка массива внешних кодов наименований из базы
     *
     * @return array
     */
    public function getGuids(): array
    {
        $this->setUsePagination(false);
        $guids = $this->verticalSlice('guid', 'id');
        $this->setUsePagination(true);
        return $guids;
    }

    /**
     * Поиск наименования по артикулу
     *
     * @param string $article
     * @return ArrayObject|null
     */
    public function findByArticle(string $article): ?ArrayObject
    {
        return $this->fetchOneBy('article', $article);
    }

    /**
     * Выборка категории для наименования
     *
     * @param int $item_id
     * @return array|ArrayObject|null
     */
    public function getLinkedCategory(int $item_id): ArrayObject|array|null
    {
        $select = new Select();
        $select->from('catalog_categories');
        $select->columns(['*']);
        $select->join('catalog_items_category', 'catalog_items_category.category = catalog_categories.id', []);
        $select->where->equalTo('catalog_items_category.item', $item_id);
        $select->limit(1);

        /** @var ResultSet $resultSet */
        $resultSet = $this->table->selectWith($select);
        return $resultSet->current();
    }

    /**
     * Retrieve input filter
     *
     * @return InputFilter
     */
    public function getInputFilter(): InputFilter
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new InputFilter();

            $this->inputFilter->add(
                [
                    'name'     => 'name',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'       => 'code',
                    'required'   => false,
                    'filters'    => [
                        [
                            'name' => StringTrim::class
                        ]
                    ],
                    'validators' => [
                        [
                            'name'    => StringLength::class,
                            'options' => [
                                'min' => 2,
                                'max' => 255
                            ]
                        ],
                        [
                            'name'    => Regex::class,
                            'options' => [
                                'pattern'  => '/^[a-zA-Z][a-zA-Z0-9_\-]*$/',
                                "messages" => [
                                    "regexNotMatch" => "Значение может состоять только из латинских букв, цифр, символа
                                     подчеркивания"
                                ]
                            ]
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'     => 'article',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'     => 'preview',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name'     => 'description',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );

        }
        return $this->inputFilter;
    }
}