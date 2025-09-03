var tabla;

//Función que se ejecuta al inicio
function init() {
  
  listar();
  limpiar();
  $('#nombre_usuario').val('');


  $("#formularioregistros").hide();
  
  //Cargamos las agencias al select
  $.post("../ajax/usuario.php?op=selectAgencia", function (r) {
    $("#id_agencia").html(r);
    $("#id_agencia").selectpicker("refresh");
  });

  // Cargamos los roles que se tienen 
  $.post("../ajax/usuario.php?op=selectRole", function (e) {
    $("#id_role").html(e);
    $("#id_role").selectpicker("refresh");
  });

  // Cargamos los estado que se tienen 
  $.post("../ajax/usuario.php?op=selectEstado", function (e) {
    $("#id_condicion").html(e);
    $("#id_condicion").selectpicker("refresh");
  });


  $("#formulario").on("submit", function (e) {
    guardaryeditar(e);
  });


  $("#login").focusout(function () {

    // validating the login
    var login = $("#login").val();
 

    //Valida si ya tenemos este login
    if (login !== "") {
       //var regex = /^[A-Za-z0-9._]*\@[A-Za-z]*\.[A-Za-z]{2,5}$/;
       $.post("../ajax/usuario.php?op=validaLogin",{valida_login : login}, function(data)
        {
          console.log(data);
          data = JSON.parse(data);
          if(data.status != 'ok'){  
            bootbox.alert(data.msg);
            $("#login").val("");
          }else{
            //mostrarform(true);
            $("#login").val(login);
          }

        })


    } else {
       $("#message").html("Ingrese su Login");
       $("#message").css("color", "red");
    }
 });

}

function limpiar(){

  $('#nombre_usuario').val('');
  $('#cargo').val('');
  $('#id_role').val('');
  $("#id_role").selectpicker("refresh");
  $('#id_agencia').val('');
  $("#id_agencia").selectpicker("refresh");
  $('#tipo_documento').val('');
  $("#tipo_documento").selectpicker("refresh");
  $('#numero_documento').val('');
  $('#extension').val('');
  $('#expedido').val('');
  $('#login').val('');
  $('#correo').val('');
  $('#telefono').val('');
  $('#id_condicion').val('');
  $("#id_condicion").selectpicker("refresh");
  $('#correo').val('');
  $('#id_usuario').val('');

}
//Función Listar
function listar() {

  tabla = $("#tbllistado")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [ ],
      columnDefs: [
        { "width": "10%", "targets": 0 },
      ],
      ajax: {
        url: "../ajax/usuario.php?op=listar",
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[0, "asc"]], //Ordenar (columna,orden)
    })
    .DataTable();
}

function guardaryeditar(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento
  $("#btnGuardar").prop("disabled", true);
  var formData = new FormData($("#formulario")[0]);

  $.ajax({
    url: "../ajax/usuario.php?op=guardaryeditar",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,

    success: function (datos) {
      bootbox.alert(datos);
      mostrarform(false);
      tabla.ajax.reload();
    },
  });
  limpiar();
}



function mostrarform(flag) {
  limpiar();
  if (flag) {
    $("#listadoregistros").hide();
    $("#formularioregistros").show();
    $('#nombre_usuario').val('');
    $("#btnGuardar").prop("disabled", false);
    $("#btnagregar").hide();
  } else {
    $("#listadoregistros").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();
    limpiar();
  }
}



function cancelarform() {
  limpiar();
  mostrarform(false);
}


function mostrar(id)
{
	//console.log("ID CANAL:"+id);

	$.post("../ajax/usuario.php?op=mostrar",{id_usuario : id}, function(data)
	{
		data = JSON.parse(data);
		// console.log(data);
		mostrarform(true);

    		console.log(data);

		$("#nombre_usuario").val(data.nombre);
    		$("#id_usuario").val(data.idusuario);
		$("#cargo").val(data.cargo);
		$("#id_role").val(data.id_role);
    		$("#id_role").selectpicker("refresh");
		$("#id_agencia").val(data.codigoAlmacen);
   		$("#id_agencia").selectpicker("refresh");
    		$("#tipo_documento").val(data.tipo_documento);
    		$("#tipo_documento").selectpicker("refresh");
    		$("#numero_documento").val(data.numero_documento);
    		$("#extension").val(data.extension);
    		$("#expedido").val(data.expedido);
    		$("#login").val(data.login);
    		$("#correo").val(data.correo);
    		$("#telefono").val(data.telefono);
    		$("#id_condicion").val(data.id_condicion);
    		$("#id_condicion").selectpicker("refresh");
    		$("#id_agencia").val(data.codigoAlmacen);

 	})
}


function desactivar(id_usuario){

  console.log('ID USUARIO: ' + id_usuario);

  bootbox.confirm("¿Está seguro de dar de baja este Usuario?", function(result){
		if(result)
        {
        	$.post("../ajax/usuario.php?op=desactivar", {id_usuario : id_usuario}, function(e){
        		bootbox.alert(e);
	            tabla.ajax.reload();
        	});	
        }
	})

}

init();
