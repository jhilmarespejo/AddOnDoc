<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//error_reporting(E_ALL);


require 'header.php';

date_default_timezone_set('America/La_Paz');

if(isset($_SESSION['login']) && ($_SESSION['usuario'] == 1)){

  $id_usuario        = $_SESSION['idusuario'];
  $codigo_agencia    = $_SESSION['codigo_agencia'];
  $nombre_supervisor = $_SESSION['nombre'];
  $nombre_agencia    = $_SESSION['nombre_agencia'];

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
                    <div class="box-header with-border">
                    	<h1 class="box-title">Usuarios <button class="btn btn-success" id="btnagregar" onclick="mostrarform(true)">
                          <i class="fa fa-plus-circle"></i> Agregar</button></h1>&nbsp;&nbsp;
			  <a href="../reportes/reporte_usuarios.php">
                            <button class="btn btn-info"><i class="fa fa-clipboard"></i> Reporte</button></a></h1>

                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- centro -->
                    <div class="panel-body table-responsive" id="listadoregistros">
                      <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                        <thead>
                          <th>Accion</th>
                          <th>Nombre</th>
                          <th>Cargo</th>
                          <th>Rol</th>
                          <th>Agencia</th>
                          <th>Estado</th>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                          <th>Accion</th>
                          <th>Nombre</th>
                          <th>Cargo</th>
                          <th>Rol</th>
                          <th>Agencia</th>
                          <th>Estado</th>
                        </tfoot>
                      </table>
                    </div>
                    <div class="panel-body" id="formularioregistros">
                        <form name="formulario" id="formulario" autocomplete="off" method="POST">
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Nombre Usuario(*):</label>
                            <input type="text" class="form-control" name="nombre_usuario" id="nombre_usuario" onkeyup="this.value = this.value.toUpperCase()"  onkeypress="return permite(event, 'car')" autocomplete="off" placeholder="Nombre del Usuario" required>
                            <input type="hidden" name="id_usuario" id="id_usuario">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Cargo:</label>
                            <input type="text" class="form-control" name="cargo" id="cargo" onkeyup="this.value = this.value.toUpperCase()"  onkeypress="return permite(event, 'car')" autocomplete="off" placeholder="Cargo">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Rol(*):</label>
                            <select name="id_role" id="id_role" class="form-control selectpicker" data-live-search="true" required>
                            </select>
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Agencia(*):</label>
                            <select id="id_agencia" name="id_agencia" class="form-control selectpicker" data-live-search="true" required></select>
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Tipo Documento(*):</label>
                            <select name="tipo_documento" id="tipo_documento" class="form-control selectpicker" required>
                              <option value="0">Elija un tipo</option>
                              <option value="C">CEDULA IDENTIDAD</option>
                              <option value="E">CARNET EXTRANJERO</option>
                              <option value="P">PASAPORTE</option>
                              <option value="O">OTRO</option>
                            </select>
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Numero Documento(*):</label>
                            <input type="text" class="form-control" name="numero_documento" id="numero_documento" maxlength="10" onkeypress="return permite(event, 'num')" placeholder="Número documento" autocomplete="off" required>
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Complemento:</label>
                            <input type="text" class="form-control" name="extension" id="extension" placeholder="Complemento" autocomplete="off">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Expedido:</label>
                            <input type="text" class="form-control" name="expedido" id="expedido" onkeyup="this.value = this.value.toUpperCase()" placeholder="Expedido" autocomplete="off">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Login(*):</label>
                            <input type="text" class="form-control" name="login" id="login" placeholder="Login" autocomplete="off" required>
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Correo(*):</label>
                            <input type="email" class="form-control" name="correo" id="correo" placeholder="Correo" autocomplete="off" required>
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Telefono:</label>
                            <input type="text" class="form-control" name="telefono" id="telefono" maxlength="8" placeholder="Telefono" onkeypress="return permite(event, 'num')" placeholder="Telefono">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Estado(*):</label>
                            <input type="hidden" class="form-control" name="id_canal" id="id_canal" value="<?php echo $id_uduario ?>">
                            <!-- <input type="text" class="form-control" name="estado" id="estado" readonly> -->
                            <select name="id_condicion" id="id_condicion" class="form-control selectpicker">
                            </select>
                          </div>
                          <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                            <button class="btn btn-danger" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                          </div>
                        </form>
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
<script type="text/javascript" src="scripts/usuario.js"></script>
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
</script>

