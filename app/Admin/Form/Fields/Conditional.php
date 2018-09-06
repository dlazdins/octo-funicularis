<?php

namespace App\Admin\Form\Fields;

use App\Builder\Block;
use Arbory\Base\Admin\Form\Fields\AbstractField;
use Arbory\Base\Admin\Form\Fields\HasOne;
use Arbory\Base\Html\Elements\Element;
use Arbory\Base\Html\Html;
use Illuminate\Http\Request;

class Conditional extends AbstractField
{

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $toggle;

    /**
     * Conditional constructor.
     *
     * @param string $toggle
     * @param string $name
     * @param HasOne $field
     */
    public function __construct(string $toggle, string $name, HasOne $field)
    {
        $this->field = $field;
        $this->toggle = $toggle;
        parent::__construct($name);
    }

    /**
     * @return Element
     */
    public function render()
    {
        $this->field->setFieldSet( $this->getFieldSet() );

        return Html::section(
            Html::div(
                Html::fieldset(
                    $this->field->render()
                )->addClass('item')
            )->addClass('body list')
        )->addClass('nested')->addAttributes([
            'data-element-type' => $this->field->name,
            'data-element-toggle' => $this->toggle
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function beforeModelSave(Request $request)
    {

    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function afterModelSave(Request $request)
    {
        $this->field->setFieldSet($this->getFieldSet());
        $relation = $this->field->getRelation();

        /** @var Block $block */
        $block = $this->getModel();

        if ($this->field->name === $block->getElementType()) {
            $this->field->afterModelSave($request);
        } else {
            $this->field->getRelatedModel()
                ->firstOrNew([$relation->getForeignKeyName() => $block->id])
                ->delete();
        }
    }

}
