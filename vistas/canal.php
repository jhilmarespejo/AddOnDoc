<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

require 'header.php';
//require "../config/Conexion.php";

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
                          <h1 class="box-title">CANALES &nbsp; &nbsp; <button class="btn btn-success" id="btnagregar" onclick="mostrarform(true)"><i class="fa fa-plus-circle"></i> Agregar</button></h1>
                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- centro -->
                    <div class="panel-body table-responsive" id="listadoregistros">
                        <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                          <thead>
                            <th>OPCIONES</th>
                            <th>ID CANAL</th>
							              <th>NOMBRE CANAL</th>
							              <th>COMISION</th>
                            <th>ESTADO</th>
                          </thead>
                          <tbody>
                          </tbody>
                          <tfoot>
                            <th>OPCIONES</th>
                            <th>ID CANAL</th>
							              <th>NOMBRE CANAL</th>
							              <th>COMISION</th>
                            <th>ESTADO</th>
                          </tfoot>
                        </table>
                    </div>
                    <div class="panel-body" style="height: 400px;" id="formularioregistros">
                        <form name="formulario" id="formulario" method="POST">

                          <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>ID CANAL:</label>
                            <input type="hidden" class="form-control" name="id" id="id">
                            <input type="text" class="form-control" name="id_canal" id="id_canal" maxlength="50" placeholder="Id Canal" required>
                          </div>

                          <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>NOMBRE CANAL:</label>
                            <input type="text" class="form-control" name="nombre_canal" id="nombre_canal" maxlength="50" placeholder="Nombre Canal" required>
                          </div>

                          <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>COMISION:</label>
                            <input type="text" class="form-control" name="comision" id="comision" maxlength="50" placeholder="Nombre Canal" required>
                          </div>

	              	<div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>ESTADO:</label>
                            <input type="text" class="form-control" name="estado" id="estado" maxlength="50" placeholder="Nombre Canal" required>
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
<script type="text/javascript" src="scripts/canal.js"></script>
<?php
ob_end_flush();
?>
