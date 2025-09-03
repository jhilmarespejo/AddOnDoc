<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//error_reporting(E_ALL);


require 'header.php';

date_default_timezone_set('America/La_Paz');

if(isset($_SESSION['login']) && ($_SESSION['contratos'] == 1)){

  $id_operador       = $_SESSION['idusuario'];
  $codigo_canal      = $_SESSION['codigo_canal'];
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
                      <h1 class="box-title"> Registro de Contratos &nbsp;&nbsp;&nbsp;<button class="btn btn-success" id="btnagregar" onclick="mostrarform(true)">
                          <i class="fa fa-plus-circle"></i> &nbsp;&nbsp;Agregar</button></h1>
                      <div class="box-tools pull-right">
                      </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- centro -->
                    <div class="panel-body table-responsive" id="listadoregistros">
                      <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                        <thead>
                          <th>Nombre de Plan</th>
                          <th>Contrato</th>
                          <th>Ciudad</th>
                        <!--  
                          <th>Gestión</th>
                          <th>Número Contrato</th>
                        -->  
                          <th>Tipo Contrato</th>
                          <th>Canal</th>
                          <th>Acción</th>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                          <th>Nombre de Plan</th>
                          <th>Contrato</th>
                          <th>Ciudad</th>
                        <!--  
                          <th>Gestión</th>
                          <th>Número Contrato</th>
                        -->
                          <th>Tipo Contrato</th>
                          <th>Canal</th>
                          <th>Acción</th>
                        </tfoot>
                      </table>
                    </div>

                    <div class="panel-body" id="formularioregistros">
                        <form name="formulario" id="formulario" method="POST">

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Nombre Plan Padre(*):</label>
                            <input type="text" class="form-control" name="nombre_plan_padre" id="nombre_plan_padre" autocomplete="off" onkeyup="this.value = this.value.toUpperCase()" placeholder="Nombre Plan" required>
                            <input type="hidden" name="codigo_canal" id="codigo_canal" value="<?=$codigo_canal?>">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Precio(*):</label>
                            <input type="text" class="form-control" name="precio_padre" id="precio_padre" autocomplete="off" placeholder="Precio">
                          </div>

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Beneficiarios(*):</label>
                            <select name="id_beneficiario" id="id_beneficiario" class="form-control selectpicker" data-live-search="true" required>
                              <option value="0">Elija una opcion</option>
                              <option value="S">SI</option>
                              <option value="N">NO</option>
                            </select>
                          </div>

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Vigencia(*):</label>
                            <input type="text" class="form-control" name="vigencia" id="vigencia" autocomplete="off" placeholder="Vigencia">
                          </div>

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Canal(*):</label>
                            <select name="id_canal" id="id_canal" class="form-control selectpicker" data-live-search="true" required>
                            </select>
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Ciudad(*):</label>
                            <select name="id_ciudad" id="id_ciudad" class="form-control selectpicker" data-live-search="true" required>
                            </select>
                          </div>
                          <div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                            <label>Gestion(*):</label>
                            <select name="gestion" id="gestion" class="form-control selectpicker" data-live-search="true" required>
                              <option value="0">Elija una Gestion</option>
                              <option value="24">2024</option>
                              <option value="25">2025</option>
                              <option value="26">2026</option>
                            </select>
                          </div>
                          <div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                            <label>Tipo Contrato(*):</label>
                            <select name="id_tipo_contrato" id="id_tipo_contrato" class="form-control selectpicker" data-live-search="true" required>
                            </select>
                          </div>

                          <div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                            <label>Fecha de Inicio:</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" maxlength="25" placeholder="Fecha Inicio" required>
                          </div>

                          <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <h4 class="box-title"> Datos Plan Hijo</h4>
                          </div>


                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Nombre Plan Hijo(*):</label>
                            <input type="text" class="form-control" name="nombre_plan_hijo1" id="nombre_plan_hijo1" autocomplete="off" onkeyup="this.value = this.value.toUpperCase()" placeholder="Nombre Plan" required>
                          </div>

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Codigo Plan Hijo(*):</label>
                            <input type="text" class="form-control"  name="codigo_plan_hijo1" id="codigo_plan_hijo1" autocomplete="off" onkeyup="this.value = this.value.toUpperCase()" placeholder="Código Plan Hijo" required>
                          </div>

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Precio Plan Hijo(*):</label>
                            <input type="text" class="form-control" name="precio_plan_hijo1" id="precio_plan_hijo1" placeholder="Precio">
                          </div>
                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Cantidad(*):</label>
                            <input type="text" class="form-control" name="cantidad_plan_hijo1" id="cantidad_plan_hijo1" autocomplete="off" required>
                          </div>

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Genero Plan(*):</label>
                            <select name="genero_plan" id="genero_plan" class="form-control selectpicker" data-live-search="true" required>
                              <option value="0">Elija un Genero</option>
                              <option value="F">FEMENINO</option>
                              <option value="M">MASCULINO</option>
                              <option value="X">MIXTO</option>
                            </select>
                          </div>

                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Visible(*):</label>
                            <select name="visible" id="visible" class="form-control selectpicker" data-live-search="true" required>
                              <option value="0">Elija si es Visible</option>
                              <option value="SI">SI</option>
                              <option value="NO">NO</option>
                            </select>
                          </div>




                          <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>Tiene otro hijo?(*):</label><br>
                            <input type="checkbox" name="tiene_otro_hijo" id="tiene_otro_hijo"> SI / NO
                          </div>


                          <div id=segundo_hijo>
                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                              <label>Nombre Plan Hijo 2(*):</label>
                              <input type="hidden" name="procesa_2do_hijo" id="procesa_2do_hijo" value="">
                              <input type="text" class="form-control" name="nombre_plan_hijo2" id="nombre_plan_hijo2" autocomplete="off" onkeyup="this.value = this.value.toUpperCase()" placeholder="Nombre Plan">
                            </div>

                            <div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                              <label>Codigo Plan Hijo 2(*):</label>
                              <input type="text" class="form-control"  name="codigo_plan_hijo2" id="codigo_plan_hijo2" autocomplete="off" onkeyup="this.value = this.value.toUpperCase()" placeholder="Código Plan Hijo">
                            </div>

                            <div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                              <label>Precio Plan Hijo 2(*):</label>
                              <input type="text" class="form-control" name="precio_plan_hijo2" id="precio_plan_hijo2" placeholder="Precio">
                            </div>
                            <div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                              <label>Cantidad 2(*):</label>
                              <input type="text" class="form-control" name="cantidad_plan_hijo2" id="cantidad_plan_hijo2" autocomplete="off" placeholder="Cantidad">
                            </div>
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
<script type="text/javascript" src="scripts/contratos_c.js"></script>

