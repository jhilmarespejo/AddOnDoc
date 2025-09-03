var tabla;

//Función que se ejecuta al inicio
function init() {
  
  $('#segundo_hijo').hide();

  
  listar();

  //Cargamos las Ciudades al select
  $.post("../ajax/varios.php?op=listarCiudades", function (r) {
    $("#id_ciudad").html(r);
    $("#id_ciudad").selectpicker("refresh");
  });

  //Cargamos los Tipos de Contratos al select
  $.post("../ajax/varios.php?op=listarTipoContrato", function (r) {
    $("#id_tipo_contrato").html(r);
    $("#id_tipo_contrato").selectpicker("refresh");
  });

  //Cargamos los Tipos de Contratos al select
  $.post("../ajax/varios.php?op=listarCanales", function (r) {
    $("#id_canal").html(r);
    $("#id_canal").selectpicker("refresh");
  });

  $("#formularioregistros").hide();
  
  $("#formulario").on("submit", function (e) {
    guardar_c(e);
  });

  $("#tiene_otro_hijo").on("change", function () {
    flag = tiene_otro_hijo.checked
    if(flag == true){
      $('#segundo_hijo').show();
      $('#procesa_2do_hijo').val('SI');
    }else if(flag == false){
      $('#segundo_hijo').hide();

      $('#nombre_plan_hijo2').val('');
      $('#codigo_plan_hijo2').val('');
      $('#precio2').val('');
      $('#cantidad_plan_hijo2').val('');

    }

   });

}

//Función Listar
function listar() {

  let codigo_canal = $('#codigo_canal').val();
  console.log(codigo_canal);


  tabla = $("#tbllistado")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [ ],
      columnDefs: [
        { "width": "35%", "targets": 0 },
        { "width": "15%", "targets": 1 },
        { "width": "15%", "targets": 2 },
      ],
      ajax: {
        url: "../ajax/contratos_c.php?op=listar",
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

function guardar_c(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento
  $("#btnGuardar").prop("disabled", true);
  var formData = new FormData($("#formulario")[0]);

  $.ajax({
    url: "../ajax/contratos_c.php?op=guardar_c",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,

    success: function (datos) {
      alert("Contrato creado de manera exitosa!");
      console.log(datos);
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
    $("#btnGuardar").prop("disabled", false);
    $("#btnagregar").hide();
  } else {
    $("#listadoregistros").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();
  }
}



function cancelarform() {
  limpiar();
  mostrarform(false);
}


function limpiar() {

  $("#nombre_plan_padre").val("");
  $("#precio_padre").val("");
  $("#id_beneficiario").val("");
  $("#id_beneficiario").selectpicker("refresh");
  $("#vigencia").val("");
  $("#id_canal").val("");
  $("#id_canal").selectpicker("refresh");
  $("#id_ciudad").val("");
  $("#id_ciudad").selectpicker("refresh");
  $("#fecha_inicio").val("");
  $("#nombre_plan_hijo1").val("");
  $("#codigo_plan_hijo1").val("");
  $("#precio_plan_hijo1").val("");
  $("#cantidad_plan_hijo1").val("");
  $("#tiene_otro_hijo").val("");
  $("#nombre_plan_hijo2").val("");
  $("#codigo_plan_hijo2").val("");
  $("#precio_plan_hijo2").val("");
  $("#cantidad_plan_hijo2").val("");
  $("#gestion").val("");
  $("#gestion").selectpicker("refresh");
  $("#id_tipo_contrato").val("");
  $("#id_tipo_contrato").selectpicker("refresh");
  $("#genero_plan").val("");
  $("#genero_plan").selectpicker("refresh");

}

function desactivar(id_usuario){

  console.log('ID USUARIO: ' + id_usuario);

  bootbox.confirm("¿Está Seguro de desactivar este Usuario?", function(result){
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
