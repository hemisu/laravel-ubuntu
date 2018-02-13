<?php
namespace App\Admin\Extensions;
use Illuminate\Support\Arr;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Excel;
class SalesRecordExporter extends AbstractExporter
{
    public function export()
    {
        $filename = '零售表 导出日期'.date('Y-m-d',time());
        $data = $this->getData();
        // 这里获取数据
        //dd($data);
        //echo '<pre>';
        //var_dump($data);
        $index = 0;//序号
        foreach ($data as $row) {
            //var_dump($row);
            $cellData[] = ['index' => $index++, 
                    'stock' => $row['stock']['name'], 
                    'bettery_type' => $row['bettery_type'],
                    'clientname' => $row['client']['name'], 
                    'clientphone' => $row['client']['phone'],
                    'motor_serial_number' => $row['motor_serial_number'],
                    'frame_number' => $row['frame_number'],
                    'remarks' => $row['remarks'],
                    'created_at' => date('Y-m-d',strtotime($row['created_at']))
                   ];
        }
        // 导出文件，
        //var_dump($cellData);
        //die(0);
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
                    'C' => 8,
                    'D' => 8,
                    'E' => 12,
                    'F' => 12,
                    'G' => 12,
                    'H' => 12,
                    'I' => 12,
                    
                ]);;
                //首行标题
                $sheet->mergeCells('A1:I1')->cell('A1:I1', function($cell) {
                  $cell->setFontSize(20);
                  $cell->setAlignment('center');
                })->row(1, ["丰舆车业零售表 导出日期:".date('Y-m-d',time())]);;
                $sheet->appendRow(2, ['序号','车辆名称','电池','客户名','联系方式','电机号','车架号','备注','购买日期']);
                //填充数据
      			    $sheet->rows($cellData);
                //绘制边界
                $rownumber = count($cellData)+2;
                $sheet->setBorder("A1:I".$rownumber, 'thin');
      			});
    		})
         ->export('xls');
        exit;
    }
}
