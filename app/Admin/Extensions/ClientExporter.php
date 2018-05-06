<?php

namespace App\Admin\Extensions;

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
        $index = 0;//序号
        foreach ($data as $row) {
            $row['sex'] = ($row['sex'] == 1)? '男' : '女';
            $row = ['index'=> $index++]+array_only($row, $titles);//筛选需要的列并且加上序号
            $cellData[] = $row;
        }

        // 导出文件，
        var_dump($data);
    	// 	Excel::create($filename,function($excel) use ($cellData){
      	// 		$excel->sheet('score', function($sheet) use ($cellData){
        //         //设置格式
        //         $sheet->setStyle([
        //             'font' => [
        //                 'name' => 'SimSun',
        //                 'size' => 12,
        //             ]
        //         ])->setWidth([
        //             'A' => 6,
        //             'B' => 25,
        //             'C' => 6,
        //             'D' => 15,
        //             'E' => 15,
        //             'F' => 25,
        //             'G' => 25,
        //         ]);;
        //         //首行标题
        //         $sheet->mergeCells('A1:F1')->cell('A1:F1', function($cell) {
        //           $cell->setFontSize(20);
        //           $cell->setAlignment('center');
        //       })->row(1, ["丰舆车业客户列表 日期:".date('Y-m',time())]);;
        //         $sheet->appendRow(2, ['序号','姓名','性别','联系方式','生日','购车','地址']);
        //         //填充数据
      	// 		    $sheet->rows($cellData);
        //         //绘制边界
        //         $rownumber = count($cellData)+2;
        //         $sheet->setBorder("A1:F".$rownumber, 'thin');
      	// 		});
    	// 	})
        //  ->export('xls');
        exit;
    }
}
