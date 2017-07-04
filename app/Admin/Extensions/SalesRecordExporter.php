<?php
namespace App\Admin\Extensions;
use Illuminate\Support\Arr;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Excel;
class SalesRecordExporter extends AbstractExporter
{
    public function export()
    {
        $filename = '零售表 '.date('Y-m',time());
        $data = $this->getData();
        // 这里获取数据
        //dd($data);
         var_dump($data);
        die(0);
        $titles = ['name','type','image','price','inventory'];
        $index = 0;//序号
        foreach ($data as $row) {
            $row = ['index'=> $index++]+array_only($row, $titles);//筛选需要的列并且加上序号
            $cellData[] = $row;
        }
        // 导出文件，
        // var_dump($cellData);
    		Excel::create($filename,function($excel) use ($cellData){
      			$excel->sheet('score', function($sheet) use ($cellData){
                //设置格式
                $sheet->setStyle([
                    'font' => [
                        'name' => 'SimSun',
                        'size' => 12,
                    ]
                ])->setWidth([
                    'A' => 6,
                    'B' => 25,
                    'C' => 20,
                    'D' => 15,
                    'E' => 12,
                    'F' => 5,
                ]);;
                //首行标题
                $sheet->mergeCells('A1:F1')->cell('A1:F1', function($cell) {
                  $cell->setFontSize(20);
                  $cell->setAlignment('center');
                })->row(1, ["丰舆车业零售表 日期:".date('Y-m',time())]);;
                $sheet->appendRow(2, ['序号','车辆名称','规格型号','客户名','联系方式','电机号','车架号','电池','备注','购买日期']);
                //填充数据
      			    $sheet->rows($cellData);
                //绘制边界
                $rownumber = count($cellData)+2;
                $sheet->setBorder("A1:F".$rownumber, 'thin');
      			});
    		})
         ->export('xls');
        exit;
    }
}
