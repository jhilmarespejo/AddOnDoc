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
  let codigo_agencia = $('#codigo_agencia').val();
  let id_usuario = $('#id_usuario').val();
  let codigo_canal = $('#codigo_canal').val();

  console.log('F Ini: '+fecha_inicio);
  console.log('F Fin: '+fecha_fin);
  console.log('ID USER: '+id_usuario);
  console.log('Cod Agencia: ' + codigo_agencia);
  console.log('Codigo Canal: ' + codigo_canal);

  tabla = $("#tbllistado")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [ ],
      columnDefs: [
        { "width": "15%", "targets": 1 },
        { "width": "20%", "targets": 3 },
	{ "width": "15%", "targets": 5 }
      ],
      ajax: {
        url: "../ajax/consultas.php?op=ventasfechacajero",
        data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin},
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[7, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();
}

init();
