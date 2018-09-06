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
class Description extends AbstractField
{
    /**
     * @return Element
     */
    public function render()
    {
        return Html::header(
            Html::h2($this->getName())
        )->addAttributes(['style' => 'clear:both;padding: 12px 24px;width:100%;']);
    }

    /**
     * @param Request $request
     */
    public function beforeModelSave(Request $request)
    {

    }
}
