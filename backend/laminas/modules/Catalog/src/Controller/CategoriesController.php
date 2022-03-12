<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use Exception;
use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\AbstractCrudController;
use MtLib\SeoUrl\Service\SeoUrl;
use MtModules\Catalog\Entity\Category as CategoryEntity;
use MtModules\Catalog\Form\Category;
use MtModules\Catalog\Model\Categories;

/**
 * Class CategoriesController
 *
 * @package MtModules\Catalog\Controller
 */
class CategoriesController extends AbstractCrudController
{

    protected string $redirectRoute = 'catalog/categories';

    protected bool $useUploadImage = true;

    /**
     * AbstractCrudController constructor.
     *
     * @param Categories $model
     * @param SeoUrl $seoUrl
     * @param Category $form
     */
    public function __construct(
        Categories $model,
        Category $form,
        protected SeoUrl $seoUrl,
    )
    {
        $this->model = $model;
        $this->form = $form;
    }

    /**
     * Ручная сортировка категорий
     */
    public function reorderAction(): JsonModel
    {
        $source = $this->params()->fromPost('source'); // куда мы двигаем
        $dest = $this->params()->fromPost('dest');     // что мы двигаем
        $hitmode = $this->params()->fromPost('hitmode');

        $srcItem = $this->model->find($source);
        if ($hitmode == 'after') {
            $dataDst = [
                'sort_index' => $srcItem->sort_index + 1,
                'parent'     => $srcItem->parent
            ];
        } elseif ($hitmode == 'over') {
            $dataDst = [
                'sort_index' => $srcItem->sort_index + 1,
                'parent'     => $srcItem->id
            ];
        } else {
            $dataDst = [
                'sort_index' => $srcItem->sort_index - 1,
                'parent'     => $srcItem->parent
            ];
        }

        $this->model->update($dataDst, $dest);
        $status['code'] = 1;
        return new JsonModel($status);
    }

    /**
     * Получение информации о категории
     */
    public function getinfoAction(): JsonModel
    {
        /** @var CategoryEntity $category */
        $category = $this->model->find($this->params()->fromRoute('id'));
        return new JsonModel($category);
    }

    /**
     * Получение дерева категорий
     */
    public function gettreeAction(): JsonModel
    {
        $use_root = $this->params()->fromQuery('use_root');
        $categoriesTree = [];
        $this->model->buildTree(0, $categoriesTree);
        if ($use_root) {
            return new JsonModel([$categoriesTree]);
        } else {
            return new JsonModel($categoriesTree);
        }
    }

    /**
     * Включение/отключение выбранной категории
     */
    public function toggleAction(): JsonModel
    {
        $item = $this->model->find($this->params()->fromRoute('id'));
        $data = [
            "active" => $item->active ? 0 : 1
        ];
        $this->model->update($data, $this->params()->fromRoute('id'));

        $status = [
            'code'   => 1,
            'active' => $data['active'],
            'id'     => $item->id
        ];

        return new JsonModel($status);
    }

    /**
     * Удаление записи
     *
     */
    public function deleteAction(): JsonModel
    {
        $id = $this->params()->fromRoute('id');
        $status = [
            'code' => 0
        ];
        if ($this->model->delete($id) !== false) {
            $status['code'] = 1;
        }
        return new JsonModel($status);
    }


    /**
     * Дополнительная обработка данных перед вставкой
     *
     * @param array $data
     * @throws Exception
     */
    protected function customProcessDataInsert(array &$data)
    {
        $data['parent'] = $this->params()->fromRoute('parent', 1);
        if (!$data['url']) {
            $data['url'] = $this->seoUrl->create($data['name']);
        }
    }

    /**
     * Дополнительная обработка данных перед обновлением
     *
     * @param array $data
     * @param int|string $id
     * @throws Exception
     */
    protected function customProcessDataUpdate(array &$data, int|string $id)
    {
        if (!$data['url']) {
            $data['url'] = $this->seoUrl->create($data['name']);
        }
    }
}
