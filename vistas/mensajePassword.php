<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//error_reporting(E_ALL);

require 'header.php';

if(isset($_POST['submit']))
{

	//header("location:http://20.242.113.194");
    header("location:login.html");
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
                            <!-- <h1 class="box-title">Cambio de Contraseña</h1> -->
                        </div>
                        <!-- centro -->
                        <div class="panel-body" id="formularioregistros">

                        <form class="form-login" method="post">
						<fieldset>
							<legend>
								Mensaje del Sistema
							</legend>

							<div style="color:blue;">
    							<h4 style="color:#ec7063;text-align:center;margin-top:20px;"><b>Su contraseña se actualizó de manera adecuada!!</b></h4><br />
    							<h4 style="color:#ec7063;text-align:center;margin-bottom:20px;"><b>Presione el boton de Salir e Ingrese nuevamente</b></h4>

							</div>


							<div class="form-actions" style="text-align: center;">

								<button type="submit" class="btn btn-primary" style="text-align:center;margin-top:30px;margin-bottom:30px;" name="submit">
									Salir <i class="fa fa-arrow-circle-right"></i>
								</button>

							</div>


						</fieldset>
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



