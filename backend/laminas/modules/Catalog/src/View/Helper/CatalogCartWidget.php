<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\View\Helper;

use Exception;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Renderer\PhpRenderer;

class CatalogCartWidget extends AbstractHelper
{

    protected string $template = 'mt-modules/catalog/partial/widget-cart';

    /**
     * Установка шаблона для отображения виджета
     *
     * @param string $template
     * @return CatalogCartWidget
     */
    public function setTemplate(string $template): CatalogCartWidget
    {
        if ($template) {
            $this->template = $template;
        }

        return $this;
    }

    public function __invoke(): CatalogCartWidget
    {
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
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->inlineScript()->appendScript("window.modules.push('cms/modules/catalog/cart.widget')");
        return $view->partial($this->template);
    }
}

