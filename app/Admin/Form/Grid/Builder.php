<?php

namespace App\Admin\Grid;

use \Arbory\Base\Admin\Grid\Builder as BaseBuilder;
use Arbory\Base\Admin\Layout\Footer\Tools;
use Arbory\Base\Admin\Widgets\Pagination;
use Arbory\Base\Html\Elements\Content;
use Arbory\Base\Html\Html;
use Arbory\Base\Html\HtmlString;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class Builder extends BaseBuilder
{
    /**
     * @var
     */
    protected $filters;

    /**
     * @var Html|null
     */
    protected $statistics = null;

    /**
     * @var array
     */
    protected $copyableData = [];

    /**
     * @param $filters
     * @return $this
     */
    public function setFilterInputs($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @param $statistics
     * @return $this
     */
    public function setStatistics($statistics)
    {
        $this->statistics = $statistics;
        return $this;
    }

    protected function createFilterForm($content)
    {
        $submitButton = Html::button(trans('admin/common.filter_submit'))
            ->addClass('button ')
            ->addAttributes([
                'type' => 'submit',
            ]);

        return Html::form([$content, $submitButton])
            ->addClass('grid-filters')
            ->addAttributes(['action' => $this->url('index')]);
    }

    /**
     * @param $data
     */
    public function setCopyableArray($data)
    {
        $this->copyableData = $data;
    }

    /**
     * @param Collection|Paginator $items
     * @return Content
     */
    public function render($items)
    {
        $this->items = $items;

        return new Content([
            Html::header([
                $this->breadcrumbs(),
                $this->searchField(),
            ]),
            Html::div(
                $this->createFilterForm($this->filters)
            ),
            $this->statistics,
            Html::section([
                $this->table(),
                $this->footer(),
            ]),
            $this->getCopyableData()
        ]);
    }

    /**
     * @return Tools
     */
    protected function footerTools()
    {
        $tools = new Tools();
        $tools->getBlock('primary')->push($this->createButton());
        $this->addCustomToolsToFooterToolset($tools);

        if ($this->grid->isPaginated() && $this->items->hasPages()) {
            $pagination = (new Pagination($this->items))->render();
            $tools->getBlock($pagination->attributes()->get('class'))->push($pagination->content());
        }

        return $tools;
    }

    /**
     * @return null
     */
    protected function getCopyableData()
    {
        if (empty($this->copyableData)) return null;

        return Html::script(
            new HtmlString('var copyContent = ' . json_encode($this->copyableData))
        );
    }
}