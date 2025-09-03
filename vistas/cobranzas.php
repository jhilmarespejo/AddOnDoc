<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesi贸n
}

require 'header.php';
//require "../config/Conexion.php";

$_SESSION['errmsg']="";

date_default_timezone_set('America/La_Paz');

if(isset($_SESSION['login']) && ($_SESSION['cobranza'] == 1)){

    $id_usuario     = $_SESSION['idusuario'];
    $codigo_agencia = $_SESSION['codigo_agencia'];

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
              <h1 style="margin-left:25px;" class="box-title"><strong>COBRANZAS</strong></h1>
            </div>
            <form name="formulario" id="formulario" method="POST">

      				<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                <label>Tipo Documento(*):</label>
                <select name="tipo_documento" id="tipo_documento" class="form-control selectpicker" required="">
                  <option value="CI">CEDULA IDENTIDAD</option>
                  <option value="CE">CARNET EXTRANJERO</option>
                  <option value="PS">PASAPORTE</option>
                  <option value="OT">OTRO</option>
                </select>
              </div>

              <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                <label>Numero Documento:</label>
				<input type="text" class="form-control" name="num_documento" id="num_documento" maxlength="10" placeholder="Num Documento">
                <input type="hidden" name="codigo_agencia" id="codigo_agencia" value="<?=$codigo_agencia?>">
              </div>

              <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                <label>Complemento:</label>
              	<input type="text" class="form-control" name="extension" id="extension" maxlength="2" placeholder="Complemento">
              </div>

              <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12">
							  <label> Buscar</label><br>
                <button id="btnAgregarArt" type="button" class="btn btn-primary" onClick="buscaClienteConDeuda()"> 
								<span class="fa fa-plus"></span> Buscar Cliente</button>
              </div>
            </form>
          </div>

					<div class="panel-body table-responsive" id="listadoregistros">
            <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
              <thead>
                <th>No</th>
                <th>RAZON SOCIAL</th>
                <th>FECHA DE ADMISION</th>
                <th>PLAN</th>
                <th>TOTAL DEUDA</th>
		<th>SELECCIONE</th>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
						    <th>No</th>
                <th>RAZON SOCIAL</th>
                <th>FECHA DE ADMISION</th>
                <th>PLAN</th>
                <th>TOTAL DEUDA</th>
							  <th>SELECCIONE</th>
              </tfoot>
            </table>
          </div>

          <div class="box-header with-border" id='vamos_a_cobrar'>
            <div class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12"">
                <span class="widget-icon"><i class="fa fa-table"></i> </span>
              <h1 style="margin-left:25px;" class="box-title"><strong>COBRANZAS</strong></h1>
              <table id="tblfacts" class="table table-striped table-bordered table-condensed table-hover">
                <thead>
                  <th style="text-align:center;" class="col-md-1">No</th>
                  <th style="text-align:center;" class="col-md-6">RAZON SOCIAL</th>
                  <th style="text-align:center;" class="col-md-2">Monto a Pagar</th>
                </thead>
                <tbody>
                  <tr>
                    <td style="text-align:center;">1</td>
                    <td><input type="text" id="nombre_pac" name="nombre_pac" class="form-control text-align-right" style="text-align:center;" readonly></td>
                    <td><input type="text" id="monto_a_pagar1" class="form-control text-align-right"  name="monto_a_pagar1" style="text-align:right;" readonly></td>
                  </tr>
                  <tr>
                    <td colspan='2' style="text-align: right;" ><strong>Total General</strong></td>
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
              <form action="../reportes/muestra_recibo.php" method="post">
                <input type="hidden" id="registro_a_imprimir" name="registro_a_imprimir">
                <button id="btnCancelar" class="btn btn-primary btn-lg"  onclick="generarFactura('<?php echo $codigo_agencia; ?>')" type="button" style="width: 250px;margin-left:227px;">
                      <i class="fa fa-lg fa-fw fa-file-text"></i>  REGISTRAR COBRANZA</button>
                <button type = "submit" id="btnImprimir" class="btn btn-primary btn-lg" style = "margin-left:30px;">
                      <i class="fa fa-lg fa-fw fa-file-text"></i>  IMPRIMIR RECIBO</button>
                      <!-- <a href="../reportes/muestra_recibo.php" target="_blank" rel="noopener noreferrer">PRINT</a> -->
              </form>
						</div>
            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" id="id_imprime_recibo">


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
<script type="text/javascript" src="scripts/cobranzas.js"></script>
<script>
function myFunction() {

  $('#total_paid').focus();
  monto_pagado = $('#total_paid').val(); 

  is_number = validateInput(monto_pagado);
  if(is_number === 'num'){
  
      console.log('IS NUMBER: ' . is_number);
      monto_a_pagar = $('#monto_a_pagar1').val();
      
      console.log('monto_a_pagar: ' + monto_a_pagar);
      console.log('Monto Pagado: ' + monto_pagado);
    
      cambio = monto_pagado - monto_a_pagar;
      console.log('Cambio: ' + cambio);
      if(cambio >= 0){
        console.log('Devuelvo Cambio');
        $("#monto_cambio").val(cambio);
        $('#id_cambio').css("color", "green");
        $('#total_paid').attr("readonly","readonly");
        $('#id_genera_factura').show();
      }
    }
}

function validateInput(val){

  let ans = isNaN(val);
  if(ans){
    console.log('TEXTO: ' + ans);
    ans = 'txt';
    return ans;
  }else{
    console.log('NUMERO: ' +ans);
    ans = 'num';
    return ans;
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
</script>

