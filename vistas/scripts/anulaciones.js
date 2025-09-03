var tabla;

//Función que se ejecuta al inicio
function init() {
  
  listar();
  $("#fecha_inicio").change(listar);
  $("#fecha_fin").change(listar);

}

//Función Listar
function listar() {

  //let fecha_inicio = $('#fecha_inicio').val();
  //let fecha_fin = $('#fecha_fin').val();

  //console.log(fecha_inicio);
  //console.log(fecha_fin);

  tabla = $("#tbllistado")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [ ],
      columnDefs: [
        { "width": "20%", "targets": 4 },
        { "width": "20%", "targets": 7 },
        {
          "targets": 1, // Indica la columna que deseas modificar (por ejemplo, columna 1)
          "createdCell": function (td, cellData, rowData, row, col) {
              // Aplica el estilo CSS directamente a la celda
              $(td).css('text-align', 'center');
          }
        }
      ],
      ajax: {
        url: "../ajax/anulaciones.php?op=ventasfecha",
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


function anularAdmision(id_admision, estado){

  console.log('ID REGISTRO: ' + id_admision);
  console.log('ESTADO: ' + estado);

  if(id_admision === 0){
    bootbox.alert('ATENCION: Este registro ya se encuentra anulado!!');
  }else{

    text = 'Esta seguro de Anular este registro?\nTransaccion No: ' + id_admision;
    if (confirm(text) === true) {

      document.body.style.cursor = 'wait';
      $.post(
        "../ajax/anulaciones.php?op=anularAdmision",
        {id_admision: id_admision, estado:estado},
        function (datos) {

          console.log('DATOS: '+datos);
          data = JSON.parse(datos)
          console.log('DATA : '+data);
          if(data['status'] == 'ok'){
            bootbox.alert("El registro fue anulado de manera satisfactoria!!");
            //$(location).attr("href", "anulaciones.php");
	    document.body.style.cursor = 'default';
	    setTimeout(()=> {
                 $(location).attr("href", "anulaciones.php");
            }
            ,2500);
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
