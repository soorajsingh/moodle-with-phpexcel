<?php
require('../../../config.php');
require_once("$CFG->libdir/phpexcel/PHPExcel.php");
require_login();
global $CFG, $DB;

$query=required_param('query',PARAM_TEXT);

$records=$DB->get_records_sql($query); 	
	
// print_object($records);
// exit();



if(count($records)>0)
{
	
	$objPHPExcel = new PHPExcel;

	// set default font
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');

	// set default font size
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

	// create the writer
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

	// currency format, € with < 0 being in red color
	$currencyFormat = '#,#0.## \€;[Red]-#,#0.## \€';

	// number format, with thousands separator and two decimal points.
	$numberFormat = '#,#0.##;[Red]-#,#0.##';


	
	$a="A1:C2";
	$objPHPExcel->getActiveSheet()->getStyle($a)->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
				$objPHPExcel->getActiveSheet()->mergeCells($a);
				$objPHPExcel->getActiveSheet()->getCell('A1')->setValue('Group Report');
				$objPHPExcel->getActiveSheet()->getStyle($a)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle($a)->getFill()                                                               ->setFillType(PHPExcel_Style_Fill::FILL_SOLID) ->getStartColor()
				->setARGB('149dc6');
				
	$b="A3:C3";		
	$objPHPExcel->getActiveSheet()->getStyle($b)->getFont()->setBold(true)->setSize(11);
	$objPHPExcel->getActiveSheet()->setCellValue('A3','S.No');
	$style_for_a = "A";
	$objPHPExcel->getActiveSheet()->getStyle($style_for_a)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	// $objPHPExcel->getActiveSheet()->getStyle($a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID) ->getStartColor()->setARGB('c2d69b');

	$objPHPExcel->getActiveSheet()->setCellValue('B3','Group Id');
	$objPHPExcel->getActiveSheet()->setCellValue('C3','Group Name');

	
	
	$user_record = $DB->get_records_sql($query);

	
	
    if(count($records)>0)
	{
		$count = 1;
		$rowCount = 4;
		foreach($records as $record)
		{
			$column='A';
			$objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount,$count);
			$column++;	
			$objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount,$record->idnum);
			$column++;	
			$objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount,$record->name);
			$column++;		
			$rowCount++;
			$count++;
		}
	}
	$char='A';
	for($i=0; $i<26; $i++)
	{
		$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setAutoSize(true);
		$char++;
	}	
}	
$save_name='GroupReport.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename='.$save_name);
header('Cache-Control: max-age=0');
$objWriter->save('php://output');
			
?>