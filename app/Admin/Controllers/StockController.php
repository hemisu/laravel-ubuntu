<?php

namespace App\Admin\Controllers;

use App\Model\Stock;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Admin\Extensions\StockExporter;


class StockController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('库存管理');
            $content->description('车辆库存');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('库存管理');
            $content->description('车辆库存编辑');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('库存管理');
            $content->description('车辆库存添加');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Stock::class, function (Grid $grid) {

            // $grid->id('ID')->sortable();
            $grid->column('name','名称')->editable()->sortable();
            $grid->column('type','型号')->editable();
            $grid->inventory('库存')->display(function ($inventory) {
                return "<span class='label label-primary'>$inventory</span>";
            });
            $grid->price('价格')->display(function ($price) {
                return "<span class='label label-info'>$price</span>";
            });

            // $grid->created_at();
            $grid->updated_at('更新时间');
            $grid->filter(function($filter){
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->where(function ($query) {
                    $query->where('name', 'like', "%{$this->input}%")
                        ->orWhere('type', 'like', "%{$this->input}%");
                }, '名称或型号');
                $filter->between('updated_at', '更新时间')->datetime();
            });
            $grid->exporter(new StockExporter());
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Stock::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name', '名称')->rules('required|min:2');
            $form->text('type', '型号');
            $form->number('inventory', '库存');
            $form->currency('price', '价格')->symbol('￥');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
