<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use MtLib\Base\Controller\AbstractCrudController;
use MtModules\Catalog\Form\ItemsProperty;
use Laminas\View\Model\JsonModel;
use MtModules\Catalog\Form\OffersProperty;
use MtModules\Catalog\Model\OffersProperties;

/**
 * Class OffersPropertiesController
 *
 * @package MtModules\Catalog\Controller
 */
class OffersPropertiesController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/offers-properties';

    /**
     * OffersPropertiesController constructor.
     *
     * @param OffersProperties $model
     * @param OffersProperty $form
     */
    public function __construct(OffersProperties $model, OffersProperty $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

    /**
     * Инициализация вывода списка записей
     */
    public function prepareIndex()
    {
        $this->additionalVariables = [
            'types' => ItemsProperty::$propertyTypes
        ];
    }

    /**
     * Изменение порядка следования свойств
     */
    public function reorderAction(): JsonModel
    {
        if ($this->getRequest()->isPost()) {
            $listItems = $this->params()->fromPost('listValues');
            $index = 0;
            foreach (explode(',', $listItems) as $itemId) {
                $data = [
                    'sort_index' => $index++
                ];

                $this->model->update($data, $itemId);
            }
        }
        return new JsonModel();
    }

}
