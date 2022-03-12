<?php
/**
 * Copyright (c) 2019.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\AbstractCrudController;
use MtModules\Catalog\Form\Picture;
use MtModules\Catalog\Model\Pictures;

/**
 * Class PicturesController
 *
 * @package MtModules\Catalog\Controller
 */
class PicturesController extends AbstractCrudController
{
    /**
     * PicturesController constructor.
     * @param Pictures $model
     * @param Picture $form
     */
    public function __construct(Pictures $model, Picture $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

    /**
     * Удаление прикрепленного изображения
     *
     * @return JsonModel
     */
    public function deleteAction(): JsonModel
    {
        $id = $this->params()->fromRoute('id');
        $record = $this->model->find($id);

        if ($this->uploads()->has($record->picture)) {
            $this->uploads()->delete($record->picture);
        }
        $status = [
            'code' => 0
        ];
        if ($this->model->delete($id) !== false) {
            $status['code'] = 1;
            $status['id'] = $id;
            $status['pictures'] = $this->generatePictures($record->item);
        }
        return new JsonModel($status);
    }

    /**
     * Выборка и обработка прикрепленных изображений
     *
     * @param int $item_id
     * @return array|bool
     */
    private function generatePictures(int $item_id): bool|array
    {
        $pictures = $this->model->fetchForItem($item_id);
        foreach ($pictures as $picture) {
            $picture->processed = $this->imgUrl($this->uploads()->get($picture->picture)->getPublicUrl(),
                'catalog-items-edit');
        }
        return $pictures;
    }

    /**
     * Добавление новой записи
     *
     * @return JsonModel
     */
    public function addAction(): JsonModel
    {
        $item_id = $this->params()->fromRoute('item');
        $status = [
            'code' => 0
        ];

        if ($this->getRequest()->isPost()) {
            $post = array_merge_recursive($this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray());

            $this->form->setData($post);
            if ($this->form->isValid()) {
                $data = $this->form->getData();
                $uploadFile = $this->params()->fromFiles('picture');

                foreach ($uploadFile as $item) {
                    if ($item['name']) {
                        $uploadId = $this->uploads()->upload($item);
                        $data['picture'] = $uploadId;
                        $data['item'] = $item_id;
                        $this->model->insert($data);
                    }
                }
                $status['pictures'] = $this->generatePictures($item_id);
                $status['code'] = 1;
            }
        }

        return new JsonModel($status);
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
