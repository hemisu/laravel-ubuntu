<?php

namespace App\Admin\Controllers;

use App\Model\Client;
use App\Model\SalesRecord;
use App\Model\Stock;
use App\Model\Staff;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

use App\Admin\Extensions\ClientExporter;

class ClientController extends Controller
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

            $content->header('客户管理');
            $content->description('客户总览');

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

            $content->header('客户管理');
            $content->description('客户资料编辑');

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

            $content->header('客户管理');
            $content->description('创建客户');

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
        return Admin::grid(Client::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('姓名')->display(function($v){
                return $v?'<a href="'.url('admin/client/'.$this->id.'/salesrecord').'">'
                .$v.'</a>':"";
            });
            $grid->phone('手机')->prependIcon('phone');
            $grid->salesrecord()->display(function($arr) {
                return serialize($value);
            });
            $grid->address('地址')->prependIcon('map-marker');
            $grid->model()->orderBy('created_at','desc');
            $grid->filter(function($filter){
                $filter->like('name', '姓名');
                $filter->like('phone', '手机');
            });
            $grid->exporter(new ClientExporter());
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Client::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name', '姓名')->rules('required|min:2');//姓名
            $states = [
                '男'  => ['value' => 1, 'text' => '男', 'color' => 'success'],
                '女' => ['value' => 2, 'text' => '女', 'color' => 'danger'],
            ];
            $form->radio('sex', '性别')->options(['1'=> '男','2' => '女'])->default('1');
            $form->text('phone', '手机');
            $form->date('birth', '生日');
            $form->text('address', '地址');
            $form->hasMany('salesrecord', function (Form\NestedForm $form) {
              $form->select('stock_id','车辆型号规格')->options(
                  Stock::all()->mapWithKeys(function ($item) {
                      return [$item['id'] => $item['name'].' 规格:'.$item['type'].' 材质:'.$item['material'].' 目前零售价:'.$item['price']];
              }))->ajax('/admin/api/stock');
              
              $form->currency('price', '销售金额')->symbol('￥');
              $form->select('staff_id','导购员')->options(
                Staff::all()->mapWithKeys(function ($item) {
                  return [$item['id'] => $item['name']];
                })
              );
              $states = [
                  'on'  => ['value' => true, 'text' => '生效', 'color' => 'success'],
                  'off' => ['value' => false, 'text' => '作废', 'color' => 'danger'],
              ];
              $form->switch('ispay', '订单生效')->states($states)->default(1);
              $form->text('motor_serial_number', '电机号');
              $form->text('frame_number', '车架号');
              $form->text('bettery_type', '电池型号');
              $form->textarea('remarks','订单备注')->rows(3);
              $form->display('updated_at', '订单日期');
            });
            $form->saving(function ($form) {

              foreach ($form->salesrecord as $sales) {
                if(Stock::find($sales['stock_id'])->inventory <= 0){
                  $error = new MessageBag([
                      'title'   => '错误',
                      'message' => Stock::find($sales['stock_id'])->name.'已售完',
                  ]);
                  return back()->with(compact('error'));
                }
              }
              //减库存
              foreach ($form->salesrecord as $sales) {
                Stock::find($sales['stock_id'])->decrement('inventory',1);
              }

            });

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
    /**
     * API
     */
    public function apiClient(Request $request)
    {
     $q = $request->get('q');

     return Client::where('name', 'like', "%$q%")
     ->paginate(null, ['id',DB::raw("concat(name,' 手机号:',phone) as text")]);
    }
    /**
     * clientSalesRecord interface.
     *
     * @return Content
     */
    public function clientSalesRecord($id){
        return Admin::content(function (Content $content) use ($id){

            $content->header('客户-订单');
            $content->description('客户名下订单信息');

            $content->row(function($row) use ($id){

            });
            $headers = ['客户名', '性别', '手机', '生日', '地址', '操作'
            ];

            $clientInfo = Client::where('id', '=', $id)->first();
            $rows = [
                [$clientInfo->name, $clientInfo->sex, $clientInfo->phone, $clientInfo->birth, $clientInfo->address,
            '<a href="'.url('admin/client/'.$clientInfo->id.'/edit').'"><i class="fa fa-edit"></i>修改客户资料</a>',],
            ];
            $content->row((new Box('客户信息', new Table($headers, $rows)))->style('primary')->solid());
            $stockRecordInfos = SalesRecord::where('client_id',$id)->get();
            foreach($stockRecordInfos as $stockRecordinfo){
                // echo $stockRecordinfo->stock_id;
                $stock = SalesRecord::find($stockRecordinfo->stock_id)->stock;
                $stockrows[] = [
                    $stockRecordinfo->id,
                    $stock->name,
                    $stockRecordinfo->price,
                    $stockRecordinfo->motor_serial_number,
                    $stockRecordinfo->frame_number,
                    $stockRecordinfo->bettery_type,
                    $stockRecordinfo->remarks,
                    '<a href="'.url('admin/salerecord/'.$stockRecordinfo->id.'/edit').'"><i class="fa fa-edit"></i>修改订单</a>',
                ];
            }
            // dd($stockInfos);
            $stockRecordHeaders = ['订单号', '名称', '售价', '电机号', '车架号', '电池型号', '备注', '操作'];
            $content->row((new Box('订单信息', new Table($stockRecordHeaders, $stockrows)))->style('primary')->solid());
        });
    }
}
