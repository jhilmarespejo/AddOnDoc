<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require 'header.php';
require "../config/Conexion.php";

$_SESSION['errmsg']="";


if(isset($_SESSION['login']) && ($_SESSION['consulta_old'] == 1)){

    $id_usuario     = $_SESSION['idusuario'];
	$codigo_agencia = $_SESSION['codigo_agencia'];
	$codigo_canal   = $_SESSION['codigo_canal'];
	$datos_asesor   = $_SESSION['datos_asesor'];

}else{

	$host = $_SERVER['HTTP_HOST'];
	$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra="./login.html";
	header("Location: http://$host$uri/$extra");
	exit();

}

?>
<!--Contenido-->
      <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
        <!-- Main content -->
	<section class="content">
    	<div class="row">
        	<div class="col-md-12">
            	<div class="box">
                    <!-- /.box-header -->
                    <!-- centro -->
                	<div class="panel-body" style="height: 100%;" id="formularioregistros">
						<div class="box-header with-border">
							<h1 style="margin-left:1px;color:#C00000" class="box-title"><strong>CONSULTA CLIENTES ANTIGUOS</strong></h1>
							<div class="box-tools pull-right">
							</div>
						</div>

						<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
							<label>Tipo Documento(*):</label>
							<select name="tipo_documento" id="tipo_documento" class="form-control selectpicker">
								<option value="C">CEDULA IDENTIDAD</option>
								<option value="E">CARNET EXTRANJERO</option>
								<option value="P">PASAPORTE</option>
								<option value="O">OTRO</option>
							</select>
						</div>


						<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
							<label>Numero Documento:</label>
							<input type="text" class="form-control" name="num_documento" id="num_documento" maxlength="25" onkeypress="return permite(event, 'num')" placeholder="Num Documento">
						</div>

						<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
							<label>Complemento:</label>
							<input type="text" class="form-control" name="extension" id="extension" maxlength="2" onkeypress="return permite(event, 'num_car')" onkeyup="this.value = this.value.toUpperCase()" placeholder="Complemento">
						</div>

<!--
						<div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
							<label>Expedicion:</label>
							<input type="text" class="form-control" name="expedido" id="expedido" maxlength="25" placeholder="Expedido">
						</div>
-->
						<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12">
							<label> Buscar</label><br>
							<button id="btnAgregarArt" type="button" class="btn btn-primary" style='width: 180px;' onClick="buscaPaciente()"> 
								<span class="fa fa-plus"></span> Buscar y Rellenar</button>
						</div>

						<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" id="idmensaje">
							<label>Mensaje:</label>
							<scan class="form-control" id="cliente_no_encontrado" style="border: 1px solid #4CAF50;padding:3px;"></scan>
	                    			</div>

						<!--   Desde aqui: CAPTURA DE DATOS CONTRATANTE -->
						<div id="datos_titular">
							<div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
								<label>Nombre Cliente:</label>
								<input type="text" class="form-control" name="nombre_cliente" id="nombre_cliente" maxlength="45">
							</div>

							<div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
								<label>Numero Cedula:</label>
								<input type="text" class="form-control" name="numero_cedula" id="numero_cedula" maxlength="15">
							</div>

							 <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                                                <label>Nombre Plan:</label>
                                                                <input type="text" class="form-control" name="nombre_plan" id="nombre_plan" maxlength="10">
                                                        </div>

							<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
								<label>Codigo Plan:</label>
								<input type="text" class="form-control" name="codigo_plan" id="codigo_plan" maxlength="10">
							</div>

							<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
								<label>Fecha de Fin:</label>
								<input type="date" class="form-control" name="fecha_fin" id="fecha_fin" maxlength="16">
							</div>

							 <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                                                <label>Status:</label>
                                                                <input type="text" class="form-control" name="status_pac" id="status_pac" maxlength="16">
                                                        </div>

							<!-- Hasta aqui: CAPTURA DATOS CONTRATANTE <span class="fa fa-arrow-circle-left" </span>   -->	

						  </div>

						<div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" id='volver_a_buscar_d'>
							<button id="volver_buscar" type="button" class="btn btn-primary" style='width: 180px;font-style:arial;' 
								onClick="volver_a_buscar()"> Volver a Buscar</button>
						</div>



                    <!--Fin centro -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->

</div><!-- /.content-wrapper -->
  <!--Fin-Contenido-->
  
<?php
require 'footer.php';

?>
<script type="text/javascript" src="scripts/consultaDB_OLD.js"></script>
<script>
function permite(elEvento, permitidos) {
  // Variables que definen los caracteres permitidos
  var numeros = "0123456789";
  var caracteres = " aábcdeéfghiíjklmnñoópqrstuvwxyzAÁBCDEÉFGHIÍJKLMNÑOÓPQRSTUVWXYZ";
  var numeros_caracteres = numeros + caracteres;
  var teclas_especiales = [];
  // 8 = BackSpace, 46 = Supr, 37 = flecha izquierda, 39 = flecha derecha
 
 
  // Seleccionar los caracteres a partir del parámetro de la función
  switch(permitidos) {
    case 'num':
      permitidos = numeros;
      break;
    case 'car':
      permitidos = caracteres;
      break;
    case 'num_car':
      permitidos = numeros_caracteres;
      break;
  }
 
  // Obtener la tecla pulsada 
  var evento = elEvento || window.event;
  var codigoCaracter = evento.charCode || evento.keyCode;
  var caracter = String.fromCharCode(codigoCaracter);
 
  // Comprobar si la tecla pulsada es alguna de las teclas especiales
  // (teclas de borrado y flechas horizontales)
  var tecla_especial = false;
  for(var i in teclas_especiales) {
    if(codigoCaracter == teclas_especiales[i]) {
      tecla_especial = true;
      break;
    }
  }
 
  // Comprobar si la tecla pulsada se encuentra en los caracteres permitidos
  // o si es una tecla especial
  return permitidos.indexOf(caracter) != -1 || tecla_especial;
}



function validarFecha() {

    
    let fechaString = $('#fecha_nacimiento').val();
    let ff = '2024-02-24';
 
    // Crear un objeto Date usando la cadena proporcionada
    var fecha = new Date(fechaString);
    
    var fechaActual = new Date();

    // Obtener el año, mes y día
    var año = fechaActual.getFullYear();
    var mes = ('0' + (fechaActual.getMonth() + 1)).slice(-2); // Agregar ceros a la izquierda si es necesario
    var dia = ('0' + fechaActual.getDate()).slice(-2); // Agregar ceros a la izquierda si es necesario

    // Concatenar los componentes de la fecha en el formato "yyyy-MM-dd"
    var fechaFormateada = año + '-' + mes + '-' + dia;

    
    // Verificar la longitud de la cadena de fecha
    if (fechaString.length !== 10) { // Longitud válida para el formato "YYYY-MM-DD"
        alert('Fecha Inválida');
        $('#fecha_nacimiento').focus();
        $('#fecha_nacimiento').val(fechaFormateada);
    }


    // Verificar si la fecha es válida y no es mayor que la fecha actual
    if (isNaN(fecha.getTime()) || fecha > fechaActual) {
        alert('Fecha Inválida');
        $('#fecha_nacimiento').focus();
        $('#fecha_nacimiento').val(fechaFormateada);
        
    } else {
        return true;
    }
}
</script>
