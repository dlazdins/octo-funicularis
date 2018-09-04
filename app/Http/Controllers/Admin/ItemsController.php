<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Arbory\Base\Admin\Form;
use Arbory\Base\Admin\Form\Fields\ArboryImage;
use Arbory\Base\Admin\Form\Fields\Checkbox;
use Arbory\Base\Admin\Form\Fields\Slug;
use Arbory\Base\Admin\Form\Fields\Text;
use Arbory\Base\Admin\Form\Fields\Textarea;
use Arbory\Base\Admin\Form\Fields\Translatable;
use Arbory\Base\Admin\Grid;
use Arbory\Base\Admin\Grid\Filter;
use Arbory\Base\Admin\Traits\Crudify;
use Illuminate\Database\Eloquent\Model;
use App\Items\Item;

class ItemsController extends Controller
{
    use Crudify;

    protected $resource = Item::class;

    /**
     * @param Model $model
     * @return Form
     */
    protected function form(Model $model)
    {

        $form = $this->module()->form($model, function (Form $form) {
            $form->addField(new Checkbox('active'))->setLabel(trans('admin/common.active'));
            $form->addField(new Text('order'))->rules('required|integer')->setLabel(trans('admin/common.order'));
            $form->addField(new Translatable((new Text('name'))))->setLabel(trans('admin/common.name'));
            $form->addField(new Translatable((new Slug('slug', 'name', $this->url('api', 'slug_generator')))));
            $form->addField(new Translatable((new Textarea('description'))));
            $form->addField(new HorizontalLine());
            $form->addField(new Text('price'))->rules('required')->setLabel(trans('admin/common.price'));
            $form->addField(new Text('amount'))->rules('required')->setLabel(trans('admin/common.amount'));
            $form->addField(new HorizontalLine());
            $form->addField(new ArboryImage('image'))->setLabel('Product open image, recommended size 1800x1200');
            $form->addField(new ArboryImage('list_image'))->setLabel('Card image, recommended size 460x280');
            $form->addField(new HorizontalLine());
        });

        return $form;
    }

    /**
     * @return Grid
     */
    public function grid()
    {
//        Admin::assets()->css('arbory/css/admin.css');
        $grid = $this->module()->grid($this->resource(), function (Grid $grid){
            $grid->column('active', trans('admin/common.active'));
            $grid->column('name', trans('admin/common.name'));
            $grid->column('price', trans('admin/common.price'));
            $grid->column('order', trans('admin/common.order'));
            $grid->column('created_at', trans('admin/common.created_at'));
        });

        $grid->filter(function (Filter $filter) {
            $filter->setPerPage(10);
            $filter->getQuery()->orderBy('order');
        });

        return $grid;
    }
}
