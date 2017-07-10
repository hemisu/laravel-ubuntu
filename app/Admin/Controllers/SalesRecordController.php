<?php

namespace App\Admin\Controllers;

use App\Model\SalesRecord;
use App\Model\Stock;
use App\Model\Client;
use App\Model\Staff;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

use App\Admin\Extensions\SalesRecordExporter;

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
            $content->description('创建订单');

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
            $grid->stock()->name('名称')->limit(10);
            $grid->price('售价')->display(function ($cost) {
                return "<span class='label label-info'>$cost</span>";
            });
            // $grid->created_at();
            $grid->ispay('生效')->value(function ($ispay) {
                return $ispay ?
                    "<i class='fa fa-check' style='color:green'></i>" :
                    "<i class='fa fa-close' style='color:red'></i>";
            });
            $grid->client()->name('客户名')->display(function ($v){
                return $v?'<a href="'.url('admin/client/'.$this->client['id'].'/salesrecord').'">'
                .$this->client['name'].'</a>':"";
            });
            $grid->client()->phone('联系方式');
            $grid->staff()->name('导购员')->value(function ($staffname){
                return mb_substr($staffname,0,1);
            });
            $grid->motor_serial_number('电机号')->value(function ($staffname){
                return mb_substr($staffname,-4);
            });
            $grid->frame_number('车架号')->value(function ($staffname){
                return mb_substr($staffname,-4);
            });
            $grid->bettery_type('电池型号');
            $grid->remarks('备注')->editable();

            $grid->updated_at('订单日期')->display(function($t){
                return date('Y-m-d', time($t));
            });
            $grid->filter(function($filter){
                //$filter->useModal();
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->where(function ($query) {

                    $input = $this->input;

                    $query->whereHas('Stock', function ($query) use ($input) {
                        $query->where('name', 'like', "%{$input}%");
                    });

                }, '车名称');
                $filter->is('id', '订单号');
                $filter->between('updated_at', '购买日期')->datetime();
            });
            $grid->model()->orderBy('created_at','desc');
            $grid->disableBatchDeletion();
            $grid->actions(function ($actions) {
            //   $actions->disableDelete();
              // $actions->disableEdit();
            });
            $grid->disableRowSelector();
            $grid->exporter(new SalesRecordExporter());
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
                    return [$item['id'] => $item['name'].' 规格:'.$item['type'].' 材质:'.$item['material'].' 目前零售价:'.$item['price']];
            }))->ajax('/admin/api/stock');
            $form->currency('price', '销售金额')->symbol('￥');
            $form->select('client_id','客户姓名')->options(
              Client::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name'].' 手机尾号: '.substr($item['phone'],-4)];
            }))->ajax('/admin/api/client');
            $form->select('staff_id','导购员')->options(
              Staff::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
              })
            );
            $states = [
                'on'  => ['value' => 1, 'text' => '生效', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '作废', 'color' => 'danger'],
            ];
            $form->switch('ispay', '订单生效')->states($states)->default(1);
            $form->text('motor_serial_number', '电机号');
            $form->text('frame_number', '车架号');
            $form->text('bettery_type', '电池型号');
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
     * Make a form builder.
     *
     * @return Form
     */
    protected function clientForm()
    {
        return Admin::form(Client::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name', '姓名')->rules('required|min:2');//姓名
            $states = [
                '男'  => ['value' => 1, 'text' => '男', 'color' => 'success'],
                '女' => ['value' => 2, 'text' => '女', 'color' => 'danger'],
            ];
            $form->radio('sex', '性别')->options(['1'=> '男','2' => '女'])->default('1');
            $form->mobile('phone', '手机');
            $form->date('birth', '生日');
            $form->text('address', '地址');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
    /**
     * API
     */
}
