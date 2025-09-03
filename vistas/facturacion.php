<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//error_reporting(E_ALL);


require 'header.php';

date_default_timezone_set('America/La_Paz');

if(isset($_SESSION['login']) && ($_SESSION['reporte'] == 1)){
	
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
                          <h1 class="box-title">Reporte Facturacion </h1>
                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- centro -->
                    <div class="panel-body table-responsive" id="listadoregistros">
                        <form action='../reportes/reporteGeneral.php' name="formulario" id="formulario" method="POST">
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label for="fecha_inicio">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" value="<?php echo date("Y-m-d"); ?>">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label for="fecha_fin">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" value="<?php echo date("Y-m-d"); ?>">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label for="supervisor">Usuario</label>
                            <input type="text" class="form-control" name="supervisor" id="supervisor" value="<?php echo $nombre_supervisor;?>" readonly>
                            <input type="hidden" name="nombre_supervisor" id="nombre_supervisor" value="<?php echo $nombre_supervisor; ?>">
                            <input type="hidden" name="codigo_agencia" id="codigo_agencia" value="<?php echo $codigo_agencia; ?>">
                            <input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $id_usuario; ?>">
                            <input type="hidden" name="nombre_agencia" id="nombre_agencia" value="<?php echo $nombre_agencia; ?>">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12">
                            <label> Reporte</label><br>
                            <button class="btn btn-primary" type="submit" name="exportar" id="exportar"><i class="fa fa-save"></i>&nbsp;&nbsp; Generar el Reporte Excel</button>
                          </div>
                        </form>
                        <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                          <thead>
                            <th>Accion</th>
                            <th>Agencia</th>
                            <th>Ciudad</th>
                            <th>Plan</th>
                            <th>Precio</th>
                            <th>Cliente</th>
                            <th>Cedula</th>
                            <th>F. Cobranzas</th>
                            <th>F. Facturacion</th>
                            <th>Estado</th>
                          </thead>
                          <tbody>                            
                          </tbody>
                          <tfoot>
                          <th>Accion</th>
                            <th>Agencia</th>
                            <th>Ciudad</th>
                            <th>Plan</th>
                            <th>Precio</th>
                            <th>Cliente</th>
                            <th>Cedula</th>
                            <th>F. Cobranzas</th>
                            <th>F. Facturacion</th>
                            <th>Estado</th>
                          </tfoot>
                        </table>
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
<script type="text/javascript" src="scripts/facturacion.js"></script>

