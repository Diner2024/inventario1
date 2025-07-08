<?php
$ruta = explode("/", $_GET['views']);
if(!isset($ruta[1]) || $ruta[1]==""){
header("location: " .BASE_URL. "movimiento");
}
$curl = curl_init(); //inicia la sesión cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Movimiento.php?tipo=buscar_movimiento_id&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token'] . "&data=".$ruta[1], //url a la que se conecta
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

    $response = curl_exec($curl); // respuesta generada≤≤
    $err = curl_error($curl); // muestra errores en caso de existir

    curl_close($curl); // termina la sesión 

    if ($err) {
        echo "cURL Error #:" . $err; // mostramos el error
    } else {
        $respuesta =json_decode($response); 
        //print_r($respuesta);
        
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

       $contenido_pdf .= ' <!DOCTYPE html>
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
    }
    th, td {
      border: 1px solid black;
      text-align: center;
      padding: 6px;
    }
    .firma {
      margin-top: 80px;
      display: flex;
      justify-content: space-between;
      padding: 0 50px;
    }
    .firma div {
      text-align: center;
    }
    .fecha {
      margin-top: 30px;
      text-align: right;
    }
  </style>
</head>
<body>

  <h2>PAPELETA DE ROTACIÓN DE BIENES</h2>

  <div class="info">
    <div><b>ENTIDAD:</b> DIRECCIÓN REGIONAL DE EDUCACIÓN - AYACUCHO</div>
    <div><b>ÁREA:</b> OFICINA DE ADMINISTRACIÓN</div>
    <div><b>ORIGEN:</b> '. $respuesta->ambiente_origen->codigo."-".$respuesta->ambiente_origen->detalle .' </div>
    <div><b>DESTINO:</b> '. $respuesta->ambiente_destino->codigo."-".$respuesta->ambiente_destino->detalle.' </div>
    <div><b>MOTIVO(*):</b> '. $respuesta->movimiento->descripcion.' </div>
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

        // datos para la fecha
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
        ?>
    
       
        <?php
        $contador = 1;
        foreach ($respuesta->bien as $bienes) {
            $contenido_pdf .= '<tr>';
            $contenido_pdf .= "<td>" . $contador . "</td>";
            $contenido_pdf .= "<td>". $bienes->cod_patrimonial . "</td>";
            $contenido_pdf .= "<td>" . $bienes->denominacion . "</td>";
            $contenido_pdf .= "<td>". $bienes->marca . "</td>";
            $contenido_pdf .= "<td>" . $bienes->color. "</td>";
            $contenido_pdf .= "<td>". $bienes->modelo . "</td>";
            $contenido_pdf .= "<td>" . $bienes->estado_conservacion. "</td>";
            $contenido_pdf .= '</tr>';
             $contador ++;
        }

        $contenido_pdf.= ' </tbody>
  </table>

  <div class="fecha">
    Ayacucho, <?php echo $dia . " de " . $meses[$mesNumero] . " del " . $año?>
  </div>

  <div class="firma">
    <div>
      ------------------------------<br>
      ENTREGUÉ CONFORME
    </div>
    <div>
      ------------------------------<br>
      RECIBÍ CONFORME
    </div>
  </div>

</body>
</html>';
        ?>
   
  
        <?php
        require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
        $pdf = new TCPDF();

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('REPORTE DE MOVIMIENTOS');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //ASIGNAR SALTO DE PAGINA AUTO
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font TIPO DE FUENTE Y TAMAÑO
        $pdf->SetFont('helvetica', 'B', 12);

        // añadir pàgina
        $pdf->AddPage();


        // generar el contenido HTML
        $pdf->writeHTML($contenido_pdf, true, false, true, false, '');

ob_clean();
        //Cerrar y generar documento PDF
        $pdf->Output('sd', 'I');



        

    }

?>