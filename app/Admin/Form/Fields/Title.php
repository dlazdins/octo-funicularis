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
class Title extends AbstractField
{
    /**
     * @return Element
     */
    public function render()
    {
        return Html::header(
            Html::h2( $this->getValue() )
        );
    }

    public function beforeModelSave( Request $request )
    {
    }
}
