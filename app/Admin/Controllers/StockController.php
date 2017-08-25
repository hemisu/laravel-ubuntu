<?php

namespace App\Admin\Controllers;

use App\Model\Stock;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Encore\Admin\Widgets\Box;
use App\Admin\Extensions\StockExporter;

use Encore\Admin\Auth\Permission;


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
            $grid->column('material','货物材质')->editable();
            $grid->inventory('库存')->display(function ($inventory) {
                if($inventory <= 0){
                  return "<span class='label label-danger'>$inventory</span>";
                }else if($inventory > 0 && $inventory < 3){
                  return "<span class='label label-warning'>$inventory</span>";
                }else{
                  return "<span class='label label-primary'>$inventory</span>";
                }

            })->sortable();
            if(Admin::user()->isRole('adminoperator')){//仅后台操作员能看到进价
              $grid->cost('进价')->display(function ($cost) {
                  return "<span class='label label-info'>$cost</span>";
              })->sortable();
            }
            $grid->price('零售价')->sortable()->editable();

            // $grid->created_at();
            $grid->updated_at('更新时间')->sortable();
            $grid->filter(function($filter){
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->where(function ($query) {
                    $query->where('name', 'like', "%{$this->input}%")
                        ->orWhere('type', 'like', "%{$this->input}%");
                }, '车辆名称');
                $filter->between('updated_at', '更新时间')->datetime();
            });
            $grid->exporter(new StockExporter());
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
              $actions->disableDelete();
              // $actions->disableEdit();
            });
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

            // $form->display('id', 'ID');
            $form->text('name', '名称')->rules('required|min:2');
            $form->text('type', '型号');
            $form->text('material', '货物材质');
            $form->number('inventory', '库存');
            $form->currency('cost', '进价')->symbol('￥');
            $form->currency('price', '零售价')->symbol('￥');
            // $form->display('created_at', '创建时间');
            // $form->display('updated_at', '更新时间');
        });
    }
    /**
     * API
     */
     public function apiStock(Request $request)
     {
         $q = $request->get('q');

         return Stock::where('name', 'like', "%$q%")
         ->paginate(null, ['id',DB::raw("concat(name,' 规格:',type,' 目前零售价:',price) as text")]);
     }

}
