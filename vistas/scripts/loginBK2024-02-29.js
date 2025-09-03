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
      //if (datos!= "null") {  
        bootbox.alert("Usuario y/o Password incorrectos");
      } else {
        $(location).attr("href", "escritorio.php");
      }
    }
  );
});
