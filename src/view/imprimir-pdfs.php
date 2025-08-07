<?php 
$ruta = explode("/", $_GET['views']);

require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
class MYPDF extends TCPDF {
public function Header() {
    $image_path_dre =__DIR__.'/images/ayacucho.jpeg';
    $image_path_goba = __DIR__.'/images/drea.jpeg';
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


    // --- LOGO DERECHO ---
    $this->Image($image_path_goba, 170, 8, 25, 0, 'JPEG', '', 'T', false, 300, 'R', false, false, 0, false, false, false);
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

if ($ruta[1] == "imprInstituciones") {
    $curl = curl_init(); //inicia la sesión cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Institucion.php?tipo=listar&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token'],
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
        ), 
    )); 
    $response = curl_exec($curl); 
    $err = curl_error($curl); 
    curl_close($curl); 
    if ($err) {
        echo "cURL Error #:" . $err; 
    } else {
       $respuesta = json_decode($response);

       $instituciones = $respuesta->contenido;

               // datos para la fechas
        $new_Date = new DateTime();
        $dia = $new_Date->format('d');
        $año = $new_Date->format('Y');
        $mesNumero = (int)$new_Date->format('n'); 
        $meses = [1 => 'Enero',2 => 'Febrero',3 => 'Marzo', 4 => 'Abril',5 => 'Mayo', 6 => 'Junio', 7 => 'Julio',8 => 'Agosto',9 => 'Septiembre',10 => 'Octubre',11 => 'Noviembre', 12 => 'Diciembre'];

       $contenido_pdf = '';

       $contenido_pdf .= '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de instituciones</title>
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

  <h2>REPORTE DE INSTITUCIONES</h2>

  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>BENEFICIARIO</th>
        <th>CODIGO MODULAR</th>
        <th>RUC</th>
        <th>NOMBRE</th>
      </tr>
    </thead>
    <tbody>';    
         $contador = 1;
        foreach ($instituciones as $institucion) {
             $contenido_pdf .= '<tr>';
             $contenido_pdf .=  "<td>".  $contador . "</td>";
             $contenido_pdf .=  "<td>".  $institucion->beneficiario . "</td>";
             $contenido_pdf .= "<td>" .  $institucion->cod_modular . "</td>";
             $contenido_pdf .=  "<td>".  $institucion->ruc . "</td>";
             $contenido_pdf .=  "<td>".  $institucion->nombre. "</td>";
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
        $pdf->SetAuthor('Diner');
        $pdf->SetTitle('INSTITUCIONES');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 48, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //ASIGNAR SALTO DE PAGINA AUTO
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // add a page
        $pdf->AddPage();
        // output the HTML content
        $pdf->writeHTML($contenido_pdf, true, false,true,false,'');

        //Close and output PDF document
        $pdf->Output('REPORTE_INSTITUCIONES.pdf', 'I');

        exit;

    }


}
//imprimir ambientes
if($ruta[1] == "imprAmbientes"){
        $curl = curl_init(); 
        curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Ambiente.php?tipo=listarTodosAmbientes&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token'],
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_FOLLOWLOCATION => true, 
        CURLOPT_ENCODING => "", 
        CURLOPT_MAXREDIRS => 10, 
        CURLOPT_TIMEOUT => 30, 
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
        CURLOPT_CUSTOMREQUEST => "GET", 
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), 
    )); 
    $response = curl_exec($curl); 
    $err = curl_error($curl); 
    curl_close($curl); 
    if ($err) {
        echo "cURL Error #:" . $err; 
    } else {
       $respuesta = json_decode($response);

       $ambientes = $respuesta->contenido;

        $new_Date = new DateTime();
        $dia = $new_Date->format('d');
        $año = $new_Date->format('Y');
        $mesNumero = (int)$new_Date->format('n'); 
        $meses = [1 => 'Enero',2 => 'Febrero',3 => 'Marzo', 4 => 'Abril',5 => 'Mayo', 6 => 'Junio', 7 => 'Julio',8 => 'Agosto',9 => 'Septiembre',10 => 'Octubre',11 => 'Noviembre', 12 => 'Diciembre'];

       $contenido_pdf = '';

       $contenido_pdf .= '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de ambientes</title>
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

  <h2>REPORTE DE AMBIENTES</h2>

  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>INSTITUCION</th>
        <th>ENCARGADO</th>
        <th>CODIGO</th>
        <th>DETALLE</th>
        <th>OTROS DETALLES</th>
      </tr>
    </thead>
    <tbody>';    
         $contador = 1;
        foreach ($ambientes as $ambiente) {
             $contenido_pdf .= '<tr>';
             $contenido_pdf .=  "<td>".  $contador . "</td>";
             $contenido_pdf .=  "<td>".  $ambiente->institucion . "</td>";
             $contenido_pdf .= "<td>" .  $ambiente->encargado . "</td>";
             $contenido_pdf .=  "<td>".  $ambiente->codigo . "</td>";
             $contenido_pdf .=  "<td>".  $ambiente->detalle. "</td>";
             $contenido_pdf .=  "<td>".  $ambiente->otros_detalle. "</td>";
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
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Diner');
        $pdf->SetTitle('REPORTE DE AMBIENTES');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->SetMargins(PDF_MARGIN_LEFT, 48, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        $pdf->writeHTML($contenido_pdf, true, false,true,false,'');
        $pdf->Output('REPORTE_AMBIENTES.pdf', 'I');

        exit;

    }
}
//imprimir bienes
if($ruta[1] == "imprbienes"){
     $curl = curl_init(); 
        curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Bien.php?tipo=ObtenerBienes&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token'],
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_FOLLOWLOCATION => true, 
        CURLOPT_ENCODING => "", 
        CURLOPT_MAXREDIRS => 10, 
        CURLOPT_TIMEOUT => 30, 
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
        CURLOPT_CUSTOMREQUEST => "GET", 
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), 
    )); 
    $response = curl_exec($curl); 
    $err = curl_error($curl); 
    curl_close($curl); 
    if ($err) {
        echo "cURL Error #:" . $err; 
    } else {
       $respuest = json_decode($response);

       $bienes = $respuest->bienes;

        $new_Date = new DateTime();
        $dia = $new_Date->format('d');
        $año = $new_Date->format('Y');
        $mesNumero = (int)$new_Date->format('n'); 
        $meses = [1 => 'Enero',2 => 'Febrero',3 => 'Marzo', 4 => 'Abril',5 => 'Mayo', 6 => 'Junio', 7 => 'Julio',8 => 'Agosto',9 => 'Septiembre',10 => 'Octubre',11 => 'Noviembre', 12 => 'Diciembre'];

       $contenido_pdf = '';

       $contenido_pdf .= '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de ambientes</title>
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

  <h2>REPORTE DE BIENES</h2>

  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>INGRESO</th>
        <th>AMBIENTE</th>
        <th>COD PATRI</th>
        <th>DENOMINACION</th>
        <th>MARCA</th>
        <th>VALOR</th>
        <th>ESTADO</th>
        <th>FECHA REGISTRO</th>
        <th>USUARIO</th>
      </tr>
    </thead>
    <tbody>';    
         $contador = 1;
        foreach ($bienes as $bien) {
             $contenido_pdf .= '<tr>';
             $contenido_pdf .=  "<td>".  $contador . "</td>";
             $contenido_pdf .=  "<td>".  $bien->ingresonombre . "</td>";
             $contenido_pdf .= "<td>" .  $bien->ambiente . "</td>";
             $contenido_pdf .=  "<td>".  $bien->cod_patrimonial . "</td>";
             $contenido_pdf .=  "<td>".  $bien->denominacion. "</td>";
             $contenido_pdf .=  "<td>".  $bien->marca. "</td>";
             $contenido_pdf .= "<td>" .  $bien->valor . "</td>";
             $contenido_pdf .=  "<td>".  $bien->estado_conservacion . "</td>";
             $contenido_pdf .=  "<td>".  $bien->fecha_registro. "</td>";
             $contenido_pdf .=  "<td>".  $bien->usuarioregistro. "</td>";
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
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Diner');
        $pdf->SetTitle('BIENES');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->SetMargins(PDF_MARGIN_LEFT, 48, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        $pdf->writeHTML($contenido_pdf, true, false,true,false,'');
        $pdf->Output('REPORTE_BIENES.pdf', 'I');
        exit;

    }

}
//imprimir movimientos
if ($ruta[1] == "imprMovimientos") {
      $curl = curl_init(); 
        curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Movimiento.php?tipo=ListarMovimientos&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token'],
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_FOLLOWLOCATION => true, 
        CURLOPT_ENCODING => "", 
        CURLOPT_MAXREDIRS => 10, 
        CURLOPT_TIMEOUT => 30, 
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
        CURLOPT_CUSTOMREQUEST => "GET", 
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), 
    )); 
    $response = curl_exec($curl); 
    $err = curl_error($curl); 
    curl_close($curl); 
    if ($err) {
        echo "cURL Error #:" . $err; 
    } else {
       $respuest = json_decode($response);

       $movimientos = $respuest->movimientos;

        $new_Date = new DateTime();
        $dia = $new_Date->format('d');
        $año = $new_Date->format('Y');
        $mesNumero = (int)$new_Date->format('n'); 
        $meses = [1 => 'Enero',2 => 'Febrero',3 => 'Marzo', 4 => 'Abril',5 => 'Mayo', 6 => 'Junio', 7 => 'Julio',8 => 'Agosto',9 => 'Septiembre',10 => 'Octubre',11 => 'Noviembre', 12 => 'Diciembre'];

       $contenido_pdf = '';

       $contenido_pdf .= '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de ambientes</title>
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

  <h2>REPORTE DE MOVIMIENTOS</h2>

  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>AMBIENTE</th>
        <th>DESTINO</th>
        <th>USUARIO</th>
        <th>FECHA REGISTRO</th>
        <th>DESCRIPCION</th>
        <th>INSTITUCION</th>
      </tr>
    </thead>
    <tbody>';    
         $contador = 1;
        foreach ($movimientos as $movimiento) {
             $contenido_pdf .= '<tr>';
             $contenido_pdf .=  "<td>".  $contador . "</td>";
             $contenido_pdf .=  "<td>".  $movimiento->origenname . "</td>";
             $contenido_pdf .= "<td>" .  $movimiento->destinoname . "</td>";
             $contenido_pdf .=  "<td>".  $movimiento->usuarioname . "</td>";
             $contenido_pdf .=  "<td>".  $movimiento->fecha. "</td>";
             $contenido_pdf .=  "<td>".  $movimiento->descripcion. "</td>";
             $contenido_pdf .= "<td>" .  $movimiento->institucionname . "</td>";
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
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Diner');
        $pdf->SetTitle('REPORTE DE MOVIMIENTOS');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->SetMargins(PDF_MARGIN_LEFT, 48, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        $pdf->writeHTML($contenido_pdf, true, false,true,false,'');
        $pdf->Output('REPORTE_MOVIMIENTOS.pdf', 'I');
        exit;

    }
}

if ($ruta[1] == "imprUsuarios") {
      $curl = curl_init(); 
        curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Usuario.php?tipo=listarUsuarios&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token'],
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_FOLLOWLOCATION => true, 
        CURLOPT_ENCODING => "", 
        CURLOPT_MAXREDIRS => 10, 
        CURLOPT_TIMEOUT => 30, 
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
        CURLOPT_CUSTOMREQUEST => "GET", 
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), 
    )); 
    $response = curl_exec($curl); 
    $err = curl_error($curl); 
    curl_close($curl); 
    if ($err) {
        echo "cURL Error #:" . $err; 
    } else {
       $respuest = json_decode($response);

       $usuarios = $respuest->usuarios;

        $new_Date = new DateTime();
        $dia = $new_Date->format('d');
        $año = $new_Date->format('Y');
        $mesNumero = (int)$new_Date->format('n'); 
        $meses = [1 => 'Enero',2 => 'Febrero',3 => 'Marzo', 4 => 'Abril',5 => 'Mayo', 6 => 'Junio', 7 => 'Julio',8 => 'Agosto',9 => 'Septiembre',10 => 'Octubre',11 => 'Noviembre', 12 => 'Diciembre'];

       $contenido_pdf = '';

       $contenido_pdf .= '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de ambientes</title>
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

  <h2>REPORTE DE USUARIOS</h2>


  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>DNI</th>
        <th>NOMBRES Y APELLIDOS</th>
        <th>CORREO</th>
        <th>TELEFONO</th>
        <th>ESTADO</th>
        <th>FECHA REGISTRO</th>
      </tr>
    </thead>
    <tbody>';  

         $contador = 1;
        foreach ($usuarios as $usuario) {
               if ($usuario->estado = 1) {
        $usuario->estado = "activo";
     } elseif($usuario->estado = 0){
        $usuario->estado = "inactivo";
     }
             $contenido_pdf .= '<tr>';
             $contenido_pdf .=  "<td>".  $contador . "</td>";
             $contenido_pdf .=  "<td>".  $usuario->dni . "</td>";
             $contenido_pdf .= "<td>" .  $usuario->nombres_apellidos . "</td>";
             $contenido_pdf .=  "<td>".  $usuario->correo . "</td>";
             $contenido_pdf .=  "<td>".  $usuario->telefono. "</td>";
             $contenido_pdf .=  "<td>".  $usuario->estado. "</td>";
             $contenido_pdf .= "<td>" .  $usuario->fecha_registro . "</td>";
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
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(' Diner');
        $pdf->SetTitle('REPORTE DE USUARIOS');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->SetMargins(PDF_MARGIN_LEFT, 48, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        $pdf->writeHTML($contenido_pdf, true, false,true,false,'');
        $pdf->Output('REPORTE_USUARIOS.pdf', 'I');
        exit;

    }
}

?>