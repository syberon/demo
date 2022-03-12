<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Entity;

use ArrayObject;
use MtLib\Image\Imagine\Filter\FilterManagerInterface;
use MtLib\Image\Imagine\Loader\LoaderManagerInterface;
use MtLib\Image\Service\CacheManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use MtLib\Upload\Controller\Plugin\Uploads;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class Category
 *
 * Класс для представления объекта категории каталога
 *
 * @package MtModules\Catalog\Entity
 *
 * @property int $id
 * @property int $parent
 * @property string $url
 * @property string $fullurl
 * @property string $name
 * @property string $description
 * @property string $picture
 * @property string $guid
 * @property int $sort_index
 * @property string $active
 * @property string $lang
 */
class Category extends ArrayObject
{
    protected int $id;
    protected int $parent;
    protected string $url;
    protected ?string $fullurl;
    protected string $name;
    protected string $description;
    protected ?string $picture;
    protected string $guid;
    protected int $sort_index;
    protected string $active;
    protected string $lang;

    protected array $config;

    protected Uploads $uploadPlugin;

    protected ContainerInterface $viewHelperManager;

    protected CacheManagerInterface $cacheManager;

    protected FilterManagerInterface $filterManager;

    protected LoaderManagerInterface $loaderManager;

    /**
     * @param ContainerInterface $viewHelperManager
     * @internal param Url $urlHelper
     */
    public function setViewHelperManager(ContainerInterface $viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param Uploads $uploadPlugin
     */
    public function setUploadPlugin(Uploads $uploadPlugin)
    {
        $this->uploadPlugin = $uploadPlugin;
    }

    /**
     * @param CacheManagerInterface $cacheManager
     */
    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param FilterManagerInterface $filterManager
     */
    public function setFilterManager(FilterManagerInterface $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @param LoaderManagerInterface $loaderManager
     */
    public function setLoaderManager(LoaderManagerInterface $loaderManager)
    {
        $this->loaderManager = $loaderManager;
    }

    /**
     * Установка коэффициента скидки для категории
     *
     * @param float $discount
     */
    public function setDiscount(float $discount)
    {
        $this->discount = $discount;
    }

    /**
     * Получение изображения для категории
     *
     * @param string|null $filter
     * @return null|string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPicture(?string $filter = null): ?string
    {
        if ($this->hasPicture()) {
            $filter = $filter ?: $this->config['catalog']['filters']['category'];
            $filterOptions = $this->filterManager->getFilterOptions($filter);
            if (isset($filterOptions['format'])) {
                $format = $filterOptions['format'];
            } else {
                $binary = $this->loaderManager->loadBinary($this->uploadPlugin->get($this->picture)->getPublicUrl(),
                    $filter);
                $format = $binary->getFormat() ?: 'png';
            }
            if ($this->cacheManager->isCachingEnabled($filter, $filterOptions)
                && $this->cacheManager->cacheExists($this->uploadPlugin->get($this->picture)->getPublicUrl(), $filter,
                    $format)
            ) {
                return '/' . $this->cacheManager->getCacheUrl($this->uploadPlugin->get($this->picture)->getPublicUrl(),
                        $filter, $format);
            }

            $urlHelper = $this->viewHelperManager->get('url');
            return $urlHelper('htimg/display', ['filter' => $filter],
                ['query' => ['relativePath' => $this->uploadPlugin->get($this->picture)->getPublicUrl()]]);
        }
        return null;
    }

    /**
     * Проверка существования загруженной фотографии
     *
     * @return bool
     */
    public function hasPicture(): bool
    {
        return $this->picture && $this->uploadPlugin->has($this->picture);
    }

    /**
     * Получение доступа к полям объекта
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * Преобразование объекта в массив
     *
     * @return array
     */
    public function toArray(): array
    {
        $this->setFlags(self::STD_PROP_LIST);
        return get_object_vars($this);
    }

}