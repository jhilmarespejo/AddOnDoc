<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//error_reporting(E_ALL);

require "../config/Conexion.php";
require 'header.php';
$_SESSION['msg1'] = '';

date_default_timezone_set('America/La_Paz');
$login = $_GET['login'];


if(isset($_POST['submit'])){

    $pass_act = md5($_POST['cpass']);
    $pass_new = $_POST['npass'];
    $login    = $_SESSION['login'];
    $loginget = $_POST['login_get'];

    $sql = "SELECT clave FROM  usuario where clave ='$pass_act' AND login='$loginget'";
	//echo "SQL: " . $sql . "<br>";

    $res = mysqli_query($conexion, $sql);
	if(!$res){
		echo "SQL: " . $sql . "<br>";
		echo "ERROR Select: " . mysqli_error($db);
		die();
	}

    $numRows = mysqli_num_rows($res);
    $row = mysqli_fetch_assoc($res);

    $cryptPass = md5($pass_new);
    //$cryptPass = $pass_new;
    $used_password = validate_old_password($conexion, $login, $cryptPass);

    if($used_password == 'NO'){

        if($numRows > 0)
        {
            $good_password =  validate_strong_password($pass_new);

            $currentTime = date( 'Y-m-d H:i:s');
            if($good_password == 'Success'){

                $sql = "UPDATE usuario SET clave = '$cryptPass', fecha_update = '$currentTime', control = '$pass_new' WHERE login = '$login'";
                $res = mysqli_query($conexion, $sql);

                $sql = "INSERT users_used_passwords (login,password, fecha_cambio, habilitado) VALUES('$login', '$cryptPass', '$currentTime','SI')";
                $res = mysqli_query($conexion, $sql);

                $_SESSION['msg1']="La contraseña fue actualizada satisfactoriamente !!";

        	$host = $_SERVER['HTTP_HOST'];
        	$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        	$extra="./mensajePassword.php";
        	$addr = "Location: http://$host$uri/$extra";
        	// echo "ADDR:" . $addr . "<br>";
        	// die();
        	header("Location: http://$host$uri/$extra");


            }else{
                $_SESSION['msg1'] = $good_password;
            }

        }
        else
        {
            $_SESSION['msg1']="La contraseña anterior no coincide !!";
        }

    }else{

        $_SESSION['msg1']="No puede repetir una contraseña anterior !!";

    }

}

function validate_old_password($db, $login, $newPass){

    $sql = "SELECT * FROM users_used_passwords
                WHERE login = '$login'
                AND password = '$newPass'";

    $res = mysqli_query($db, $sql);
    $numRows = mysqli_num_rows($res);

    $retCode = $numRows ? 'SI':'NO';

    return $retCode;

}

function validate_strong_password($password){

    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $msg = 'La contraseña debe tener por lo menos 8 caracteres y debe incluir una mayúscula, una minúscula, un número y un caracter especial!!';
    }else{
        $msg = 'Success';
    }

    //echo "CLAVE: " . $password . "<br>";
    return $msg;

}

function dep($data){
        $format = print_r('<pre>');
        $format .= print_r($data);
        $format .= print_r('</pre>');
        return $format;
}
?>
<script>
function valid()
{
	if(document.chngpwd.cpass.value==""){

		alert("La contraseña actual está vacía !!");
		// alert("Current Password Filed is Empty !!"); MALY 18/05/2020 
		document.chngpwd.cpass.focus();
		return false;

	}else if(document.chngpwd.npass.value==""){

		alert("La nueva contraseña está vacía !!");
		// alert("New Password Filed is Empty !!"); MALY 18/05/2020
		document.chngpwd.npass.focus();
		return false;

	}else if(document.chngpwd.cfpass.value==""){

		alert("La confirmación de la nueva contraseña está vacía !!");
		// alert("Confirm Password Filed is Empty !!"); MALY 18/05/2020
		document.chngpwd.cfpass.focus();
		return false;

	}else if(document.chngpwd.npass.value!= document.chngpwd.cfpass.value){

		alert("La nueva contraseña y la confirmación de la nueva contraseña no coinciden  !!");
		// alert("Password and Confirm Password Field do not match  !!"); MALY 18/05/2020
		document.chngpwd.cfpass.focus();
		return false;

	}
	return true;
}
</script>
<!--Contenido-->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h1 class="box-title">Cambio de Contraseña</h1>
                        </div>
                        <!-- centro -->
                        <div class="panel-body" id="formularioregistros">

                            <p style="color:red;"><?php echo htmlentities($_SESSION['msg1']);?>

                            <?php echo htmlentities($_SESSION['msg1']="");?></p>
							<form role="form" name="chngpwd" method="post" onSubmit="return valid();">

                                <div class="form-group">
                                    <label for="motivo" style="font-size:18px;">La razón: </label>
                                    <span id='motivo'>
								</div>
                                <div class="form-group">
									<label for="exampleInputEmail1">Contraseña Actual</label>
									<input type="password" name="cpass" class="form-control"  placeholder="Ingrese Contraseña Actual">
									<input type="hidden" name="login_get" id="login_get" value="<?=$login?>" >
								</div>

                                   <div class="form-group">
									<label for="exampleInputPassword1">Nueva Contraseña</label><br>
									<label for="examplePssd1" style="color:red;"><strong>Atención. La nueva Contraseña debe cumplir con lo siguiente!!</strong></label><br>
									<span style="margin-bottom:35px;">Debe contener como mínimo una letra Mayúscula, una Minúscula, un Número y un caracter Especial(Ej:#$!@&*,etc.) y debe tener una longitud de 8 caracteres como mínimo.</span><br>
									<span> Tampoco puede usar una contraseña que ya haya usado anteriormente. </span><br>
									<span>&nbsp;</span>
									<input type="password" name="npass" class="form-control"  placeholder="Ingrese Nueva Contraseña">
								</div>

								<div class="form-group">
									<label for="exampleInputPassword1">Confirmar Contraseña</label>
									<input type="password" name="cfpass" class="form-control"  placeholder="Confirmar Contraseña">
								</div>

								<button type="submit" name="submit" class="btn btn-o btn-primary">Enviar</button>
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



