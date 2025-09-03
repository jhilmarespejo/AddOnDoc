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
  let codigo_canal = $('#codigo_canal').val();

  console.log('F Ini: '+fecha_inicio);
  console.log('F Fin: '+fecha_fin);
  console.log('Cod Agencia: ' + codigo_agencia);
  console.log('Cod Canal: ' + codigo_canal);

  tabla = $("#tbllistado")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [ ],
      columnDefs: [
        { "width": "20%", "targets": 2 },
        { "width": "20%", "targets": 5 },
        { className: "text-right", "targets": [3,4] },
	{ className: "text-center", "targets": [1,7] },
      ],
      ajax: {
        url: "../ajax/consultas.php?op=ventasfechaagencia",
        data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, codigo_agencia:codigo_agencia, codigo_canal:codigo_canal},
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[0, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();
}

init();
