<?php

namespace App\Admin\Extensions;

use App\Model\Stock;

use Illuminate\Support\Arr;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Excel;

class ClientExporter extends AbstractExporter
{
    public function export()
    {
        $filename = '客户列表 '.date('Y-m',time());

        $data = $this->getData();
        // 这里获取数据

        // dd($data);
        // var_dump($data);
        $titles = ['name','sex','phone','birth','salesrecord','address'];
        $index = count($data);//序号
        foreach ($data as $row) {
            $row['sex'] = ($row['sex'] == 1)? '男' : '女';
            $row = ['index'=> $index--]+array_only($row, $titles);//筛选需要的列并且加上序号
            $temp = '';
            foreach ($row['salesrecord'] as $key=>$value){
                $stock = Stock::find($value['stock_id'])->name.'('.date('Y-m-d', strtotime($value['created_at'])).')';
                $temp .= $stock;
                if($key) $temp .= "|";
            }
            $row['salesrecord'] = $temp;
            $row['created_at'] = 
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
                    'B' => 12,
                    'C' => 6,
                    'D' => 15,
                    'E' => 15,
                    'F' => 25,
                    'G' => 25,
                    'H' => 20,
                ]);;
                //首行标题
                $sheet->mergeCells('A1:H1')->cell('A1:H1', function($cell) {
                  $cell->setFontSize(20);
                  $cell->setAlignment('center');
              })->row(1, ["丰舆车业客户列表 日期:".date('Y-m',time())]);;
                $sheet->appendRow(2, ['序号','姓名','性别','联系方式','生日','地址','购车','录入日期']);
                //填充数据
      			    $sheet->rows($cellData);
                //绘制边界
                $rownumber = count($cellData)+2;
                $sheet->setBorder("A1:H".$rownumber, 'thin');
      			});
    		})
         ->export('xls');
        exit;
    }
}
