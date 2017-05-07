<?php

namespace App\Admin\Controllers;

use App\Model\SalesRecord;
use App\Model\Stock;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SalesRecordController extends Controller
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

            $content->header('零售订单');
            $content->description('零售订单页');

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

            $content->header('零售订单');
            $content->description('编辑');

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

            $content->header('零售订单');
            $content->description('创建');

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
        return Admin::grid(SalesRecord::class, function (Grid $grid) {

            $grid->id('订单号')->sortable();
            $grid->stock()->name('名称');
            $grid->stock()->type('规格');
            $grid->price('售价')->display(function ($cost) {
                return "<span class='label label-info'>$cost</span>";
            });
            // $grid->created_at();
            $grid->ispay('生效')->value(function ($ispay) {
                return $ispay ?
                    "<i class='fa fa-check' style='color:green'></i>" :
                    "<i class='fa fa-close' style='color:red'></i>";
            });
            $grid->updated_at('订单日期');
            $grid->disableBatchDeletion();
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
        return Admin::form(SalesRecord::class, function (Form $form) {

          // dd($collect);
            $form->hidden('id');
            $form->select('stock_id','车辆型号规格')->options(
              Stock::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name'].' 规格:'.$item['type'].' 目前零售价:'.$item['price']];
            })
              )->ajax('/admin/api/stock');
            $form->currency('price', '销售金额')->symbol('￥');
            $states = [
                'on'  => ['value' => 1, 'text' => '生效', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '作废', 'color' => 'danger'],
            ];
            $form->switch('ispay', '订单生效')->states($states)->default(1);
            $form->textarea('remarks','订单备注')->rows(3);
            // $form->display('created_at', 'Created At');
            $form->display('updated_at', '订单日期');
            // echo url()->current();
            //保存后进行验证

            $form->saved(function ($form) {
                if($form->id) {//修改表格时
                  $isChange = ($form->ispay = 'on')?true:false;
                  $ispay = (SalesRecord::findOrFail($form->id)->toArray()['ispay'])?true:false;
                  if($form->ispay == 'on' && $ispay == $isChange){//修改表格时订单支付状态改变
                    DB::table('stocks')->where('id',$form->stock_id)->decrement('inventory',1);
                  }else{//作废
                    DB::table('stocks')->where('id',$form->stock_id)->increment('inventory',1);
                  }
                }else{//创建表格时
                  if($form->ispay == 'on'){
                    DB::table('stocks')->where('id',$form->stock_id)->decrement('inventory',1);
                  }
                }
                // // DB::table('stocks')->decrement('inventory',1);
                // // echo $request->url();

            });
        });
    }
    /**
     * API
     */
}
