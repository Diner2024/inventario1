// Mostrar el popup de carga
function mostrarPopupCarga() {
    const popup = document.getElementById('popup-carga');
    if (popup) {
        popup.style.display = 'flex';
    }
}
// Ocultar el popup de carga
function ocultarPopupCarga() {
    const popup = document.getElementById('popup-carga');
    if (popup) {
        popup.style.display = 'none';
    }
}
//funcion en caso de session acudacada
async function alerta_sesion() {
    Swal.fire({
        type: 'error',
        title: 'Error de Sesión',
        text: "Sesión Caducada, Por favor inicie sesión",
        confirmButtonClass: 'btn btn-confirm mt-2',
        footer: '',
        timer: 1000
    });
    location.replace(base_url + "login");
}
// cargar elementos de menu
async function cargar_institucion_menu(id_ies = 0) {
    const formData = new FormData();
    formData.append('sesion', session_session);
    formData.append('token', token_token);
    try {
        let respuesta = await fetch(base_url_server + 'src/control/Institucion.php?tipo=listar', {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            body: formData
        });
        let json = await respuesta.json();
        if (json.status) {
            let datos = json.contenido;
            let contenido = '';
            let sede = '';
            datos.forEach(item => {
                if (id_ies == item.id) {
                    sede = item.nombre;
                }
                contenido += `<button href="javascript:void(0);" class="dropdown-item notify-item" onclick="actualizar_ies_menu(${item.id});">${item.nombre}</button>`;
            });
            document.getElementById('contenido_menu_ies').innerHTML = contenido;
            document.getElementById('menu_ies').innerHTML = sede;
        }
        //console.log(respuesta);
    } catch (e) {
        console.log("Error al cargar categorias" + e);
    }

}
async function cargar_datos_menu(sede) {
    cargar_institucion_menu(sede);
}
// actualizar elementos del menu
async function actualizar_ies_menu(id) {
    const formData = new FormData();
    formData.append('id_ies', id);
    try {
        let respuesta = await fetch(base_url + 'src/control/sesion_cliente.php?tipo=actualizar_ies_sesion', {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            body: formData
        });
        let json = await respuesta.json();
        if (json.status) {
            location.reload();
        }
        //console.log(respuesta);
    } catch (e) {
        console.log("Error al cargar instituciones" + e);
    }
}
function generar_paginacion(total, cantidad_mostrar) {
    let actual = document.getElementById('pagina').value;
    let paginas = Math.ceil(total / cantidad_mostrar);
    let paginacion = '<li class="page-item';
    if (actual == 1) {
        paginacion += ' disabled';
    }
    paginacion += ' "><button class="page-link waves-effect" onclick="numero_pagina(1);">Inicio</button></li>';
    paginacion += '<li class="page-item ';
    if (actual == 1) {
        paginacion += ' disabled';
    }
    paginacion += '"><button class="page-link waves-effect" onclick="numero_pagina(' + (actual - 1) + ');">Anterior</button></li>';
    if (actual > 4) {
        var iin = (actual - 2);
    } else {
        var iin = 1;
    }
    for (let index = iin; index <= paginas; index++) {
        if ((paginas - 7) > index) {
            var n_n = iin + 5;
        }
        if (index == n_n) {
            var nn = actual + 1;
            paginacion += '<li class="page-item"><button class="page-link" onclick="numero_pagina(' + nn + ')">...</button></li>';
            index = paginas - 2;
        }
        paginacion += '<li class="page-item ';
        if (actual == index) {
            paginacion += "active";
        }
        paginacion += '" ><button class="page-link" onclick="numero_pagina(' + index + ');">' + index + '</button></li>';
    }
    paginacion += '<li class="page-item ';
    if (actual >= paginas) {
        paginacion += "disabled";
    }
    paginacion += '"><button class="page-link" onclick="numero_pagina(' + (parseInt(actual) + 1) + ');">Siguiente</button></li>';

    paginacion += '<li class="page-item ';
    if (actual >= paginas) {
        paginacion += "disabled";
    }
    paginacion += '"><button class="page-link" onclick="numero_pagina(' + paginas + ');">Final</button></li>';
    return paginacion;
}
function generar_texto_paginacion(total, cantidad_mostrar) {
    let actual = document.getElementById('pagina').value;
    let paginas = Math.ceil(total / cantidad_mostrar);
    let iniciar = (actual - 1) * cantidad_mostrar;
    if (actual < paginas) {

        var texto = '<label>Mostrando del ' + (parseInt(iniciar) + 1) + ' al ' + ((parseInt(iniciar) + 1) + 9) + ' de un total de ' + total + ' registros</label>';
    } else {
        var texto = '<label>Mostrando del ' + (parseInt(iniciar) + 1) + ' al ' + total + ' de un total de ' + total + ' registros</label>';
    }
    return texto;
}
// ---------------------------------------------  DATOS DE CARGA PARA FILTRO DE BUSQUEDA -----------------------------------------------
//cargar programas de estudio
function cargar_ambientes_filtro(datos, form = 'busqueda_tabla_ambiente', filtro = 'filtro_ambiente') {
    let ambiente_actual = document.getElementById(filtro).value;
    lista_ambiente = `<option value="0">TODOS</option>`;
    datos.forEach(ambiente => {
        pe_selected = "";
        if (ambiente.id == ambiente_actual) {
            pe_selected = "selected";
        }
        lista_ambiente += `<option value="${ambiente.id}" ${pe_selected}>${ambiente.detalle}</option>`;
    });
    document.getElementById(form).innerHTML = lista_ambiente;
}
//cargar programas de estudio
function cargar_sede_filtro(sedes) {
    let sede_actual = document.getElementById('sede_actual_filtro').value;
    lista_sede = `<option value="0">TODOS</option>`;
    sedes.forEach(sede => {
        sede_selected = "";
        if (sede.id == sede_actual) {
            sede_selected = "selected";
        }
        lista_sede += `<option value="${sede.id}" ${sede_selected}>${sede.nombre}</option>`;
    });
    document.getElementById('busqueda_tabla_sede').innerHTML = lista_sede;
}



// ------------------------------------------- FIN DE DATOS DE CARGA PARA FILTRO DE BUSQUEDA -----------------------------------------------

async function validar_datos_reset_password(){
    let id = document.getElementById('data').value;
    let token = document.getElementById('data2').value;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('token', token);
    formData.append('sesion', '');

    try {
        let respuesta = await fetch(base_url_server + 'src/control/Usuario.php?tipo=validar_datos_reset_password', {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            body: formData
        });
        let json = await respuesta.json();
        if (json.status == false) {
        Swal.fire({
        type: 'error',
        title: 'Error de Sesión',
        text: "Link Caducada, Verefique su correo",
        confirmButtonClass: 'btn btn-confirm mt-2',
        footer: '',
        timer: 1000
    });
    let formulario = document.getElementById('contentn_reset');
    formulario.innerHTML = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Enlace Caducado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    html {
      background-color: #000;
    }

    body {
      background-color: #000;
      color: #00ff00;
      font-family: 'Courier New', Courier, monospace;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      overflow: hidden;
    }

    .container {
      text-align: center;
      animation: flicker 2s infinite;
    }

    h1 {
      font-size: 2em;
      text-shadow: 0 0 5px #0f0, 0 0 10px #0f0;
    }

    .link {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      border: 2px solid #0f0;
      color: #0f0;
      text-decoration: none;
      font-weight: bold;
      font-size: 1.2em;
      transition: all 0.3s ease;
      box-shadow: 0 0 10px #0f0, 0 0 20px #0f0;
    }

    .link:hover {
      background-color: #0f0;
      color: #000;
      box-shadow: 0 0 20px #0f0, 0 0 40px #0f0;
    }

    @keyframes flicker {
      0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
        opacity: 1;
      }
      20%, 24%, 55% {
        opacity: 0.4;
      }
    }

    .glitch {
      position: relative;
      color: #00ffcc;
      animation: glitch 1.5s infinite;
    }

    @keyframes glitch {
      0% { transform: translate(0); }
      10% { transform: translate(-2px, 2px); }
      20% { transform: translate(2px, -2px); }
      30% { transform: translate(-2px, -2px); }
      40% { transform: translate(2px, 2px); }
      50% { transform: translate(0); }
      60% { transform: translate(2px, 0); }
      70% { transform: translate(-2px, 0); }
      80% { transform: translate(0); }
      90% { transform: translate(2px, 2px); }
      100% { transform: translate(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="glitch">⚠️ ENLACE CADUCADO ⚠️</h1>
    <p>Este enlace ha sido bloqueado por motivos de seguridad.</p>
    <a href="https://www.hackthissite.org/" class="link">Acceso No Autorizado</a>
  </div>
</body>
</html>
`;
    //location.replace(base_url + "login");

        }
    }catch (e) {
        console.log("Error al validar datos" + e);
    }

}

function validar_imputs_password(){
    let pass1 = document.getElementById('cont').value;
    let pass2 = document.getElementById('cont2').value;
    if (pass1 !== pass2){
        Swal.fire({
        type: 'error',
        title: 'Error',
        text: "Contraseña no Coinciden ",
        footer: '',
        timer: 1500
    });
    return;
    }
    if (pass1.length<8 && pass2.length<8){
         Swal.fire({
        type: 'error',
        title: 'Error',
        text: "La Contraseña tiene que ser mínimo 8 caracteres",
        footer: '',
        timer: 1500
    });
    return;
    }else{
       actualizar_password(); 
    }
}

async function actualizar_password() {
    
    let id = document.getElementById('data').value;
    let contrasenia = document.getElementById('cont').value;

    const formdata = new FormData();
    formdata.append('id',id);
    formdata.append('constrasenia',contrasenia);
    formdata.append('sesion','');
    formdata.append('token','');
   try {
    let respuesta = await fetch(base_url+'src/control/Usuario.php?tipo=cambiarContrasenia',{
        method: 'POST',
        mode:'cors',
        cache: 'no-cache',
        body: formdata
    });
    let json = await respuesta.json();
    if (json) {
        Swal.fire({
         type:'success',
         title:'Actualizado',
         text:'CONTRASEÑA ACTUALIZADA',
         footer: '',
         timer: 3000
        });
    }
   } catch (e) {
     console.log('error al cambiar contraseña'+ e);
   }
  


    //enviar informacion de password y id al controlador usuario
    // recibir informacion y incriptar la nueva contraseña 
    // guardar en base de datos y actualizar campo de reset_password = 0 y token_password = ''
    // notificar a usuario sobre el estado del proceso😳
    
}