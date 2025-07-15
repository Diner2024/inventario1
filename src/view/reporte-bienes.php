<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()->setCreator("yp")->setLastModifiedBy("yo")->setDescription("yo");
$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setTitle("hoja 1");
$activeWorksheet->setCellValue('A1', 'HOLA MUNDO !');
$activeWorksheet->setCellValue('A2', 'DNI');
$activeWorksheet->setCellValue('B2', '70872769');
for ($i=1; $i < 10; $i++) { 
    $activeWorksheet->setCellValue('A'.$i, $i);
}
for ($i=1; $i <= 30; $i++) {
    $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
    $activeWorksheet->setCellValue($columna. '1', $i);
}

$row = 1; // Contador global de filas

// Generar 5 tablas de multiplicar (del 1 al 5)
for ($table = 1; $table <= 12; $table++) {
    // Agregar título de la tabla
    $activeWorksheet->setCellValue('A'.$row, 'Tabla del '.$table);
    $activeWorksheet->mergeCells('A'.$row.':E'.$row);
    $row++;
    
    // Generar cada fila de la tabla de multiplicar
    for ($n2 = 1; $n2 <= 12; $n2++) {
        $activeWorksheet->setCellValue('A'.$row, $table);
        $activeWorksheet->setCellValue('B'.$row, 'X');
        $activeWorksheet->setCellValue('C'.$row, $n2);
        $activeWorksheet->setCellValue('D'.$row, '=');
        $activeWorksheet->setCellValue('E'.$row, $table * $n2);
        $row++;
    }
    
    // Agregar una fila vacía entre tablas (excepto después de la última)
    if ($table < 12) {
        $row++;
    }
}


// Descargar sin guardar en servidor
$writer = new Xlsx($spreadsheet);
$writer->save("mi eexcel.xlsx");
exit;