<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\AbstractCrudController;
use MtModules\Catalog\Form\Group;
use MtModules\Catalog\Model\Groups;

/**
 * Class GroupsController
 *
 * @package MtModules\Catalog\Controller
 */
class GroupsController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/groups';

    /**
     * GroupsController constructor.
     *
     * @param Groups $model
     * @param Group $form
     */
    public function __construct(Groups $model, Group $form)
    {
        $this->model = $model;
        $this->form = $form;
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
