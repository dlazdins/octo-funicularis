<?php

namespace App\Admin\Form\Fields\Renderer;

use App\Admin\Form\Fields\HorizontalLine;
use Arbory\Base\Admin\Form\Fields\FieldInterface;
use Arbory\Base\Admin\Form\FieldSet;
use Arbory\Base\Html\Elements\Element;
use Arbory\Base\Html\Html;
use Arbory\Base\Admin\Widgets\Button;

class FieldConditionalRenderer
{
    /**
     * @var FieldInterface
     */
    protected $field;

    /**
     * @var FieldSet
     */
    protected $fieldSet;

    /**
     * FieldArrayRenderer constructor.
     *
     * @param FieldInterface $field
     * @param FieldSet $fieldSet fields containing fieldSet
     */
    public function __construct(FieldInterface $field, FieldSet $fieldSet)
    {
        $this->field = $field;
        $this->fieldSet = $fieldSet;
    }

    /**
     * @return Element
     */
    public function render()
    {
        $fieldSet = new FieldSet($this->fieldSet->getModel(), $this->field->getNameSpacedName());

        $fieldSet->prepend(new HorizontalLine($this->field->getLabel()));

        $fields = $this->fieldSet->all();
        array_walk($fields, function ($val) use ($fieldSet) {
            $fieldSet->push($val);
        });

        $fieldSetHtml = Html::div()
            ->addClass('conditional-toggle')
            ->addAttributes([
                'data-name' => $this->field->getName(),
            ]);

        foreach ($fieldSet->getFields() as $field) {
            $fieldSetHtml->append($field->render());
        }

        return $fieldSetHtml;
    }

    /**
     * @return Element
     */
    protected function getHeader()
    {
        return Html::header(Html::h1($this->field->getName()));
    }


    /**
     * @param $name
     * @return Element
     */
    protected function getFieldSetRemoveButton($name)
    {
        $button = Button::create()
            ->title(trans('arbory::fields.relation.remove'))
            ->type('button', 'only-icon danger remove-nested-item')
            ->withIcon('trash-o')
            ->iconOnly();

        $input = Html::input()
            ->setType('hidden')
            ->setName($name)
            ->setValue('false')
            ->addClass('destroy');

        return Html::div([$button, $input])->addClass('remove-item-box');
    }
}
