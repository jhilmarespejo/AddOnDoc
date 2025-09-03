<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require 'header.php';

$_SESSION['errmsg']="";


if(isset($_SESSION['login']) && ($_SESSION['admision_cobranza'] == 1)){

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
							<h1 style="margin-left:1px;color:#C00000" class="box-title"><strong>REGISTRO TITULAR</strong></h1>
							<div class="box-tools pull-right">
							</div>
						</div>
                        <form name="formulario" id="formulario" method="POST">

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
								<input type="hidden" name="codigo_canal" id="codigo_canal" value="<?=$codigo_canal?>">
								<input type="hidden" name="datos_asesor" id="datos_asesor" value="<?=$datos_asesor?>">
								<input type="hidden" name="donde" id="donde" >
							</div>

							<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
								<label>Complemento:</label>
								<input type="text" class="form-control" name="extension" id="extension" maxlength="2" onkeypress="return permite(event, 'num_car')" onkeyup="this.value = this.value.toUpperCase()" placeholder="Complemento">
							</div>

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
								<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<label>Apellido Paterno:</label>
									<input type="text" class="form-control" name="ap_paterno" id="ap_paterno" maxlength="25" placeholder="Apellido Paterno" onkeyup="this.value = this.value.toUpperCase()" onkeypress="return permite(event, 'car')" required>
									<input type="hidden" name="encontrado" id="encontrado" >
									<input type="hidden" name="id_cliente" id="id_cliente" >
									<input type="hidden" name="usa_beneficiario" id="usa_beneficiario" >
								</div>

								<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<label>Apellido Materno:</label>
									<input type="text" class="form-control" name="ap_materno" id="ap_materno" maxlength="25" placeholder="Apellido Materno" onkeyup="this.value = this.value.toUpperCase()" onkeypress="return permite(event, 'car')">
								</div>

								<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<label>Nombres:</label>
									<input type="text" class="form-control" name="nombres" id="nombres" maxlength="25" placeholder="Nombres" onkeyup="this.value = this.value.toUpperCase()" onkeypress="return permite(event, 'car')" required>
								</div>

								<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<label>Fecha Nacimiento:</label>
									<input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento" maxlength="10" onfocusout="validarFecha()" placeholder="Fecha Nacimiento" required>
								</div>

								<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<label>Genero(*):</label>
									<select name="genero" id="genero" class="form-control selectpicker" required="">
										<option value="0">Seleccione el genero</option>
										<option value="F">FEMENINO</option>
										<option value="M">MASCULINO</option>
									</select>
								</div>

								<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<label>Telefono:</label>
									<input type="text" class="form-control" name="telefono" id="telefono" maxlength="8" placeholder="Telefono" onkeypress="return permite(event, 'num')" required>
								</div>
								<!-- Hasta aqui: CAPTURA DATOS CONTRATANTE -->	

								<!-- Desde aqui: CAPTURA DATOS PLAN A CONTRATAR -->	
								<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<label style='font-size:18px;color:#C00000'>SELECCIONE UN PLAN</label>
								</div>
								<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<label>Seleccionar un Plan(*):</label>
									<select id="planes" name="planes" class="form-control selectpicker" data-live-search="true" data-size="6" required>
									</select>
									<input type="hidden" name="respuesta_c" id="respuesta_c" >
									<input type="hidden" name="codigo_renovacion" id="codigo_renovacion" >

								</div>
								<!-- Hasta aqui: CAPTURA DATOS PLAN A CONTRATAR -->	


								<!-- Desde aqui: CAPTURA DATOS ASESOR  -->	
								<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12 datos_asesor">
									<label style='font-size:18px;color:#C00000'>DATOS DEL ASESOR</label>
								</div>
								<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12 datos_asesor">
									<label>Numero documento:</label>
									<input type="text" class="form-control" name="cedula_asesor" id="cedula_asesor" maxlength="8" placeholder="Numero cedula" onkeypress="return permite(event, 'num')" required>
								</div>
								<!-- Hasta aqui: CAPTURA DATOS ASESOR -->


								<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<button class="btn btn-primary" type="submit" style="width: 180px;" id="btnGuardar" name="btnGuardar" value="guardar"> <i class="fa fa-save"> </i>&nbsp;&nbsp; GUARDAR </button>
								</div>

								<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" id="idmensaje_final">
									<label>Mensaje:</label>
									<scan class="form-control" id="mensaje_final" ></scan>
								</div>

							</div>

						</form>
                    </div>

					<!-- AQUI HACEMOS LA COBRANZA -->
					<div class="box-header with-border" id='vamos_a_cobrar'>
						<div class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12"">
							<span class="widget-icon"><i class="fa fa-table"></i> </span>
						<h1 style="margin-left:25px;" class="box-title"><strong>COBRANZAS</strong></h1>
						<table id="tblfacts" class="table table-striped table-bordered table-condensed table-hover">
							<thead>
							<th style="text-align:center;" class="col-md-1">No</th>
							<th style="text-align:center;" class="col-md-9">RAZON SOCIAL</th>
							<th style="text-align:center;" class="col-md-2">Monto a Pagar</th>
							</thead>
							<tbody>
							<tr>
								<td style="text-align:center;">1</td>
								<td><input type="text" id="nombre_pac" name="nombre_pac" class="form-control text-align-right" style="text-align:center;" readonly></td>
								<td><input type="text" id="monto_a_pagar1" class="form-control text-align-right"  name="monto_a_pagar1" style="text-align:right;" readonly></td>
							</tr>
							<tr>
								<td colspan='2' style="text-align: right;" id="id_total_a_pagar"><strong>Total General</strong></td>
								<td><input type="text" id="monto_a_pagar2" class="form-control text-align-right"  name="monto_a_pagar2" style="text-align:right;" readonly></td>
							</tr>
							<tr>
								<td colspan='2' style="text-align: right;" id="id_pagado"><strong>Pagado</strong></td>

								<td><input type="text" id="total_paid" class="form-control text-align-right"  style="text-align:right;" onkeypress="return permite(event, 'num')" onkeyup="myFunction()"></td>
							</tr>
							<tr>
								<td colspan='2' style="text-align: right;" id="id_cambio"><strong>Cambio</strong></td>
								<td><input type="text" id="monto_cambio" class="form-control text-align-right"  style="text-align:right;" readonly></td>
								<td><input type="hidden" id="registro_a_facturar"></td>
							</tr>
							</tbody>
						</table>
						</div>
						<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" id="id_genera_factura">
							<form action="registro_a_imprimir.php" method="post">
								<input type="hidden" id="registro_a_imprimir" name="registro_a_imprimir">
								<button id="btnCancelar" class="btn btn-primary btn-lg"  onclick="generarFactura()" type="button" style="width: 250px;margin-left:227px;">
									<i class="fa fa-lg fa-fw fa-file-text"></i>  REGISTRAR COBRANZA</button>
								<button type = "submit" id="btnImprimir" class="btn btn-primary btn-lg" style = "margin-left:30px;">
									<i class="fa fa-lg fa-fw fa-file-text"></i>  IR A IMPRESION</button>
									<!-- <a href="../reportes/muestra_recibo.php" target="_blank" rel="noopener noreferrer">PRINT</a> -->
							</form>
						</div>
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
<script type="text/javascript" src="scripts/admision_cobranzas.js"></script>
<script>
function myFunction() {

	$('#total_paid').focus();
	monto_pagado = $('#total_paid').val(); 

	is_number = validateInput(monto_pagado);
	console.log('IS NUMBER: ' . is_number);
	monto_a_pagar = $('#monto_a_pagar1').val();

	console.log('monto_a_pagar: ' + monto_a_pagar);
	console.log('Monto Pagado: ' + monto_pagado);

	cambio = monto_pagado - monto_a_pagar;
	console.log('Cambio: ' + cambio);
	if(cambio >= 0){
		console.log('Devuelvo Cambio');
		$("#monto_cambio").val(cambio);
		$('#id_cambio').css("color", "red");
		$('#id_total_a_pagar').css("color", "green");
		$('#total_paid').attr("readonly","readonly");
		$('#id_genera_factura').show();
	}
}

function validateInput(val){

	ans = isNaN(val);
	if(ans){
		console.log('TEXTO');
	}else{
		console.log('NUMERO');
	}

}

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
