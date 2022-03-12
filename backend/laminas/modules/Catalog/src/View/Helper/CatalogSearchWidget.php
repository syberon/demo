<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Renderer\PhpRenderer;

class CatalogSearchWidget extends AbstractHelper
{

    public function __invoke(): Partial|string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        return $view->partial('mt-modules/catalog/partial/widget-search');
    }
}

