<?php

namespace App\Admin\Form\Fields;

use Arbory\Base\Admin\Form\Fields\AbstractField;
use Arbory\Base\Html\Elements\Element;
use Arbory\Base\Html\Html;
use Illuminate\Http\Request;

/**
 * Class Text
 * @package Arbory\Base\Admin\Form\Fields
 */
class Table extends AbstractField
{
    private $headerColums = [];
    private $bodyRows = [];

    /**
     * @param array $columns
     * @return $this
     */
    public function setHeader(array $columns)
    {
        $this->headerColums = $columns;
        return $this;
    }

    /**
     * @param array $rows
     * @return $this
     */
    public function setBody(array $rows)
    {
        $this->bodyRows = $rows;
        return $this;
    }

    /**
     * @return array
     */
    private function getHead()
    {
        $head = array_map(function ($column){
            return Html::th(Html::span($column));
        }, $this->headerColums);
        return $head;
    }

    /**
     * @return array
     */
    private function getBody()
    {
        $rows = array_map(function ($row){
            $rowElement = Html::tr(
                array_map(function ($column){
                    return Html::td(Html::span($column));
                }, $row)
            );
            return $rowElement;
        }, $this->bodyRows);
        return $rows;
    }

    /**
     * @return Element
     */
    public function render()
    {
        return Html::div(
            Html::table([
                Html::thead(
                    Html::tr($this->getHead())
                ),
                Html::tbody($this->getBody())->addClass('tbody'),
            ])->addClass('table')->addAttributes(['style' => 'border-top: 1px solid #d4d4d4;'])
        )->addClass('field type-table');
    }

    /**
     * @param Request $request
     */
    public function beforeModelSave(Request $request)
    {

    }
}
