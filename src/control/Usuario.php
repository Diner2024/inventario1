<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once('../model/admin-sesionModel.php');
require_once('../model/admin-usuarioModel.php');
require_once('../model/adminModel.php');
require '../../vendor/autoload.php';
$tipo = $_GET['tipo'];


//instanciar la clase categoria model
$objSesion = new SessionModel();
$objUsuario = new UsuarioModel();
$objAdmin = new AdminModel();

//variables de sesion
$id_sesion = $_POST['sesion'];
$token = $_POST['token'];

if ($tipo == "listar_usuarios_ordenados_tabla") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        $pagina = $_POST['pagina'];
        $cantidad_mostrar = $_POST['cantidad_mostrar'];
        $busqueda_tabla_dni = $_POST['busqueda_tabla_dni'];
        $busqueda_tabla_nomap = $_POST['busqueda_tabla_nomap'];
        $busqueda_tabla_estado = $_POST['busqueda_tabla_estado'];
        //repuesta
        $arr_Respuesta = array('status' => false, 'contenido' => '');
        $busqueda_filtro = $objUsuario->buscarUsuariosOrderByApellidosNombres_tabla_filtro($busqueda_tabla_dni, $busqueda_tabla_nomap, $busqueda_tabla_estado);
        $arr_Usuario = $objUsuario->buscarUsuariosOrderByApellidosNombres_tabla($pagina, $cantidad_mostrar, $busqueda_tabla_dni, $busqueda_tabla_nomap, $busqueda_tabla_estado);
        $arr_contenido = [];
        if (!empty($arr_Usuario)) {
            // recorremos el array para agregar las opciones de las categorias
            for ($i = 0; $i < count($arr_Usuario); $i++) {
                // definimos el elemento como objeto
                $arr_contenido[$i] = (object) [];
                // agregamos solo la informacion que se desea enviar a la vista
                $arr_contenido[$i]->id = $arr_Usuario[$i]->id;
                $arr_contenido[$i]->dni = $arr_Usuario[$i]->dni;
                $arr_contenido[$i]->nombres_apellidos = $arr_Usuario[$i]->nombres_apellidos;
                $arr_contenido[$i]->correo = $arr_Usuario[$i]->correo;
                $arr_contenido[$i]->telefono = $arr_Usuario[$i]->telefono;
                $arr_contenido[$i]->estado = $arr_Usuario[$i]->estado;
                $opciones = '<button type="button" title="Editar" class="btn btn-primary waves-effect waves-light" data-toggle="modal" data-target=".modal_editar' . $arr_Usuario[$i]->id . '"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-info" title="Resetear Contraseña" onclick="reset_password(' . $arr_Usuario[$i]->id . ')"><i class="fa fa-key"></i></button>';
                $arr_contenido[$i]->options = $opciones;
            }
            $arr_Respuesta['total'] = count($busqueda_filtro);
            $arr_Respuesta['status'] = true;
            $arr_Respuesta['contenido'] = $arr_contenido;
        }
    }
    echo json_encode($arr_Respuesta);
}
if ($tipo == "registrar") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        //repuesta
        if ($_POST) {
            $dni = $_POST['dni'];
            $apellidos_nombres = $_POST['apellidos_nombres'];
            $correo = $_POST['correo'];
            $telefono = $_POST['telefono'];
            $password = password_hash($dni, PASSWORD_DEFAULT);


            if ($dni == "" || $apellidos_nombres == "" || $correo == "" || $telefono == "") {
                //repuesta
                $arr_Respuesta = array('status' => false, 'mensaje' => 'Error, campos vacíos');
            } else {
                $arr_Usuario = $objUsuario->buscarUsuarioByDni($dni);
                if ($arr_Usuario) {
                    $arr_Respuesta = array('status' => false, 'mensaje' => 'Registro Fallido, Usuario ya se encuentra registrado');
                } else {
                    $id_usuario = $objUsuario->registrarUsuario($dni, $apellidos_nombres, $correo, $telefono, $password);
                    if ($id_usuario > 0) {
                        // array con los id de los sistemas al que tendra el acceso con su rol registrado
                        // caso de administrador y director
                        $arr_Respuesta = array('status' => true, 'mensaje' => 'Registro Exitoso');
                    } else {
                        $arr_Respuesta = array('status' => false, 'mensaje' => 'Error al registrar producto');
                    }
                }
            }
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "actualizar") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        //repuesta
        if ($_POST) {
            $id = $_POST['data'];
            $dni = $_POST['dni'];
            $nombres_apellidos = $_POST['nombres_apellidos'];
            $correo = $_POST['correo'];
            $telefono = $_POST['telefono'];
            $estado = $_POST['estado'];

            if ($id == "" || $dni == "" || $nombres_apellidos == "" || $correo == "" || $telefono == "" || $estado == "") {
                //repuesta
                $arr_Respuesta = array('status' => false, 'mensaje' => 'Error, campos vacíos');
            } else {
                $arr_Usuario = $objUsuario->buscarUsuarioByDni($dni);
                if ($arr_Usuario) {
                    if ($arr_Usuario->id == $id) {
                        $consulta = $objUsuario->actualizarUsuario($id, $dni, $nombres_apellidos, $correo, $telefono, $estado);
                        if ($consulta) {
                            $arr_Respuesta = array('status' => true, 'mensaje' => 'Actualizado Correctamente');
                        } else {
                            $arr_Respuesta = array('status' => false, 'mensaje' => 'Error al actualizar registro');
                        }
                    } else {
                        $arr_Respuesta = array('status' => false, 'mensaje' => 'dni ya esta registrado');
                    }
                } else {
                    $consulta = $objUsuario->actualizarUsuario($id, $dni, $nombres_apellidos, $correo, $telefono, $estado);
                    if ($consulta) {
                        $arr_Respuesta = array('status' => true, 'mensaje' => 'Actualizado Correctamente');
                    } else {
                        $arr_Respuesta = array('status' => false, 'mensaje' => 'Error al actualizar registro');
                    }
                }
            }
        }
    }
    echo json_encode($arr_Respuesta);
}
if ($tipo == "reiniciar_password") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        $id_usuario = $_POST['id'];
        $password = $objAdmin->generar_llave(10);
        $pass_secure = password_hash($password, PASSWORD_DEFAULT);
        $actualizar = $objUsuario->actualizarPassword($id_usuario, $pass_secure);
        if ($actualizar) {
            $arr_Respuesta = array('status' => true, 'mensaje' => 'Contraseña actualizado correctamente a: ' . $password);
        } else {
            $arr_Respuesta = array('status' => false, 'mensaje' => 'Hubo un problema al actualizar la contraseña, intente nuevamente');
        }
    }
    echo json_encode($arr_Respuesta);
}
if ($tipo == "send_email_password") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {

        $datos_sesion = $objSesion->buscarSesionLoginById($id_sesion);
        $datos_usuario = $objUsuario->buscarUsuarioById($datos_sesion->id_usuario);
        $llave = $objAdmin->generar_llave(30);
        $token = password_hash($llave, PASSWORD_DEFAULT);
        $update = $objUsuario->updateResetPassword($datos_sesion->id_usuario, $llave, 1);
        if ($update){
            
            //Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function


//Load Composer's autoloader (created by composer, not included with PHPMailer)


//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'mail.limon-cito.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'inventario_diner@limon-cito.com';                     //SMTP username
    $mail->Password   = 'diner@2025';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('inventario_diner@limon-cito.com', 'Cambio la contrasena');
    $mail->addAddress($datos_usuario->correo, $datos_usuario->nombres_apellidos);     //Add a recipient
    /*$mail->addAddress('ellen@example.com');               //Name is optional
    $mail->addReplyTo('info@example.com', 'Information');
    $mail->addCC('cc@example.com');
    $mail->addBCC('bcc@example.com');

    //Attachments
    $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name*/

    //Content
    $mail->isHTML(true); 
    $mail->CharSet='UTF-8';                                 //Set email format to HTML
    $mail->Subject = 'cambio de contraseña-Sistema de Inventario';
    $mail->Body    = '
    <!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elite Shoes - Calzados de Lujo</title>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@400;600;800&family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 20px;
      background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #f9ca24, #ff6b6b);
      background-size: 400% 400%;
      animation: gradientShift 6s ease infinite;
      font-family:'.'Open Sans'.', sans-serif;
      min-height: 100vh;
    }
    
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    .container {
      max-width: 650px;
      margin: auto;
      background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
      font-family: '.'Open Sans'.', sans-serif;
      color: #2c3e50;
      border: 4px solid transparent;
      border-radius: 25px;
      box-shadow: 0 30px 60px rgba(0,0,0,0.2);
      overflow: hidden;
      position: relative;
      animation: borderRainbow 3s linear infinite;
    }
    
    @keyframes borderRainbow {
      0% {
        border-color: #ff0000;
        box-shadow: 0 30px 60px rgba(255,0,0,0.3), 0 0 40px rgba(255,0,0,0.5);
      }
      16.66% {
        border-color: #ff8000;
        box-shadow: 0 30px 60px rgba(255,128,0,0.3), 0 0 40px rgba(255,128,0,0.5);
      }
      33.33% {
        border-color: #ffff00;
        box-shadow: 0 30px 60px rgba(255,255,0,0.3), 0 0 40px rgba(255,255,0,0.5);
      }
      50% {
        border-color: #00ff00;
        box-shadow: 0 30px 60px rgba(0,255,0,0.3), 0 0 40px rgba(0,255,0,0.5);
      }
      66.66% {
        border-color: #0080ff;
        box-shadow: 0 30px 60px rgba(0,128,255,0.3), 0 0 40px rgba(0,128,255,0.5);
      }
      83.33% {
        border-color: #8000ff;
        box-shadow: 0 30px 60px rgba(128,0,255,0.3), 0 0 40px rgba(128,0,255,0.5);
      }
      100% {
        border-color: #ff0000;
        box-shadow: 0 30px 60px rgba(255,0,0,0.3), 0 0 40px rgba(255,0,0,0.5);
      }
    }
    
    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 45px 20px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .header::before {
      content: '.';
      position: absolute;
      top: -100%;
      left: -100%;
      width: 300%;
      height: 300%;
      background: conic-gradient(transparent, rgba(255,255,255,0.3), transparent);
      animation: spin 4s linear infinite;
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .header h2 {
      font-family: '.'Dancing Script'.', cursive;
      font-size: 36px;
      font-weight: 700;
      margin: 0;
      text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
      position: relative;
      z-index: 2;
      animation: textPulse 2s ease-in-out infinite alternate;
    }
    
    @keyframes textPulse {
      from { 
        text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
        transform: scale(1);
      }
      to { 
        text-shadow: 0 0 20px rgba(255,255,255,0.8), 0 0 40px rgba(255,255,255,0.6);
        transform: scale(1.05);
      }
    }
    
    .content {
      padding: 40px 35px;
      background: white;
      position: relative;
    }
    
    .content::before {
      content: '.';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 8px;
      background: linear-gradient(90deg, #ff0000, #ff8000, #ffff00, #00ff00, #0080ff, #8000ff, #ff0000);
      background-size: 400% 100%;
      animation: rainbowSlide 2s linear infinite;
    }
    
    @keyframes rainbowSlide {
      0% { background-position: 0% 0%; }
      100% { background-position: 400% 0%; }
    }
    
    .content h1 {
      font-family: '.'Merriweather'.', serif;
      font-size: 30px;
      font-weight: 700;
      margin-bottom: 25px;
      color: #2c3e50;
      text-align: center;
      animation: slideInDown 1s ease-out;
    }
    
    @keyframes slideInDown {
      from { opacity: 0; transform: translateY(-50px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .content p {
      font-size: 18px;
      line-height: 1.8;
      color: #34495e;
      margin-bottom: 20px;
      font-weight: 400;
      text-align: justify;
    }
    
    .promo-box {
      background: linear-gradient(135deg, #ff6b6b 0%, #4ecdc4 100%);
      color: white;
      padding: 30px;
      border-radius: 20px;
      margin: 30px 0;
      text-align: center;
      box-shadow: 0 20px 40px rgba(255,107,107,0.4);
      animation: floatUpDown 3s ease-in-out infinite;
      position: relative;
      overflow: hidden;
    }
    
    @keyframes floatUpDown {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    
    .promo-box::before {
      content: '.';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      animation: rotate 6s linear infinite;
    }
    
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .promo-box h3 {
      margin: 0 0 15px 0;
      font-family: '.'Merriweather'.', serif;
      font-weight: 700;
      font-size: 24px;
      position: relative;
      z-index: 2;
    }
    
    .promo-box p {
      position: relative;
      z-index: 2;
      margin: 0;
      color: white;
      text-align: center;
    }
    
    .button {
      display: inline-block;
      background: linear-gradient(45deg, #667eea, #764ba2);
      color: #ffffff !important;
      padding: 20px 40px;
      margin: 25px 10px;
      text-decoration: none;
      border-radius: 50px;
      font-weight: 800;
      font-family: '.'Open Sans'.', sans-serif;
      font-size: 16px;
      box-shadow: 0 20px 40px rgba(102,126,234,0.4);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      animation: buttonFlash 3s ease-in-out infinite;
    }
    
    @keyframes buttonFlash {
      0%, 100% { 
        box-shadow: 0 20px 40px rgba(102,126,234,0.4);
        transform: scale(1);
      }
      50% { 
        box-shadow: 0 25px 50px rgba(102,126,234,0.6), 0 0 30px rgba(102,126,234,0.5);
        transform: scale(1.05);
      }
    }
    
    .button:hover {
      transform: translateY(-5px) scale(1.1);
      box-shadow: 0 30px 60px rgba(102,126,234,0.6);
    }
    
    .button::before {
      content: '.';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.5s;
    }
    
    .button:hover::before {
      left: 100%;
    }
    
    .footer {
      background: linear-gradient(135deg, #2c3e50, #34495e);
      text-align: center;
      padding: 30px;
      font-size: 14px;
      color: #ecf0f1;
      font-weight: 400;
    }
    
    .footer a {
      color: #f9ca24;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .footer a:hover {
      color: #ff6b6b;
      text-decoration: underline;
      text-shadow: 0 0 10px rgba(255,107,107,0.5);
    }
    
    .shoe-emoji {
      font-size: 28px;
      animation: bounce 2s ease-in-out infinite;
      display: inline-block;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-15px); }
      60% { transform: translateY(-8px); }
    }
    
    .sparkle {
      animation: sparkle 1.5s ease-in-out infinite alternate;
    }
    
    @keyframes sparkle {
      from { opacity: 0.5; transform: scale(1); }
      to { opacity: 1; transform: scale(1.2); }
    }
    
    @media screen and (max-width: 600px) {
      body {
        padding: 10px;
      }
      
      .content, .header, .footer {
        padding: 25px 20px !important;
      }
      
      .button {
        padding: 15px 30px !important;
        font-size: 14px;
        display: block;
        margin: 15px 0;
        text-align: center;
      }
      
      .header h2 {
        font-size: 28px;
      }
      
      .content h1 {
        font-size: 24px;
      }
      
      .content p {
        font-size: 16px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h2><span class="shoe-emoji">👠</span> ELITE SHOES BOUTIQUE <span class="shoe-emoji">👟</span></h2>
    </div>
    
    <div class="content">
      <h1>¡Hola [GARCIA CONDORI DINER]! <span class="sparkle">✨</span></h1>
      
      <p>
        Te saludamos cordialmente desde <strong>Elite Shoes Boutique</strong>, tu destino exclusivo para 
        el calzado más elegante y sofisticado. Queremos informarte sobre nuestras últimas 
        <strong>colecciones de temporada</strong> y promociones especiales que hemos preparado especialmente para ti.
      </p>
      
      <div class="promo-box">
        <h3><span class="sparkle">🔥</span> ¡OFERTAS IMPERDIBLES! <span class="sparkle">🔥</span></h3>
        <p>¡No te pierdas nuestras ofertas especiales por tiempo limitado! Descuentos de hasta el 50% en zapatos de las mejores marcas internacionales.</p>
      </div>
      
      <p>
        Descubre nuestra exclusiva selección de <strong>calzado premium</strong>: desde sofisticados tacones 
        de diseñador hasta cómodas zapatillas de lujo. Cada par cuenta una historia de elegancia y estilo.
      </p>
      
      <div style="text-align: center; margin: 35px 0;">
        <a href="https://www.eliteshoesboutique.com/promociones" class="button">
          <span class="sparkle">👠</span> Ver Ofertas Exclusivas
        </a>
        <a href="https://www.eliteshoesboutique.com/nuevas-colecciones" class="button">
          <span class="sparkle">✨</span> Nuevas Colecciones
        </a>
      </div>
      
      <p style="text-align: center; font-style: italic; color: #7f8c8d; font-size: 18px; font-family: '.'Dancing Script'.', cursive;">
        Gracias por elegirnos como tu calzado de confianza. <br>
        <strong>¡Camina con elegancia, camina con Elite Shoes!</strong> <span class="sparkle">👠✨</span>
      </p>
    </div>
    
    <div class="footer">
      © 2025 Elite Shoes Boutique. Todos los derechos reservados.<br><br>
      <a href="https://www.eliteshoesboutique.com/desuscribirse">Cancelar suscripción</a> | 
      <a href="https://www.eliteshoesboutique.com/contacto">Contacto</a> | 
      <a href="https://www.eliteshoesboutique.com/tiendas">Nuestras Boutiques</a>
    </div>
  </div>
</body>
</html>
    ';
   

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
    }else{ 
        echo "fallo la actualizacion";
    }
   // print_r($token);

    }
    
}