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
        { "width": "20%", "targets": 3 },
        { "width": "20%", "targets": 5 }
      ],
      ajax: {
        url: "../ajax/facturacion.php?op=ventasfecha",
        data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin },
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[7, "asc"]], //Ordenar (columna,orden)
    })
    .DataTable();
}

function saca_fecha_hora(){
    
    const dt = new Date();
    const padL = (nr, len = 2, chr = `0`) => `${nr}`.padStart(2, chr);

    let fecha_hora = `${
        padL(dt.getMonth()+1)}/${
        padL(dt.getDate())}/${
        dt.getFullYear()} ${
        padL(dt.getHours())}:${
        padL(dt.getMinutes())}:${
        padL(dt.getSeconds())}`;
    
    return fecha_hora;


}


function llamarWS_Facturacion(id_registro){

  console.log('ID REGISTRO: ' + id_registro);

   let hora_envio = saca_fecha_hora();
   console.log("HORA ENVIA: " + hora_envio);
   

  if(id_registro === 0){
    bootbox.alert('ATENCION: Solo puede FACTURAR registros que solo estén cobrados!!');
  }else{

    $.post(
      "../ajax/facturacion.php?op=llamarWS_Facturacion",
      {id_registro: id_registro},
      function (datos) {

        console.log('DATOS: '+datos);
        data = JSON.parse(datos);

        let hora_recibe = saca_fecha_hora();
        console.log("HORA RECIBE: " + hora_recibe);
        
        console.log('DATA STATUS: '+data.status_fact);
        if(data.status_fact === 'ok'){
          tabla.ajax.reload();
          bootbox.alert("El registro fue facturado de manera satisfactoria!!");
          //$(location).attr("href", "reportes.php");
        }else{
          bootbox.alert(data.msg);
        }
      }
    );
    
  }
}

init();
