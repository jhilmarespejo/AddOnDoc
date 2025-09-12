<?php
error_reporting(0);
ob_start();
if(strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION['idusuario']))
{
      $_SESSION['idusuario']='';
      $_SESSION['nombre']='';
      $_SESSION['imagen']='';
      $_SESSION['login']='';
      $_SESSION['nombre_agencia']='';
      $_SESSION['codigo_agencia']='';
      $_SESSION['codigo_canal']='';
      $_SESSION['nombre_canal']='';
      $_SESSION['nombre_ciudad']='';
      $_SESSION['datos_asesor']='';
      $_SESSION['escritorio']='';
      $_SESSION['admision']='';
      $_SESSION['cobranza']='';
      $_SESSION['reportexcajero']='';
      $_SESSION['reportexag']='';
      $_SESSION['reporte']='';
      $_SESSION['usuario']='';
      $_SESSION['admision_cobranza']='';
      $_SESSION['reporte_innova']='';
      $_SESSION['contratos']='';
      $_SESSION['facturacion']='';
      $_SESSION['canal']='';
      $_SESSION['consulta_ws']='';
      $_SESSION['consulta_old']='';
}


?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SALUD | INNOVASALUD SA</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/css/font-awesome.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../public/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="../public/css/_all-skins.min.css">
    <link rel="apple-touch-icon" href="../public/img/apple-touch-icon.png">
    <link rel="shortcut icon" href="../public/img/favicon.ico">

    <!-- DATATABLES -->
    <link rel="stylesheet" type="text/css" href="../public/datatables/jquery.dataTables.min.css">
    <link href="../public/datatables/buttons.dataTables.min.css" rel="stylesheet"/>
    <link href="../public/datatables/responsive.dataTables.min.css" rel="stylesheet"/>

    <link rel="stylesheet" type="text/css" href="../public/css/bootstrap-select.min.css">

    <style>
      table.dataTable.dataTable_width_auto {
        width: auto;
      }

      table.dataTable td th{
        font-size: 0.8em;
      }
    </style>
  </head>
  <body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

      <header class="main-header">

        <!-- Logo -->
        <a href="index2.html" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>Stock</b></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>SALUD <?php echo $_SESSION['nombre_canal']; ?></b></span>
        </a>

        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Navegación</span>
          </a>
          <!-- Navbar Right Menu -->
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- Messages: style can be found in dropdown.less-->

              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <!--        <img src="../files/usuarios/<?php echo $_SESSION['imagen']; ?>" class="user-image" alt="User Image">     -->
                <img src="../files/usuarios/1487132068.jpg" class="user-image" alt="User Image">
                  <span class="hidden-md"><?php echo isset($_SESSION['nombre'])?$_SESSION['nombre']:''; ?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                  <img src="../files/usuarios/1487132068.jpg" class="img-circle" alt="User Image">
                  <p>Oficina: <?php echo $_SESSION['nombre_agencia']; ?></p>
                    <p><?php echo $_SESSION['nombre_canal']; ?>
                    </p>
                  </li>

                  <!-- Menu Footer-->
                  <li class="user-footer">

                    <!-- <div class="pull-right"> -->
                    <div>
                      <!-- <a href="../ajax/usuario.php?op=salir" class="btn btn-default btn-flat">Salir</a> -->

                      <div style="text-align:center;">
                        <a href="cambia_password.php">
                            <button class="btn btn-primary"><i class="fa fa-key"></i> Cambia Contraseña</button></a>
                        <a href="../ajax/usuario.php?op=salir">
                            <button class="btn btn-primary"><i class="fa fa-sign-out"></i> Salir</button></a>
                      </div>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>

        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            <li class="header"></li>


            <?php 
              //var_dump($_SESSION);

            if ($_SESSION['escritorio']=='1')
            {
              echo '<li id="mEscritorio">
              <a href="escritorio.php">
                <i class="fa fa-home"></i> <span>Escritorio</span>
              </a>
            </li>';
            }
            ?>

            <?php 
              if ($_SESSION['admision']=='1')
              {
                echo '<li id="mRegistro">
                <a href="admision.php">
                  <i class="fa fa-plus-square"></i> <span>Admisión</span>
                  <small class="label pull-right bg-green">REG</small>
                </a>
              </li>';
              }
            ?>

          <?php 
            if ($_SESSION['cobranza']=='1')
            {
              echo '<li id="mCobranzas">
              <a href="cobranzas.php">
                <i class="fa fa-money"></i> <span>Cobranzas</span>
                <small class="label pull-right bg-blue">COB</small>
              </a>
            </li>';
            }
            ?>

            <?php 
              if ($_SESSION['admision_cobranza']=='1')
              {
                echo '<li id="mRegistro">
                <a href="admision_cobranzas.php">
                  <i class="fa fa-plus-square"></i> <span>Admisión & Cobranzas</span>
                  <small class="label pull-right bg-green">REG</small>
                </a>
              </li>';
              }
            ?>

          <?php 
            if ($_SESSION['reportexcajero']=='1')
            {
              echo '<li id="mReportePorAgencia">
              <a href="reportePorCajero.php">
                <i class="fa fa-file-text"></i> <span>Reporte por Cajero</span>
                <small class="label pull-right bg-blue">CAJ</small>
              </a>
            </li>';
            }
          ?>

          <?php
            if ($_SESSION['reportexag']=='1')
            {
              echo '<li id="mReportePorAgencia">
              <a href="reportePorAgencia.php">
                <i class="fa fa-file-text"></i> <span>Reporte por Agencia</span>
                <small class="label pull-right bg-blue">SUC</small>
              </a>
            </li>';
            }
          ?>

          <?php
            if ($_SESSION['consulta_vit']=='1')
            {
              echo '<li id="mReportePorAgencia">
              <a href="reportePorAgenciaVitalicia.php">
                <i class="fa fa-file-text"></i> <span>Reporte Agencia Vitalicia</span>
                <small class="label pull-right bg-blue">SUC</small>
              </a>
            </li>';
            }
          ?>

          <?php
            if ($_SESSION['reporte']=='1')
            {
              echo '<li id="mReportePorAgencia">
              <a href="reportes.php">
                <i class="fa fa-file-text"></i> <span>Reporte General</span>
                <small class="label pull-right bg-green">NAL</small>
              </a>
            </li>';
            }
          ?>

	  <?php
            if ($_SESSION['consulta_vit']=='1')
            {
              echo '<li id="mReporteGeneralVitalicia">
              <a href="reportesVitalicia.php">
                <i class="fa fa-file-text"></i> <span>Reporte GeneralVitalicia</span>
                <small class="label pull-right bg-green">NAL</small>
              </a>
            </li>';
            }
          ?>


          <?php
            if ($_SESSION['anulacion']=='1')
            {
              echo '<li id="mAnulaciones">
              <a href="anulaciones.php">
                <i class="fa fa-file-text"></i> <span>Anulaciones</span>
                <small class="label pull-right bg-green">NAL</small>
              </a>
            </li>';
            }
          ?>

          <?php
            if ($_SESSION['usuario']=='1')
            {
              echo '<li id="mGestionUsuarios">
              <a href="usuario.php">
                <i class="fa fa-group"></i> <span>Usuarios</span>
                <small class="label pull-right bg-blue">USR</small>
              </a>
            </li>';
            }
            ?>


            <?php
            if ($_SESSION['reporte_innova']=='1')
            {
              echo '<li id="mReportePorAgencia">
              <a href="reportesInnova.php">
                <i class="fa fa-file-text"></i> <span>Reportes Administrador</span>
                <small class="label pull-right bg-green">SUC</small>
              </a>
            </li>';
            }
            ?>

            <?php 
            if ($_SESSION['facturacion']=='1')
            {
              echo '<li id="mGestionFacturas">
              <a href="facturacion.php">
                <i class="fa fa-group"></i> <span>Facturación</span>
                <small class="label pull-right bg-blue">SUC</small>
              </a>
            </li>';
            }
            ?>

	     <?php
            if ($_SESSION['consulta_ws']=='1')
            {
              echo '<li id="mGestionFacturas">
              <a href="consultaWS_PM.php">
                <i class="fa fa-group"></i> <span>Consulta WS PM</span>
                <small class="label pull-right bg-blue">CWS</small>
              </a>
            </li>';
            }
            ?>


	   <?php
            if ($_SESSION['consulta_old']=='1')
            {
              echo '<li id="mGestionFacturas">
              <a href="consultaClientesAntiguos.php">
                <i class="fa fa-group"></i> <span>Consulta Cli Antiguos</span>
                <small class="label pull-right bg-blue">CON</small>
              </a>
            </li>';
            }
            ?>

            <?php
            if ($_SESSION['contratos']==1)
            {
              echo '<li id="mCanal" class="treeview">
              <a href="#">
                <i class="fa fa-laptop"></i>
                <span>Gestion de Contratos</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li id="lCanal"><a href="contratos_c.php"><i class="fa fa-circle-o"></i> Contratos Colectivos</a></li>
              </ul>
			        <ul class="treeview-menu">
                <li id="lCanal"><a href="contratos_colectivos.php"><i class="fa fa-circle-o"></i> Constratos Masivos</a></li>
              </ul>
            </li>';
            }
            ?>


      	    <?php 
            if ($_SESSION['canal']==1)
            {
              echo '<li id="mCanal" class="treeview">
              <a href="#">
                <i class="fa fa-laptop"></i>
                <span>Gestión de Canal</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li id="lCanal"><a href="canal.php"><i class="fa fa-circle-o"></i> Gestión Canal</a></li>
              </ul>
            </li>';
            }
            ?>


            <?php if($_SESSION['canal']==1): ?>
              <li>
                <a href="#">
                  <i class="fa fa-plus-square"></i> <span>Ayuda</span>
                  <small class="label pull-right bg-red">PDF</small>
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="fa fa-info-circle"></i> <span>Acerca De...</span>
                  <small class="label pull-right bg-yellow">IT</small>
                </a>
              </li>
            <?php endif;?>
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>
