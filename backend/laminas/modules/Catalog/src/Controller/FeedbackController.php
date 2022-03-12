<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Controller;

use DateTime;
use Laminas\Json\Json;
use Laminas\View\Model\JsonModel;
use MtLib\Base\Controller\AbstractCrudController;
use MtModules\Catalog\Form\Feedback;
use MtModules\Catalog\Model\Feedbacks;

/**
 * Class FeedbackController
 *
 * @package MtModules\Catalog\Controller
 */
class FeedbackController extends AbstractCrudController
{
    protected string $redirectRoute = 'catalog/feedback';

    /**
     * FeedbackController constructor.
     *
     * @param Feedbacks $model
     * @param Feedback $form
     */
    public function __construct(Feedbacks $model, Feedback $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

    /**
     * Добавление нового отзыва к товару
     *
     * @return JsonModel
     */
    public function addAction(): JsonModel
    {
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();

            if (!isset($post['username']) || !$post['username']) {
                $post['username'] = $this->acl()->getIdentity()->display_name;
            }
            $this->form->setData($post);

            if ($this->form->isValid()) {
                $data = $this->form->getData();
                $data['user'] = $this->acl()->getIdentity()->id;
                if ($this->acl()->hasRole('admin')) {
                    $data['active'] = 1;
                }

                $this->model->insert($data);

                return new JsonModel([
                    'status' => 1
                ]);
            }
        }

        return new JsonModel([
            'status' => 0
        ]);
    }

    /**
     * Удаление записи
     */
    public function deleteAction(): JsonModel
    {
        $id = $this->params()->fromRoute('id');
        $this->model->delete($id);
        return new JsonModel([
            'status' => 1
        ]);
    }


    /**
     * Получение списка отзывов о товаре
     */
    public function getlistAction(): JsonModel
    {
        $item_id = $this->params()->fromRoute('item');
        $this->model->setUsePagination(false);
        $items = $this->model->fetchActive($item_id);

        $rate_sum = 0;
        foreach ($items as $item) {
            $item->text = nl2br($item->text);
            $item->date = DateTime::createFromFormat('Y-m-d H:i:s', $item->date)->format("d.m.Y");
            $rate_sum += $item->rate;
        }

        if (count($items)) {
            $total_rate = round($rate_sum / count($items), 1);
        }


        return new JsonModel([
            'total_rate' => $total_rate ?? null,
            'items'      => $items
        ]);
    }

    /**
     * Получение списка отзывов в формате JSON для администрирования
     */
    public function getadminlistAction(): JsonModel
    {
        $post = Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY);
        $filterData = $post['filter'];
        $paramsData = $post['params'];

        $this->model->setItemsPerPage($paramsData['pageSize'] ?: 50);
        $this->model->setCurrentPage($filterData['page']);

        $orderField = null;
        if ($paramsData['order']['field']) {
            $orderField = $paramsData['order']['field'] . ' ' . $paramsData['order']['direction'];
        }

        $feedbacks = $this->model->fetchList($filterData['fields'], $orderField);

        foreach ($feedbacks as $feedback) {
            $feedback->format_date = DateTime::createFromFormat('Y-m-d H:i:s', $feedback->date)->format("d.m.Y H:i:s");
            $feedback->link = $this->url()->fromRoute('catalogrouter', ['item' => $feedback->item]);
        }

        return new JsonModel([
            'pagination' => [
                'current' => $feedbacks->getCurrentPageNumber(),
                'count'   => $feedbacks->count()
            ],
            'items'      => $feedbacks->getCurrentItems(),

        ]);
    }

}
