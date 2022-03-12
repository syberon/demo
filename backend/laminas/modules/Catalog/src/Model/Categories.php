<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Model;

use ArrayObject;
use JetBrains\PhpStorm\Pure;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\EventManager\Event;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Paginator\Paginator;
use MtLib\Base\Model\AbstractModel;
use MtLib\Upload\Controller\Plugin\Uploads;

/**
 * Модель работы с базой категорий каталога
 *
 */
class Categories extends AbstractModel
{
    /**
     * Критерии сортировки по умолчанию
     */
    protected ?array $defaultOrder = ['sort_index ASC'];

    /**
     * Categories constructor.
     *
     * @param TableGatewayInterface $tableGateway
     * @param Uploads $uploadsPlugin
     */
    #[Pure]
    public function __construct(
        TableGatewayInterface $tableGateway,
        protected Uploads $uploadsPlugin
    )
    {
        parent::__construct($tableGateway);
    }

    /**
     * Поиск категории по её полному адресу
     *
     * @param string $url
     * @return ArrayObject|null
     */
    public function fetchByUrl(string $url): ?ArrayObject
    {
        $url = $url ?: '';
        return $this->fetchOne([
            'fullurl' => $url
        ]);
    }

    /**
     * Поиск категории по внешнему коду
     *
     * @param string $guid
     * @return ArrayObject|null
     */
    public function getByGuid(string $guid): ?ArrayObject
    {
        return $this->fetchOneBy('guid', $guid);
    }

    /**
     * Выбор полного списка всех дочерних категорий для указанной
     * @param $category_id
     * @param bool $asSimpleArray
     * @return array
     */
    public function getChildCategories($category_id, bool $asSimpleArray = false): array
    {
        $result = [];
        if ($category_id != 1) {
            $category = $this->find($category_id);
            if ($asSimpleArray) {
                $result[] = $category->id;
            } else {
                $result[] = $category;
            }
        }
        $childs = $this->fetchByParent($category_id);
        if (count($childs)) {
            foreach ($childs as $child) {
                $result = array_merge($result, $this->getChildCategories($child->id, $asSimpleArray));
            }
        }
        return $result;
    }

    /**
     * Поиск записи
     *
     * @param mixed $id
     * @return ArrayObject|null
     */
    public function find($id): ?ArrayObject
    {
        return parent::find($id);
    }

    /**
     * Получение списка подкатегорий для текущей родительской категории
     *
     * @param int $parent
     * @return array|Paginator
     */
    public function fetchByParent(int $parent): array|Paginator
    {
        return $this->fetch([
            'parent' => $parent,
            'active' => 1
        ], $this->defaultOrder);
    }

    /**
     * Генерация полного пути каталога
     *
     * @param int $category_id
     * @param ArrayObject|null $group
     * @param array $path
     */
    public function getPath(int $category_id, ?ArrayObject $group, array &$path)
    {
        $category = parent::find($category_id);
        if ($category->parent) {
            $path[] = [
                'name'    => $category->name,
                'fullurl' => '/catalog/' . (isset($group->code) ? $group->code . '/' : '') . $category->fullurl
            ];
            $this->getPath($category->parent, $group, $path);
        }
    }

    /**
     * Обновление индексов сортировки страниц
     *
     * @param Event $event
     * @var int $parent
     */
    public function regenerateIndexes(Event $event, int $parent = 0)
    {
        $childs = $this->fetch(['parent' => $parent], ['sort_index']);
        if ($childs) {
            $sort_key = 2;
            foreach ($childs as $category) {
                $data = [
                    'sort_index' => $sort_key
                ];
                $sort_key += 2;
                $this->table->update($data, ['id' => $category->id]);
                $this->regenerateIndexes($event, $category->id);
            }
        }
    }

    /**
     * Генерация полных адресов URL
     *
     * @param Event $event
     * @param int $parent
     * @param string $url
     */
    public function regenerateUrl(Event $event, int $parent = 0, string $url = '')
    {
        $childs = $this->fetch(['parent' => $parent], ['sort_index']);
        if ($childs) {
            foreach ($childs as $category) {
                $newurl = $url . ($url ? '/' : '') . $category->url;
                $data = [
                    'fullurl' => $newurl
                ];
                $this->table->update($data, ['id' => $category->id]);
                $this->regenerateUrl($event, $category->id, $newurl);
            }
        }
    }

    /**
     * Рекурсивная функция построения дерева
     *
     * @param int $parent
     * @param array $item
     */
    public function buildTree(int $parent, array &$item)
    {
        if (!$parent) {
            $category = $this->fetchOne(["parent" => "0"]);
            $item = [
                'key'    => $category->id,
                'href'   => $category->fullurl,
                'title'  => $category->name,
                'parent' => 0
            ];
            $this->buildTree($category->id, $item);
        } else {
            $childs = $this->fetch(['parent' => $parent], ['sort_index']);
            if (count($childs)) {
                $item['folder'] = true;
                $item['children'] = [];
                foreach ($childs as $key => $value) {
                    $category = [
                        'key'    => $value->id,
                        'href'   => $value->fullurl,
                        'title'  => $value->name,
                        'parent' => $parent
                    ];
                    if (!$value->active) {
                        $category['extraClasses'] = 'notactive';
                    }
                    $item['children'][$key] = $category;
                    $this->buildTree($value->id, $item['children'][$key]);
                }
            }
        }
    }

    /**
     * Рекурсивное удаление категорий
     *
     * @param mixed $whereOrId
     * @param bool $sql
     * @return int|null
     */
    public function delete($whereOrId, bool $sql = false): ?int
    {
        $childs = $this->fetch(['parent' => $whereOrId]);
        if ($childs) {
            foreach ($childs as $value) {
                $this->delete($value->id);
            }
        }

        // Удаляем прикрепленный файл
        $record = $this->find($whereOrId);

        if ($this->uploadsPlugin->has($record->picture)) {
            $this->uploadsPlugin->delete($record->picture);
        }

        return parent::delete($whereOrId);
    }

    /**
     * Retrieve input filter
     *
     * @return InputFilter
     */
    public function getInputFilter(): InputFilter
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new InputFilter();

            $this->inputFilter->add(
                [
                    'name'     => 'name',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => StringTrim::class
                        ]
                    ]
                ]
            );
        }
        return $this->inputFilter;
    }
}