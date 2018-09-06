<?php

namespace App\Admin\Form\Fields;

use Arbory\Base\Admin\Form\Fields\AbstractField;
use Arbory\Base\Html\Elements\Element;
use Arbory\Base\Html\Html;
use Illuminate\Http\Request;

/**
 * Class HorizontalLine
 * Separates fields horizontally for easier reading/grouping
 *
 * @package App\Admin\Form\Fields
 */
class HorizontalLine extends AbstractField
{
    public function __construct($name = '')
    {
        parent::__construct($name);
    }

    /**
     * @return Element
     */
    public function render()
    {
        return Html::div(
            [Html::hr(), Html::h2($this->getName())]
        )->addAttributes(['style' => 'clear:both;padding: 12px 24px;width:100%;']);
    }

    public function beforeModelSave(Request $request)
    {

    }
}
