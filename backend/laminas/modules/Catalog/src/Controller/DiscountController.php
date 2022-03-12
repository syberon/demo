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
use MtModules\Catalog\Form\Discount as DiscountForm;
use MtModules\Catalog\Model\Discount;

/**
 * Class DiscountController
 *
 * @package MtModules\Catalog\Controller
 */
class DiscountController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/discount';

    /**
     * DiscountController constructor.
     *
     * @param Discount $model
     * @param DiscountForm $form
     */
    public function __construct(Discount $model, DiscountForm $form)
    {
        $this->model = $model;
        $this->form = $form;
    }
}
