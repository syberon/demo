<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class CrudController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Имя модуля для генерации пространства имен
     *
     * @var string
     */
    protected string $module;

    /**
     * Класс используемой модели
     *
     * @var string|Model|Builder
     */
    protected mixed $model;

    /**
     * Используется ли в контроллере постраничная разбивка вывода моделей
     *
     * @var bool
     */
    protected bool $usePagination = false;

    /**
     * Размер страницы для отображения списка моделей
     *
     * @var int
     */
    protected int $pageSize = 15;

    /**
     * Класс запроса формы для добавления записи
     *
     * @var string
     */
    protected string $formRequestCreate = FormRequest::class;

    /**
     * Класс запроса формы для редактирования записи
     *
     * @var string
     */
    protected string $formRequestUpdate = FormRequest::class;


    /**
     * Шаблоны для отображения действий контроллера
     *
     * @var array|string[]
     */
    protected array $templates = [
        'index'  => '',
        'create' => '',
        'edit'   => '',
        'show'   => ''
    ];

    protected string $route_path_part = '';


    /**
     * Инициализация шаблонов по умолчанию
     */
    public function __construct()
    {
        // Инициализация шаблонов по умолчанию
        $modelPath = strtolower($this->module) . '::' . Str::of($this->model)->afterLast('\\')->lower();
        foreach ($this->templates as $key => &$value) {
            $value = $modelPath . '.' . $key;
        }

        $this->route_path_part = Str::of($this->module)
            ->lower()
            ->append('.')
            ->append(Str::of($this->model)->afterLast('\\')->lower())
            ->append('s.');

        $this->model = new ($this->model);
    }


    /**
     * Отображение списка моделей
     *
     * @return View
     */
    public function index(): View
    {
        if ($this->usePagination) {
            $records = $this->model->paginate($this->pageSize);
        }
        else {
            $records = $this->model->all();
        }

        $this->listing($records);

        return view($this->templates['index'], compact('records'));
    }

    /**
     * Просмотр модели
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $record = $this->model->findOrFail($id);

        $this->showing($record);

        return view($this->templates['show'], compact('record'));
    }

    /**
     * Добавление новой модели
     *
     * @return View
     */
    public function create(): View
    {
        $this->creating();
        return view($this->templates['create'])->with('record', $this->model->newInstance());
    }

    /**
     * Сохранение новой модели в базу данных
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        /** @var FormRequest $request */
        $request = App::make($this->formRequestCreate);

        $model = $this->model->newInstance();
        $model->fill($request->validated());

        $this->storing($request, $model);

        $model->save();

        $this->stored($request, $model);

        return to_route($this->route_path_part . 'index');
    }

    /**
     * Редактирование модели
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $model = $this->model->findOrFail($id);

        $this->editing($model);

        return view($this->templates['edit'])->with('record', $model);
    }

    /**
     * Обновление информации о модели в базе данных
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function update(int $id): RedirectResponse
    {
        /** @var FormRequest $request */
        $request = App::make($this->formRequestCreate);

        $model = $this->model->findOrFail($id);
        $model->fill($request->validated());

        $this->updating($request, $model);

        $model->save();

        $this->updated($request, $model);

        return to_route($this->route_path_part . 'index');
    }

    /**
     * Удаление модели из базы данных
     *
     * @param int $id
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function destroy(int $id): RedirectResponse
    {
        $model = $this->model->findOrFail($id);

        $this->deleting($model);

        $model->deleteOrFail();

        $this->deleted($model);

        return redirect()->back();
    }


    /**
     * Дополнительное действие перед обновлением модели
     *
     * @param FormRequest $request
     * @param Model $model
     */
    public function updating(FormRequest $request, Model $model)
    {
    }

    /**
     * Дополнительное действие после обновления модели
     *
     * @param FormRequest $request
     * @param Model $model
     */
    public function updated(FormRequest $request, Model $model)
    {
    }

    /**
     * Дополнительное действие перед добавлением модели
     *
     * @param FormRequest $request
     * @param Model $model
     */
    public function storing(FormRequest $request, Model $model)
    {
    }

    /**
     * Дополнительное действие после обновления модели
     *
     * @param FormRequest $request
     * @param Model $model
     */
    public function stored(FormRequest $request, Model $model)
    {
    }

    /**
     * Дополнительное действие перед удалением модели
     *
     * @param Model $model
     */
    public function deleting(Model $model)
    {
    }

    /**
     * Дополнительное действие после удаления модели
     *
     * @param Model $model
     */
    public function deleted(Model $model)
    {
    }

    /**
     * Дополнительное действие перед показом модели
     *
     * @param Model $model
     */
    public function showing(Model $model)
    {
    }

    /**
     * Дополнительное действие перед открытием формы создания модели
     */
    public function creating()
    {
    }

    /**
     * Дополнительное действие перед открытием формы редактирования модели
     *
     * @param Model $model
     */
    public function editing(Model $model)
    {
    }

    /**
     * Дополнительное действие перед отображением списка моделей
     *
     * @param array|Collection|Paginator|LengthAwarePaginator $records
     */
    public function listing(array|Collection|Paginator|LengthAwarePaginator $records)
    {
    }
}
