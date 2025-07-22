<?php 
$ruta = explode("/", $_GET['views']);

if (!isset($ruta[1]) || $ruta[1] == "") {
    header("location: " . BASE_URL. "movimientos");

}


require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {

public function Header() {
    $image_path_dre = __DIR__ . '/images/gobayacucho.jpg';
    $image_path_goba = __DIR__ . '/images/dreaya.jpg';

    // --- LOGO IZQUIERDO ---
    $this->Image($image_path_dre, 15, 8, 25, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    
    // --- TEXTOS DEL CENTRO ---
    $this->SetFont('helvetica', 'B', 10);
    $this->SetY(10);
    $this->Cell(0, 5, 'GOBIERNO REGIONAL DE AYACUCHO', 0, 1, 'C');
    
    $this->SetFont('helvetica', 'B', 12);
    $this->Cell(0, 5, 'DIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO', 0, 1, 'C');

    $this->SetFont('helvetica', '', 9);
    $this->Cell(0, 5, 'DIRECCION DE ADMINISTRACION', 0, 1, 'C');
    
    // --- DIBUJO DE LÍNEAS CON FUNCIONES NATIVAS---

    // Parámetros para las líneas
    $lineWidth = 140; 
    $pageWidth = $this->getPageWidth();
    $x = ($pageWidth - $lineWidth) / 2; // Calcula la posición X para centrar las líneas
    
    // Línea superior (delgada, más oscura)
    $y1 = 29; // Posición Y (distancia desde la parte superior de la página)
    $this->SetFillColor(41, 91, 162); // Color #295BA2 en RGB
    // Rect(x, y, ancho, alto, estilo) 'F' significa Relleno (Fill)
    $this->Rect($x, $y1, $lineWidth, 0.5, 'F'); 

    // Línea inferior (gruesa, más clara)
    $y2 = $y1 + 1.2; // Posición Y, un poco debajo de la primera línea
    $this->SetFillColor(51, 116, 194); // Color #3374C2 en RGB
    $this->Rect($x, $y2, $lineWidth, 1, 'F');
    
    // --- TEXTO "ANEXO - 4 -" ---
    // Lo dibujamos después de las líneas para que quede debajo
    $this->SetY($y2 + 3); // Posicionamos el cursor debajo de las líneas
    $this->SetFont('helvetica', 'B', 12);
    $this->Cell(0, 10, 'ANEXO - 4 -', 0, 1, 'C');

    // --- LOGO DERECHO ---
    // Dibujamos este logo al final para asegurarnos que esté en la capa superior si se solapa.
    $this->Image($image_path_goba, 170, 8, 25, 0, 'JPG', '', 'T', false, 300, 'R', false, false, 0, false, false, false);
}

public function Footer() {
    $this->SetY(-20);
    $this->SetX(120);
    $this->SetFont('helvetica', '', 8);
    $footer_html = '
    <table border="0" cellpadding="1" cellspacing="0" width="100%">
        <tr>
            <!-- Columna Izquierda: URL -->
            <td width="48%" align="center" valign="middle">
                <a href="http://www.dreaya.gob.pe" style="color:#0000ff; text-decoration:underline; font-size:10pt;">www.dreaya.gob.pe</a>
            </td>

            <!-- Columna Central: Línea vertical roja -->
            <!-- Truco: Usamos un div con un borde izquierdo dentro de una celda -->
            <td width="4%" align="center">
                <div style="border-left: 1px solid #C5232A; height: 15px;"> </div>
            </td>

            <!-- Columna Derecha: Información de contacto -->
            <!-- Usamos entidades HTML para los iconos de teléfono (☎) y fax () -->
            <td width="48%" align="left" valign="middle" style="font-size:8pt; line-height:1.4;">
                Jr. 28 de Julio N° 383 - Huamanga<br>
                ☎ (066) 31-2364<br>
                 (066) 31-1395 Anexo 55001
            </td>
        </tr>
    </table>
    ';
    $this->writeHTML($footer_html, true, false, true, false, '');
}
}


 $curl = curl_init(); //inicia la sesión cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Movimiento.php?tipo=buscar_movimiento_id&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token']."&data=". $ruta[1], //url a la que se conecta
        CURLOPT_RETURNTRANSFER => true, //devuelve el resultado como una cadena del tipo curl_exec
        CURLOPT_FOLLOWLOCATION => true, //sigue el encabezado que le envíe el servidor
        CURLOPT_ENCODING => "", // permite decodificar la respuesta y puede ser"identity", "deflate", y "gzip", si está vacío recibe todos los disponibles.
        CURLOPT_MAXREDIRS => 10, // Si usamos CURLOPT_FOLLOWLOCATION le dice el máximo de encabezados a seguir
        CURLOPT_TIMEOUT => 30, // Tiempo máximo para ejecutar
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // usa la versión declarada
        CURLOPT_CUSTOMREQUEST => "GET", // el tipo de petición, puede ser PUT, POST, GET o Delete dependiendo del servicio
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), //configura las cabeceras enviadas al servicio
    )); //curl_setopt_array configura las opciones para una transferencia cURL

    $response = curl_exec($curl); // respuesta generada
    $err = curl_error($curl); // muestra errores en caso de existir

    curl_close($curl); // termina la sesión 

    if ($err) {
        echo "cURL Error #:" . $err; // mostramos el error
    } else {
       $respuesta = json_decode($response);

               // datos para la fechas
        $new_Date = new DateTime();
        $dia = $new_Date->format('d');
        $año = $new_Date->format('Y');
        $mesNumero = (int)$new_Date->format('n'); 

        $meses = [
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Septiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre'
            ];

       $contenido_pdf = '';

       $contenido_pdf .= '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de Bienes</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
    }
    h2 {
      text-align: center;
      text-transform: uppercase;
    }
    .info {
      margin-bottom: 20px;
      line-height: 1.8;
    }
    .info b {
      display: inline-block;
      width: 80px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      font-size:9px;
    }
    th, td {
      border: 1px solid black;
      text-align: center;
      padding: 6px;
    }
    .fecha {
      margin-top: 30px;
      text-align: right;
    }

    .firma-section tr td{
       border: none;
      }

  </style>
</head>
<body>

  <h2>PAPELETA DE ROTACIÓN DE BIENES</h2>

  <div class="info">
    <div><b>ENTIDAD:</b> DIRECCIÓN REGIONAL DE EDUCACIÓN - AYACUCHO</div>
    <div><b>ÁREA:</b> OFICINA DE ADMINISTRACIÓN</div>
    <div><b>ORIGEN:</b> '.  $respuesta->ambiente_origen->codigo."-".$respuesta->ambiente_origen->detalle . '</div>
    <div><b>DESTINO:</b> '. $respuesta->ambiente_destino->codigo."-".$respuesta->ambiente_destino->detalle.'</div>
    <div><b>MOTIVO(*):</b> '. $respuesta->movimiento->descripcion.'</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>CÓDIGO PATRIMONIAL</th>
        <th>NOMBRE DEL BIEN</th>
        <th>MARCA</th>
        <th>COLOR</th>
        <th>MODELO</th>
        <th>ESTADO</th>
      </tr>
    </thead>
    <tbody>';
        
         $contador = 1;
        foreach ($respuesta->bien as $bienes) {
            $contenido_pdf .= '<tr>';
             $contenido_pdf .=  "<td>" . $contador . "</td>";
             $contenido_pdf .=  "<td>". $bienes->cod_patrimonial . "</td>";
             $contenido_pdf .= "<td>" . $bienes->denominacion . "</td>";
             $contenido_pdf .=  "<td>". $bienes->marca . "</td>";
             $contenido_pdf .=  "<td>" . $bienes->color. "</td>";
             $contenido_pdf .=  "<td>". $bienes->modelo . "</td>";
             $contenido_pdf .=  "<td>" . $bienes->estado_conservacion. "</td>";
             $contenido_pdf .=  '</tr>';
             $contador ++;
        }


 $contenido_pdf .='  </tbody>
  </table> 

  <div class="fecha">
    Ayacucho, '. $dia . " de " . $meses[$mesNumero] . " del " . $año.'
  </div>
<table  class="firma-section">
  <tr>
  <td>
    <div>
      ------------------------------<br>
      ENTREGUÉ CONFORME
    </div>
    </td>
    <td>
    <div>
      ------------------------------<br>
      RECIBÍ CONFORME
    </div>
    </td>
   </tr>
  </table>

</body>
</html>';

      
              
       

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('REPORTE DE MOVIMIENTOS');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 48, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //ASIGNAR SALTO DE PAGINA AUTO
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font TIPO DE FUENTE Y TAMAÑO
       

        // add a page
        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTML($contenido_pdf, true, false,true,false,'');

        //Close and output PDF document
        $pdf->Output('REPORTE_MOVIMIENTO.pdf', 'I');

        exit;

    }

?>