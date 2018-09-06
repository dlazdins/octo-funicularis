<?php

namespace App\Admin\Form\Fields\Renderer;

use Arbory\Base\Html\Elements\Element;
use Arbory\Base\Html\Html;
use Arbory\Base\Admin\Form\Fields\Renderer\InputFieldRenderer;

/**
 * Class DateFieldRenderer
 * @package App\Admin\Form\Fields\Renderer
 */
class DateFieldRenderer extends InputFieldRenderer
{
    /**
     * @return Element
     */
    protected function getInput()
    {
        $value = $this->field->getValue();

        $input = Html::input()
            ->setName($this->field->getNameSpacedName())
            ->addClass('text datetime-picker');

        if ($value) {
            $value = date('Y-m-d H:i', strtotime($value));
            $input->setValue(date('Y-m-d H:i', strtotime($value)));
        }

        return $input;
    }
}
