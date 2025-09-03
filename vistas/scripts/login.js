$("#frmAcceso").on("submit", function (e) {
  e.preventDefault();
  logina = $("#logina").val();
  clavea = $("#clavea").val();

  //console.log(logina);
  //console.log(clavea);

  $.post(
    "../ajax/usuario.php?op=verificar",
    { logina: logina, clavea: clavea },
    function (datos) {

      console.log(datos);
      data = JSON.parse(datos);

      if (data['status'] == 'error') {

        if (data['motivo'] == 'NoHizoPrimerCambio'){
          console.log('Debe Cambiar la password');
          bootbox.alert("Debe cambiar la contraseña la primera vez que ingresa al Sistema!");
          //$(location).attr("href", "cambia_password.php");
	  login = data['login'];
	  console.log(data);
	  console.log('LOGIN:' + login);

          setTimeout(()=> {
              $(location).attr("href", "cambia_password.php?login="+login);
            }
           ,3000);
        }

        if (data['motivo'] == 'MuchosDias'){
            console.log('Cantidad de dias');
          bootbox.alert("Cantidad de días sin cambiar contraseña\nDebe cambiar su Contraseña");
          //$(location).attr("href", "cambia_password.php");
	  login = data['login'];
          console.log(data);
          console.log('LOGIN:' + login);

          setTimeout(()=> {
		$(location).attr("href", "cambia_password.php?login="+login);
            }
           ,2500);

        }

        if (data['motivo'] == 'UsuarioPasswordInvalido'){
            console.log('Password Invalido');
          bootbox.alert("Usuario y/o Password incorrectos");
          //$(location).attr("href", "login.html");  

          setTimeout(()=> {
              $(location).attr("href", "login.html");
            }
           ,2500);

        }

        if (data['motivo'] == 'UsuarioBaja'){
          console.log('Usuario Baja');
          alert("Usuario dado de Baja!\nFavor ponerse en contacto con Acceso");
        //$(location).attr("href", "login.html");  

        setTimeout(()=> {
            $(location).attr("href", "login.html");
          }
         ,2000);

      }
      
      } else {

        // Todo bien con el usuario
        $(location).attr("href", "escritorio.php");
      
      }
    }
  );
});
