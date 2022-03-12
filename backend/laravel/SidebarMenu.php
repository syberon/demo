<?php
/*
 * Copyright (c) 2022.
 *
 * @author Syber
 */

namespace Modules\Admin\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\View\Component;

class SidebarMenu extends Component
{
    const TYPE_DROPDOWN = "dropdown";
    const TYPE_LINK = "link";
    const TYPE_HEADER = "header";

    /**
     * Шаблон навигации
     * @var string
     */
    protected string $template;

    protected ?object $navigation;

    public function __construct(
        Request              $request,
        protected Stringable $currentRouteName,
        protected Stringable $currentPath
    )
    {
        $this->currentRouteName = Str::of($request->route()->getName());
        $this->currentPath = Str::of($request->path());
        $this->parseNavigationFile();
        $this->markActivePage();
    }

    /**
     * Разбор JSON файла со структурой категорий
     *
     * @return void
     */
    protected function parseNavigationFile()
    {
        $file = module_path(config('admin.name')) . '/Storage/sidebar.json';
        $this->navigation = json_decode(file_get_contents($file));
    }

    /**
     * Поиск и маркировка активных категорий в дереве
     *
     * @return void
     */
    protected function markActivePage()
    {
        if ($this->navigation && isset($this->navigation->blocks) && count($this->navigation->blocks)) {
            foreach ($this->navigation->blocks as $block) {
                if (isset($block->items) && is_iterable($block->items)) {
                    $this->checkItems($block->items);
                }
            }
        }
    }

    /**
     * Поиск и маркировка активных категорий в дереве
     *
     * @param array $items
     * @return bool
     */
    protected function checkItems(array $items): bool
    {
        $hasActive = false;
        foreach ($items as $item) {
            if (isset($item->items) && is_iterable($item->items)) {
                $hasActive = $this->checkItems($item->items);
                if ($hasActive) {
                    $item->active = true;
                    break;
                }
            }
            if (isset($item->route) && !empty($item->route)) {
                if ($this->currentRouteName->startsWith(Str::of($item->route)->beforeLast('.'))) {
                    $item->active = true;
                    $hasActive = true;
                    break;
                }
            } elseif (isset($item->url) && !empty($item->url)) {
                if ($this->currentPath->startsWith($item->url)) {
                    $item->active = true;
                    $hasActive = true;
                    break;
                }
            }
        }
        return $hasActive;
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('admin::components.sidebar-menu', ['navigation' => $this->navigation]);
    }
}
