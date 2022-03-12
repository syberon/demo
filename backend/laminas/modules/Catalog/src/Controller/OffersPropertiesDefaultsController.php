<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use Laminas\Http\Response;
use Laminas\Session\Container;
use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\AbstractCrudController;
use MtModules\Catalog\Form\OffersPropertyDefault;
use MtModules\Catalog\Model\OffersProperties;
use MtModules\Catalog\Model\OffersPropertiesDefaults;

/**
 * Class OffersPropertiesDefaultsController
 *
 * @package MtModules\Catalog\Controller
 */
class OffersPropertiesDefaultsController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/offers-properties-defaults/list';

    protected Container $container;

    /**
     * ItemsPropertiesDefaultsController constructor.
     * @param OffersPropertiesDefaults $model
     * @param OffersProperties $modelOffersProperties
     * @param OffersPropertyDefault $form
     */
    public function __construct(
        OffersPropertiesDefaults $model,
        OffersPropertyDefault $form,
        protected OffersProperties $modelOffersProperties,
    )
    {
        $this->container = new Container(self::class);
        $this->model = $model;
        $this->form = $form;
    }

    /**
     * Вывод списка записей
     *
     * @return array|Response
     */
    public function indexAction(): Response|array
    {

        $property_id = $this->params()->fromRoute('property');
        if ($property_id) {
            $this->container->offsetSet('property', $property_id);
        }

        if (!$this->container->offsetExists('property')) {
            return $this->redirect()->toRoute('catalog/items-properties');
        }

        $property = $this->modelOffersProperties->find($this->container->offsetGet('property'));

        $records = $this->model->fetchByProperty($this->container->offsetGet('property'));

        return [
            'records'  => $records,
            'property' => $property
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

    /**
     * Дополнительная обработка данных перед вставкой
     *
     * @param array $data
     */
    protected function customProcessDataInsert(array &$data)
    {
        $data['property'] = $this->container->offsetGet('property');
    }

}
