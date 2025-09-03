var tabla;

//Función que se ejecuta al inicio
function init() {
  
  listar();
  $("#fecha_inicio").change(listar);
  $("#fecha_fin").change(listar);

}

//Función Listar
function listar() {

  let fecha_inicio = $('#fecha_inicio').val();
  let fecha_fin = $('#fecha_fin').val();

  console.log(fecha_inicio);
  console.log(fecha_fin);

  tabla = $("#tbllistado")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [ ],
      columnDefs: [
        { "width": "9%", "targets": 0 },
        { "width": "9%", "targets": 1 },
	{ "width": "15%", "targets": 2 },
	{ className: "text-right", "targets": [4] },
	{ className: "text-center", "targets": [6, 7,8] },
      ],
      ajax: {
        url: "../ajax/consultasVitalicia.php?op=ventasfecha",
        data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin },
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      bDestroy: true,
      iDisplayLength: 5, //Paginación
      //order: [[7, "asc"]], //Ordenar (columna,orden)
    })
    .DataTable();
}


function anularAdmision(id_admision){

  console.log('ID REGISTRO: ' + id_admision);

  if(id_admision === 0){
    bootbox.alert('ATENCION: Solo puede anular registros que no estén cobrados!!');
  }else{

    text = 'Esta seguro de Anular este registro?\nREGISTRO: ' + id_admision;
    if (confirm(text) === true) {

      $.post(
        "../ajax/varios.php?op=anularAdmision",
        {id_admision: id_admision},
        function (datos) {

          console.log('DATOS: '+datos);
          data = JSON.parse(datos)
          console.log('DATA : '+data);
          if(data['status'] == 'ok'){
            bootbox.alert("El registro fue anulado de manera satisfactoria!!");
            $(location).attr("href", "reportes.php");
          }else{
            bootbox.alert(data['msg']);
          }
          //if (data == 'error') {
          //  bootbox.alert("Usuario y/o Password incorrectos");
          //} else {
          //  $(location).attr("href", "escritorio.php");
          //}
        }
      );
    }
  }
}

init();
