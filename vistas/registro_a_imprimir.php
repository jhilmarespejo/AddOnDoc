<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require 'header.php';

require "../config/Conexion.php";

$_SESSION['errmsg']="";




if(isset($_POST['registro_a_imprimir'])){

	//var_dump($_POST);
	$registro_a_imprimir = $_POST['registro_a_imprimir'];
	//die();

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

				<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="text-align:center;margin-top:20px;">
					<h1 style="margin-left:1px;color:#C00000;font-size:25px" class="box-title"><strong>SECCION IMPRESION</strong></h1>
				</div>

				<div style="margin-top:90px;">

					<div class="form-group col-lg-6 col-md-6 col-sm-3 col-xs-12 mt-15 mx-auto" >

						<a href="../reportes/muestra_recibo.php??registro_a_imprimir=<?php echo $registro_a_imprimir ?>" class="btn btn-o btn-primary" 
							style="background:#007aff; color:#ffffff; border-radius:5px; border-width: 0px; font-weight: bold; padding-left: 30px; 
							margin-left:250px;padding-top: 8px;padding-bottom: 8px;padding-right: 30px;" target="_blank" >IMPRIME RECIBO</a>

					</div>

					<div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
						<a href="escritorio.php" class="btn btn-o btn-primary" style="background:#007aff; color:#ffffff; border-radius:5px; border-width: 0px; font-weight: bold; 
							margin-left:50px;padding-left: 50px; padding-top: 8px;padding-bottom: 8px;padding-right: 50px;">REGRESAR</a>
					</div>
				</div>

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

