<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Chart\Bar;
use Encore\Admin\Widgets\Chart\Doughnut;
use Encore\Admin\Widgets\Chart\Line;
use Encore\Admin\Widgets\Chart\Pie;
use Encore\Admin\Widgets\Chart\PolarArea;
use Encore\Admin\Widgets\Chart\Radar;
use Encore\Admin\Widgets\Collapse;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;

use App\Model\Stock;
use App\Model\SalesRecord;

use Carbon\Carbon;
class HomeController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('首页');
            $content->description('基本信息显示');


            $content->row(function ($row) {
                $costInventory = Stock::select('price','inventory')
                                 ->get();
                $amountCostInventory = 0;
                foreach ($costInventory as $v) {
                  $amountCostInventory += $v->price * $v->inventory;
                }
                $row->column(3, new InfoBox('库存金额', 'dollar', 'aqua', '/admin/stock', $amountCostInventory.'元'));
                $row->column(3, new InfoBox('月订单数', 'shopping-cart', 'yellow', '/admin/salerecord?per_page=50', SalesRecord::whereMonth('created_at', date('m',time()))->count().'单'));
                $row->column(3, new InfoBox('日订单数', 'shopping-cart', 'red', '/admin/salerecord?per_page=50', SalesRecord::whereDate('created_at', date('Y-m-d',time()))->count().'单'));
                $row->column(3, new InfoBox('日营销额', 'shopping-cart', 'green', '/admin/salerecord?per_page=50', SalesRecord::whereDate('created_at', date('Y-m-d',time()))->pluck('price')->sum().'元'));
                // $row->column(3, new InfoBox('Articles', 'book', 'yellow', '/admin/articles', '2786'));
                // $row->column(3, new InfoBox('Documents', 'file', 'red', '/admin/files', '698726'));
            });
            $content->row(function (Row $row) {
                //最近7天统计表
                $dt = Carbon::now()->subDay(29);
                for ($i = 0; $i < 30; $i++) {
                  $time[] = $dt->format('Y-m-d');
                  $dt->addDay();
                }
                foreach ($time as $value) {
                  $daysales[] = SalesRecord::whereDate('created_at', $value)->pluck('price')->sum();
                  $daysalesAmount[] = SalesRecord::whereDate('created_at', $value)->count();
                }
                $daysalesbar = new Bar(
                            array_slice($time,-7),
                            [
                                ['日订单数', array_slice($daysalesAmount,-7), '#dd4b39'],
                            ]
                        );
                $daysalesAmountbar = new Bar(
                            array_slice($time,-7),
                            [
                                ['日营销额', array_slice($daysales,-7), '#00a65a'],
                            ]
                        );
                $monthsalesbar = new Bar(
                            $time,
                            [
                                ['日订单数', $daysalesAmount, '#629819'],
                            ]
                        );
                $updatelist = <<<EOT
                    <ul>
                        <li>2017-07-10 更新：零售订单页面点击顾客姓名可以查看顾客的所有订单信息</li>
                    </ul>
EOT;

                $row->column(4,(new Box('更新日志', $updatelist))->style('primary')->solid());
                $row->column(4,(new Box('日订单数', $daysalesbar))->style('danger')->solid());
                $row->column(4,(new Box('日营销额', $daysalesAmountbar))->style('success')->solid());

                $row->column(12,(new Box('月营销额', $monthsalesbar))->style('success')->solid());
            });
            //
            // $content->row(function (Row $row) {
            //
            //     $row->column(6, function (Column $column) {
            //
            //         $tab = new Tab();
            //
            //         $pie = new Pie([
            //             ['Stracke Ltd', 450], ['Halvorson PLC', 650], ['Dicki-Braun', 250], ['Russel-Blanda', 300],
            //             ['Emmerich-O\'Keefe', 400], ['Bauch Inc', 200], ['Leannon and Sons', 250], ['Gibson LLC', 250],
            //         ]);
            //
            //         $tab->add('Pie', $pie);
            //         $tab->add('Table', new Table());
            //         $tab->add('Text', 'blablablabla....');
            //
            //         $tab->dropDown([['Orders', '/admin/orders'], ['administrators', '/admin/administrators']]);
            //         $tab->title('Tabs');
            //
            //         $column->append($tab);
            //
            //         $collapse = new Collapse();
            //
            //         $bar = new Bar(
            //             ["January", "February", "March", "April", "May", "June", "July"],
            //             [
            //                 ['First', [40,56,67,23,10,45,78]],
            //                 ['Second', [93,23,12,23,75,21,88]],
            //                 ['Third', [33,82,34,56,87,12,56]],
            //                 ['Forth', [34,25,67,12,48,91,16]],
            //             ]
            //         );
            //         $collapse->add('Bar', $bar);
            //         $collapse->add('Orders', new Table());
            //         $column->append($collapse);
            //
            //         $doughnut = new Doughnut([
            //             ['Chrome', 700],
            //             ['IE', 500],
            //             ['FireFox', 400],
            //             ['Safari', 600],
            //             ['Opera', 300],
            //             ['Navigator', 100],
            //         ]);
            //         $column->append((new Box('Doughnut', $doughnut))->removable()->collapsable()->style('info'));
            //     });
            //
            //     $row->column(6, function (Column $column) {
            //
            //         $column->append(new Box('Radar', new Radar()));
            //
            //         $polarArea = new PolarArea([
            //             ['Red', 300],
            //             ['Blue', 450],
            //             ['Green', 700],
            //             ['Yellow', 280],
            //             ['Black', 425],
            //             ['Gray', 1000],
            //         ]);
            //         $column->append((new Box('Polar Area', $polarArea))->removable()->collapsable());
            //
            //         $column->append((new Box('Line', new Line()))->removable()->collapsable()->style('danger'));
            //     });
            //
            // });
            //
            // $headers = ['Id', 'Email', 'Name', 'Company', 'Last Login', 'Status'];
            // $rows = [
            //     [1, 'labore21@yahoo.com', 'Ms. Clotilde Gibson', 'Goodwin-Watsica', '1997-08-13 13:59:21', 'open'],
            //     [2, 'omnis.in@hotmail.com', 'Allie Kuhic', 'Murphy, Koepp and Morar', '1988-07-19 03:19:08', 'blocked'],
            //     [3, 'quia65@hotmail.com', 'Prof. Drew Heller', 'Kihn LLC', '1978-06-19 11:12:57', 'blocked'],
            //     [4, 'xet@yahoo.com', 'William Koss', 'Becker-Raynor', '1988-09-07 23:57:45', 'open'],
            //     [5, 'ipsa.aut@gmail.com', 'Ms. Antonietta Kozey Jr.', 'Braun Ltd', '2013-10-16 10:00:01', 'open'],
            // ];
            //
            // $content->row((new Box('Table', new Table($headers, $rows)))->style('info')->solid());
        });
    }
}
