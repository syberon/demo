<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\View\Helper;

use Exception;
use Laminas\View\Helper\Partial;
use MtModules\Catalog\Model\Items;
use MtModules\Catalog\Model\Pictures;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;

class CatalogItemsWidget extends AbstractHelper
{
    protected ?array $filterData = null;

    protected ?array $orderData = null;

    protected int $limit = 4;

    protected string $template = 'mt-modules/catalog/partial/widget-items';

    /**
     * @param Items $modelItems
     * @param Pictures $modelPictures
     * @param array $config
     */
    public function __construct(
        protected Items $modelItems,
        protected Pictures $modelPictures,
        protected array $config
    )
    {
    }

    /**
     * Установка шаблона для отображения виджета
     *
     * @param string $template
     * @return CatalogItemsWidget
     */
    public function setTemplate(string $template): CatalogItemsWidget
    {
        if ($template) {
            $this->template = $template;
        }

        return $this;
    }

    /**
     * @param array $filterData
     * @param int|null $limit
     * @return CatalogItemsWidget
     */
    public function __invoke(array $filterData, ?int $limit = null): CatalogItemsWidget
    {
        if (isset($filterData['order'])) {
            $this->orderData = $filterData['order'];
            unset($filterData['order']);
        }

        $this->filterData = $filterData;
        if ($limit) {
            $this->limit = $limit;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            $msg = get_class($e) . ': ' . $e->getMessage();
            trigger_error($msg, E_USER_ERROR);
        }
    }

    /**
     * Отображение виджета
     */
    public function render(): Partial|string
    {
        $this->modelItems->setUsePagination(false);

        $order = $this->orderData ?: [
            'field'     => 'RAND()',
            'direction' => 'asc'
        ];

        $items = $this->modelItems->getFilteredList($this->filterData, $order, $this->limit);
        $this->modelItems->setUsePagination(true);

        foreach ($items as $item) {
            $picture = $this->modelPictures->fetchMain($item->id);
            if ($picture) {
                $item->picture = $picture->picture;
            }
        }

        /** @var PhpRenderer $view */
        $view = $this->getView();
        return $view->partial($this->template, [
            'items' => $items
        ]);
    }

}

