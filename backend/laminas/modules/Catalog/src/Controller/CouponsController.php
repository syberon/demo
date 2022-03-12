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
use MtModules\Catalog\Form\Coupon;
use MtModules\Catalog\Model\Coupons;

/**
 * Class CouponsController
 *
 * @package MtModules\Catalog\Controller
 */
class CouponsController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/coupons';

    /**
     * CouponsController constructor.
     *
     * @param Coupons $model
     * @param Coupon $form
     */
    public function __construct(Coupons $model, Coupon $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

    /**
     * Инициализация вывода списка записей
     */
    public function prepareIndex()
    {
        $this->additionalVariables['types'] = Coupon::$type;
        $this->additionalVariables['discount_types'] = Coupon::$discount_type;
    }
}
