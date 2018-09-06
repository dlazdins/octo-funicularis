<?php

namespace App\Admin\Form\Fields\Renderer;

use Arbory\Base\Admin\Form\Fields\FieldInterface;
use Arbory\Base\Admin\Form\FieldSet;
use Arbory\Base\Html\Elements\Element;
use Arbory\Base\Html\Html;
use Arbory\Base\Admin\Widgets\Button;

class FieldArrayRenderer
{
    protected $field;
    protected $fieldSet;

    /**
     * FieldArrayRenderer constructor.
     *
     * @param FieldInterface $field
     * @param FieldSet       $fieldSet fields containing fieldSet
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
        return Html::div(
            Html::section([
                $this->getHeader(),
                $this->getBody(),
                $this->getFooter(),
            ])
            ->addClass('nested')
            ->addAttributes([
                'data-name' => $this->field->getName(),
                'data-arbory-template' => $this->getFieldsetHtml('_template_'),
            ])
        )->addClass('type-sortable');
    }

    /**
     * @param       $index
     * @param array $values
     * @return $this
     */
    protected function getFieldsetHtml($index, $values = [])
    {
        // create clone fieldset with different index
        $fieldSet = new FieldSet($this->fieldSet->getModel(), $this->field->getNameSpacedName() . '.' . $index);

        $fields = $this->fieldSet->all();
        array_walk($fields, function ($val) use ($fieldSet) {
            $fieldSet->push($val);
        });

        $fieldSetHtml = Html::fieldset()
            ->addClass('item type-association')
            ->addAttributes([
                'data-name' => $this->field->getName(),
                'data-index' => $index
            ]);

        foreach ($fieldSet->getFields() as $field) {
            $field->setValue(array_get($values, $field->getName()));
            $fieldSetHtml->append($field->render());
        }

        $fieldSetHtml->append($this->getSortableNavigation());

        $fieldSetHtml->append(
            $this->getFieldSetRemoveButton($fieldSet->getNamespace() . '._destroy')
        );

        return $fieldSetHtml;
    }


    /**
     * @return Element
     */
    protected function getSortableNavigation()
    {

        $navigation = Html::div()->addClass('sortable-navigation');

        $navigation->append(Button::create()
            ->title(trans('arbory::fields.relation.moveDown'))
            ->type('button', 'only-icon secondary move-down')
            ->withIcon('chevron-down')
            ->iconOnly());

        $navigation->append(Button::create()
            ->title(trans('arbory::fields.relation.moveUp'))
            ->type('button', 'only-icon secondary move-up')
            ->withIcon('chevron-up')
            ->iconOnly());

        return $navigation;
    }

    /**
     * @return Element
     */
    protected function getHeader()
    {
        return Html::header(Html::h1($this->field->getName()));
    }

    /**
     * @return Element
     */
    protected function getBody()
    {
        $relationItems = [];

        $val = $this->field->getValue();
        if(is_array($val)){
            foreach ($val as $index => $arrayFields) {
                $relationItems[] = $this->getFieldsetHtml($index, $arrayFields);
            }
        }

        return Html::div($relationItems)->addClass('body list type-sortable"');
    }

    /**
     * @return Element|null
     */
    protected function getFooter()
    {
        $title = trans('arbory::fields.has_many.add_item', ['name' => $this->field->getName()]);

        return Html::footer(
            Html::button([
                Html::i()->addClass('fa fa-plus'),
                $title,
            ])
                ->addClass('button with-icon primary add-nested-item')
                ->addAttributes([
                    'type' => 'button',
                    'title' => $title,
                ])
        );
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
