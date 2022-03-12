<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use MtLib\Base\Controller\AbstractCrudController;
use MtModules\Catalog\Form\Currency;
use MtModules\Catalog\Model\Currencies;

/**
 * Class CurrenciesController
 *
 * @package MtModules\Catalog\Controller
 */
class CurrenciesController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/currencies';

    /**
     * CurrenciesController constructor.
     *
     * @param Currencies $model
     * @param Currency $form
     */
    public function __construct(Currencies $model, Currency $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

}
