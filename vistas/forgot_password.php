<?php
session_start();
error_reporting(1);
//include("include/config.php");

//$mensaje = "";


?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title> SALUD | INNOVASALUD SA</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta
      content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"
      name="viewport"
    />
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="../public/css/bootstrap.min.css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/css/font-awesome.css" />

    <!-- Theme style -->
    <link rel="stylesheet" href="../public/css/AdminLTE.min.css" />
    <!-- iCheck -->
    <link rel="stylesheet" href="../public/css/blue.css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <h3><b> Recupera su contraseña</b></h3>
            </div>
      <!-- /.login-logo -->
      <!-- <div class="login-box-body"> -->
    <div class="login-box-body" id="formularioregistros">
        <form class="form-login" method="post">
			<fieldset>
				<p style="color:#C00000;text-align:center;">
					Por favor ingrese su Correo Electrónico.<br />
                    Presione el boton REINICIAR y espere unos segundos
				</p>

				<div class="form-group has-feedback">
                   <input type="text" id="correo" name="correo" class="form-control" placeholder="Correo" />
                    <span class="fa fa-user form-control-feedback"></span>
                </div>

				<div class="form-actions" style="margin-top:20px;">
                    <button type="button" class="btn btn-primary"  onclick="envia_correo()"  style="margin-left:115px;">
                    <i class="fa fa-arrow-circle-right"></i>  REINICIAR</button>
				</div>

				<div class="new-account" style="margin-top:20px;">
					Tiene una cuenta?
					<a href="login.html">Iniciar sesión</a>
				</div>
            </fieldset>
        </form>

    </div>

	<div class="login-box-body" id="listadoregistros">
		<legend  style="text-align:center;">Mensaje del Sistema</legend>
		<div style="color:blue;">
    		<h5 style="color:#ec7063;text-align:center;margin-top:2px;"><b>Se le envió un password temporal a su correo</b></h5><br />
            <h5 style="color:#ec7063;text-align:center;margin-top:2px;"><b>Favor revise su correo y vuelva a ingresar</b></h5><br />
    		<h5 style="color:#ec7063;text-align:center;margin-bottom:20px;"><b>Presione el boton de Salir</b></h5>

			</div>

            <fieldset>
				<div class="form-actions">
					<a href="login.html" class="btn btn-o btn-primary"  style="margin-left:120px;
						border-radius:5px; border-width: 1px; font-weight: bold; padding-left: 10px; padding-right: 10px;">
                        <i class="fa fa-arrow-circle-left"></i> SALIR</a>
                </div>
			</fieldset>
        </div>
      <!-- /.login-box-body -->
    </div>

    <div style="margin-left:530px;">
        <div class="login-box-body col-lg-6 col-md-6 col-sm-6 col-xs-12" id="mensajedeerror">
            <legend style="text-align:center;">Mensaje del Sistema</legend>
    		<div style="color:blue;">
        		<h5 style="color:#ec7063;text-align:center;margin-top:2px;"><b>No se encontró el correo ingresado</b></h5><br />
                <h5 style="color:#ec7063;text-align:center;margin-top:2px;"><b>Favor revise el correo y vuelva a ingresar</b></h5><br />
        		<h5 style="color:#ec7063;text-align:center;margin-bottom:20px;"><b>Presione el boton de Salir</b></h5>

			</div>

            <fieldset>
				<div class="form-actions">
					<a href="login.html" class="btn btn-o btn-primary"  style="margin-left:198px;
						border-radius:5px; border-width: 1px; font-weight: bold; padding-left: 10px; padding-right: 10px;">
                        <i class="fa fa-arrow-circle-left"></i> SALIR
                    </a>
                </div>
			</fieldset>
        </div>

        <div class="login-box-body col-lg-6 col-md-6 col-sm-6 col-xs-12" id="canal_inhabilitado">
            <legend style="text-align:center;">Mensaje del Sistema</legend>
    		<div style="color:blue;">
        		<h5 style="color:#ec7063;text-align:center;margin-top:2px;"><b>Esta función no está habilitada</b></h5><br />
        		<h5 style="color:#ec7063;text-align:center;margin-top:2px;"><b>para este Canal</b></h5><br />
    		    <h5 style="color:#ec7063;text-align:center;margin-bottom:20px;"><b>Presione el boton de Salir</b></h5>
 
			</div>

            <fieldset>
				<div class="form-actions">
					<a href="login.html" class="btn btn-o btn-primary"  style="margin-left:198px;
						border-radius:5px; border-width: 1px; font-weight: bold; padding-left: 10px; padding-right: 10px;">
                        <i class="fa fa-arrow-circle-left"></i> SALIR
                    </a>
                </div>
			</fieldset>
        </div>

    </div>
    </div>

    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="../public/js/jquery-3.1.1.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="../public/js/bootstrap.min.js"></script>
    <!-- Bootbox -->
    <script src="../public/js/bootbox.min.js"></script>

    <script type="text/javascript" src="scripts/forgot_password.js"></script>
  </body>
</html>
