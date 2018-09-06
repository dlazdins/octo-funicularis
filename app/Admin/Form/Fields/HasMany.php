<?php

namespace App\Admin\Form\Fields;

use App\Admin\Form\Fields\Renderer\NestedFieldRenderer;
use Arbory\Base\Admin\Form\Fields\AbstractField;
use Arbory\Base\Admin\Form\Fields\Hidden;
use Arbory\Base\Admin\Form\Fields\Sortable;
use Closure;
use Arbory\Base\Admin\Form\Fields\Concerns\HasRelationships;
use Arbory\Base\Admin\Form\FieldSet;
use Arbory\Base\Html\Elements\Element;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;

/**
 * Class HasMany
 * @package Arbory\Base\Admin\Form\Fields
 */
class HasMany extends AbstractField
{
    use HasRelationships;

    /**
     * @var Closure
     */
    protected $fieldSetCallback;

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @var boolean
     */
    protected $canAddRelationItem = true;

    /**
     * @var boolean
     */
    protected $canSortRelationItems = true;

    /**
     * @var boolean
     */
    protected $canRemoveRelationItems = true;

    /**
     * AbstractRelationField constructor.
     * @param string  $name
     * @param Closure $fieldSetCallback
     */
    public function __construct( $name, Closure $fieldSetCallback )
    {
        parent::__construct( $name );

        $this->fieldSetCallback = $fieldSetCallback;
    }

    /**
     * @return bool
     */
    public function canAddRelationItem()
    {
        return $this->canAddRelationItem;
    }

    /**
     * @return bool
     */
    public function canSortRelationItems()
    {
        return $this->canSortRelationItems;
    }

    /**
     * @return bool
     */
    public function canRemoveRelationItems()
    {
        return $this->canRemoveRelationItems;
    }

    public function setSortable( bool $status )
    {
        $this->canSortRelationItems = $status;
        return $this;
    }

    public function setAddable( bool $status )
    {
        $this->canAddRelationItem = $status;
        return $this;
    }

    public function setRemovable( bool $status )
    {
        $this->canRemoveRelationItems = $status;
        return $this;
    }

    /**
     * @return Element|string
     */
    public function render()
    {
        return ( new NestedFieldRenderer( $this, $this->orderBy ) )->render();
    }

    /**
     * @param       $index
     * @param Model $model
     * @return FieldSet
     */
    public function getRelationFieldSet( $model, $index )
    {
        $fieldSet         = new FieldSet( $model, $this->getNameSpacedName() . '.' . $index );
        $fieldSetCallback = $this->fieldSetCallback;
        $fieldSetCallback( $fieldSet );

        $fieldSet->prepend(
            ( new Hidden( $model->getKeyName() ) )
                ->setValue( $model->getKey() )
        );

        if( $this->isSortable() && $this->getOrderBy() )
        {
            $fieldSet->prepend(
                ( new Hidden( $this->getOrderBy() ) )
                    ->setValue( $model->{$this->getOrderBy()} )
            );
        }

        return $fieldSet;
    }

    /**
     * @param Request $request
     */
    public function beforeModelSave( Request $request )
    {

    }

    /**
     * @param Request $request
     */
    public function afterModelSave( Request $request )
    {
        $items = (array) $request->input( $this->getNameSpacedName(), [] );

        foreach( $items as $index => $item )
        {
            $relatedModel = $this->findRelatedModel( $item );

            if( filter_var( array_get( $item, '_destroy' ), FILTER_VALIDATE_BOOLEAN ) )
            {
                $relatedModel->delete();

                continue;
            }

            $relatedFieldSet = $this->getRelationFieldSet(
                $relatedModel,
                $index
            );

            foreach( $relatedFieldSet->getFields() as $field )
            {
                $field->beforeModelSave( $request );
            }

            $relation = $this->getRelation();

            if( $relation instanceof MorphMany )
            {
                $relatedModel->setAttribute( $relation->getMorphType(), get_class( $this->getModel() ) ); // TODO:
            }

            $relatedModel->setAttribute( $relation->getForeignKeyName(), $this->getModel()->getKey() );

            $relatedModel->save();

            foreach( $relatedFieldSet->getFields() as $field )
            {
                $field->afterModelSave( $request );
            }
        }
    }


    /**
     * @param $variables
     * @return Model
     */
    private function findRelatedModel( $variables )
    {
        $relation = $this->getRelation();

        $relatedModelId = array_get( $variables, $relation->getRelated()->getKeyName() );

        return $relation->getRelated()->findOrNew( $relatedModelId );
    }

    /**
     * @return bool
     */
    protected function isSortable(): bool
    {
        foreach( $this->fieldSet->getFields() as $field )
        {
            if( $field instanceof Sortable && $field->getSortableField() === $this )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     * @return $this
     */
    public function setOrderBy( string $orderBy )
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        $rules = [];

        foreach( $this->getRelationFieldSet( $this->getRelatedModel(), '*' )->getFields() as $field )
        {
            $rules = array_merge( $rules, $field->getRules() );
        }

        return $rules;
    }
}
