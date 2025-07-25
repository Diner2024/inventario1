<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once('../model/admin-sesionModel.php');
require_once('../model/admin-usuarioModel.php');
require_once('../model/adminModel.php');
require  '../../vendor/autoload.php' ;

$tipo = $_GET['tipo'];

//instanciar la clase categoria model
$objSesion = new SessionModel();
$objUsuario = new UsuarioModel();
$objAdmin = new AdminModel();

//variables de sesion
$id_sesion = $_REQUEST['sesion'];
$token = $_REQUEST['token'];

//falta
if($tipo == "restablecer_password"){

        $arr_Respuesta = array('status' => false, 'msg' => 'Error al restablecer');

        if ($_POST) {
            $id = $_POST['id'];
            $NewPassword = $_POST['password'];
            $hashedPassword = password_hash($NewPassword, PASSWORD_DEFAULT);

            if ($id == "" || $NewPassword == "") {
                $arr_Respuesta = array('status' => false, 'mensaje' => 'Error, campos vacíos');
            } else {
                //validar si existe el usuario con ese id
                $arr_Usuario = $objUsuario->buscarUsuarioById($id);
                if ($arr_Usuario){
                    $operacion = $objUsuario->actualizarPassword($id, $hashedPassword);
                    if ($operacion) {
                        $tokenVacio = "";
                        $nuevoEstado = 0;
                        $operacion2 = $objUsuario->UpdateResetPassword($id, $tokenVacio, $nuevoEstado);
                        if (!$operacion2) {
                           $arr_Respuesta = array('status' => false, 'mensaje' => 'Error al limpiar token');
                        }
                        $arr_Respuesta = array('status' => true, 'mensaje' => 'Actualizado correctamente');
                    } else {
                        $arr_Respuesta = array('status' => false, 'mensaje' => 'Fallo al actualizar');
                    }
                    
                } else {
                   $arr_Respuesta = array('status' => false, 'mensaje' => 'Error, usuario no existe');
                }
            }
        }
         echo json_encode($arr_Respuesta);
}

if($tipo == "validar_datos_reset_password"){
   $id_email = $_POST['id'];
   $token_email = $_POST['token'];

   $arr_Respuesta = array('status'=> false, 'msg'=>'link caducado');
   $datos_usuario = $objUsuario->buscarUsuarioById($id_email);
   if($datos_usuario->reset_password == 1 && password_verify($datos_usuario->token_password, $token_email)){
     $arr_Respuesta = array('status'=> true, 'msg'=>'ok');
   }
   echo json_encode($arr_Respuesta);
}
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
            $contraseniahash = password_hash($dni, PASSWORD_DEFAULT);

            if ($dni == "" || $apellidos_nombres == "" || $correo == "" || $telefono == "") {
                //repuesta
                $arr_Respuesta = array('status' => false, 'mensaje' => 'Error, campos vacíos');
            } else {
                $arr_Usuario = $objUsuario->buscarUsuarioByDni($dni);
                if ($arr_Usuario) {
                    $arr_Respuesta = array('status' => false, 'mensaje' => 'Registro Fallido, Usuario ya se encuentra registrado');
                } else {
                    $id_usuario = $objUsuario->registrarUsuario($dni, $apellidos_nombres, $correo, $telefono,$contraseniahash);
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

if($tipo == "sent_email_password"){
     $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
            $datos_secion = $objSesion->buscarSesionLoginById($id_sesion);

              //obtenemos los datos del usuario mediante el id de la sesion
               $id_usuario = $datos_secion->id_usuario;
               $datos_usuario = $objUsuario->buscarUsuarioById($id_usuario);
               $correo_usuario = $datos_usuario->correo;
               $nombre_usuario = $datos_usuario->nombres_apellidos;
                
                $llave = $objAdmin->generar_llave(30);
                $token = password_hash($llave, PASSWORD_DEFAULT);
                $update = $objUsuario->UpdateResetPassword($id_usuario,$llave,1);
                if ($update) {
                            //Create an instance; passing `true` enables exceptions
                        //incluimos la plantilla de correo para el body email
                        ob_start();
                        include __DIR__ . '../../view/BodyEmail.php';
                        $emailBody = ob_get_clean();
                        
                        //php mailer
                        $mail = new PHPMailer(true);

                        try {
                            //Server settings
                            $mail->SMTPDebug = 2;                      //Enable verbose debug output
                            $mail->isSMTP();                                            //Send using SMTP
                            $mail->Host       = 'mail.limon-cito.com';                     //Set the SMTP server to send through
                            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                            $mail->Username   = 'sisve_jota@limon-cito.com';                     //SMTP username
                            $mail->Password   = 'jota123@@JOTA';                               //SMTP password
                            $mail->SMTPSecure = 'ssl';            //Enable implicit TLS encryption
                            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                            //Recipients
                            $mail->setFrom('sisve_jota@limon-cito.com', 'Support Sisve app');
                            $mail->addAddress($correo_usuario, $nombre_usuario);     //Add a recipient
                            //Name is optional



                            //Content
                            $mail->isHTML(true);                                  //Set email format to HTML
                            $mail->Subject = 'password reset request';

        /*                   $file = fopen("../view/BodyEmail.php","r");
                            $str = fread($file, filesize("../view/BodyEmail.php"));
                            $str = trim($str);
                            fclose($file); */

                            $mail->Body    = $emailBody;
                            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                            $mail->send();
                            echo 'Correo enviado con éxito.';
                        } catch (Exception $e) {
                            echo "Error al enviar: {$mail->ErrorInfo}";
                        }
                }else{
                    echo "fallo";
                }
    }
}
if($tipo == "listarUsuarios"){
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
   $arr_usuarios = $objUsuario->listarUsuarios();

   $arr_Respuesta['usuarios'] = $arr_usuarios;
   $arr_Respuesta['status'] = true;
   $arr_Respuesta['msg'] = 'correcto';
    }
    echo json_encode($arr_Respuesta);
}