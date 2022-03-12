<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Router;

use MtLib\Base\Lib\Common;
use MtModules\Catalog\Model\Categories;
use MtModules\Catalog\Model\Groups;
use MtModules\Catalog\Model\Items;
use Traversable;
use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\Router\Http\RouteInterface;
use Laminas\Router\Http\RouteMatch;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\RequestInterface as Request;


class CatalogRouter implements RouteInterface
{

    protected array $defaults = [];

    protected array $params = [];

    protected Categories $modelCategories;

    protected Items $modelItems;

    protected Groups $modelGroups;

    /**
     * Инициализация роутера
     *
     * @param array $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    /**
     * Создание экземпляра роутера с указанными параметрами
     * @param array $options
     * @return static
     */
    public static function factory($options = []): CatalogRouter
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = [];
        }

        return new static($options['defaults']);
    }

    /**
     * Установка модели работы с категориями
     *
     * @param Categories $modelCategories
     */
    public function setModelCategories(Categories $modelCategories)
    {
        $this->modelCategories = $modelCategories;
    }

    /**
     * Установка модели работы с наименованиями
     *
     * @param Items $modelItems
     */
    public function setModelItems(Items $modelItems)
    {
        $this->modelItems = $modelItems;
    }

    /**
     * Установка модели работы с группами товаров
     *
     * @param Groups $modelGroups
     */
    public function setModelGroups(Groups $modelGroups)
    {
        $this->modelGroups = $modelGroups;
    }

    /**
     * Match a given request.
     * @param Request $request
     * @param null $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null): RouteMatch|null
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }
        $uri = Common::prepareUri($request->getUri()->getPath());

        if ($uri) {
            $uri_parts = explode("/", $uri);
            $page = array_shift($uri_parts);

            if ($page == 'catalog') {
                // Выборка по группе, если задана в url
                if (count($uri_parts)) {
                    $group = $this->modelGroups->findByCode($uri_parts[0]);
                    if ($group) {
                        $params['group'] = $group->id;
                        array_shift($uri_parts);

                        // Если не задана категория
                        if (!count($uri_parts)) {
                            return new RouteMatch(array_merge($this->defaults, $params));
                        }
                    }
                }

                // Поиск категории в БД по url-адресу
                $category = $this->modelCategories->fetchByUrl(implode("/", $uri_parts));
                if ($category) {
                    $params['category'] = $category->id;
                    return new RouteMatch(array_merge($this->defaults, $params));
                }

                // Поиск наименования в БД
                $item_part = array_pop($uri_parts);
                $category = $this->modelCategories->fetchByUrl(implode("/", $uri_parts));
                if ($category) {
                    $item = $this->modelItems->findByCode($item_part, $category->id);
                    if ($item) {
                        $params['action'] = 'view';
                        $params['item'] = $item->id;
                        $params['category'] = $category->id;
                        return new RouteMatch(array_merge($this->defaults, $params));
                    }
                }
            }
        }

        return null;
    }

    /**
     * Assemble the route.
     * @param array $params
     * @param array $options
     * @return string|null
     */
    public function assemble(array $params = [], array $options = []): ?string
    {
        $this->params = $params;

        $route = '/catalog/';

        if (isset($params['group']) && $params['group']) {
            $group = $this->modelGroups->find($params['group']);
            if ($group) {
                $route .= $group->code . '/';
            }
        }

        if (isset($params['item']) && $params['item']) {
            // Строим мартшрут к наименованию, с учетом указанной категории
            $item = $this->modelItems->find($params['item']);
            if ($item) {
                $category = null;
                if (isset($params['category'])) {
                    $category = $this->modelCategories->find($params['category']);
                }
                // Если категория не задана или не найдена, выбираем первую привязанную к наименованию
                if (!$category) {
                    $category = $this->modelItems->getLinkedCategory($item->id);
                }

                $route .= $category->fullurl . '/' . $item->code;
                return $route;
            }
        }

        if (isset($params['category']) && $params['category']) {
            // Выбираем категорию из базы и возвращаем маршрут
            $category = $this->modelCategories->find($params['category']);
            if ($category) {
                $route .= $category->fullurl;
            }
        }
        return $route;
    }

    /**
     * Get a list of parameters used while assembling.
     */
    public function getAssembledParams(): array
    {
        return $this->params;
    }

}