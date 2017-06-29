<?php

namespace App\Admin\Controllers;

use App\Model\Client;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
            $grid->name('姓名');
            $grid->phone('手机')->prependIcon('phone');
            $grid->address('地址')->prependIcon('map-marker');
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
     public function apiClient(Request $request)
     {
         $q = $request->get('q');

         return Client::where('name', 'like', "%$q%")
         ->paginate(null, ['id',DB::raw("concat(name,' 手机号:',phone) as text")]);
     }
}
