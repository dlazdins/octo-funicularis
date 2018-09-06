<?php

namespace App\Admin\Form\Fields;

use App\Admin\Form\Fields\Renderer\FieldArrayRenderer;
use Arbory\Base\Admin\Form\Fields\AbstractField;
use Illuminate\Http\Request;
use Arbory\Base\Admin\Form\FieldSet;

class FieldArray extends AbstractField
{
    protected $fieldSetCallback;

    public function __construct($name, \Closure $fieldSetCallback)
    {
        parent::__construct($name);
        $this->fieldSetCallback = $fieldSetCallback;
    }

    public function getRules(): array
    {
        $rules = [];
        // using * in order to apply rules to all array indexes (1, 2, 3, ...)
        foreach ($this->getContentFieldSet('*')->getFields() as $field) {
            $rules = array_merge($rules, $field->getRules());
        }
        return $rules;
    }

    public function render()
    {
        return (new FieldArrayRenderer($this, $this->getContentFieldSet()))->render();
    }

    public function getContentFieldSet($index = null) : FieldSet
    {
        $fieldSet = new FieldSet($this->getModel(), $this->getNameSpacedName() . ($index ? ('.' . $index) : ''));
        $fieldSetCallback = $this->fieldSetCallback;
        $fieldSetCallback($fieldSet);
        return $fieldSet;
    }

    /**
     * @param Request $request
     */
    public function afterModelSave(Request $request)
    {
        $items = (array)$request->input($this->getNameSpacedName(), []);

        $values = [];
        //TODO : check for unwanted input fields using fieldset fields?
        // remove deleted/destroyed fields and unset _destroy input field from all field sets
        foreach ($items as $index => $item) {
            if (filter_var(array_get($item, '_destroy'), FILTER_VALIDATE_BOOLEAN) === false) {
                unset($item['_destroy']);
                $values[] = $item;
                continue;
            }
        }

        $model = $this->getModel();
        $model->{$this->getName()} = $values;
        $model->save();
    }

}