var tabla;

//Función que se ejecuta al inicio
function init() {
  
  listar();
  $("#fecha_inicio").change(listar);
  $("#fecha_fin").change(listar);

}

//Cargamos los canales al select
$.post("../ajax/varios.php?op=listarCanales", function (r) {
  $("#id_canal").html(r);
  $("#id_canal").selectpicker("refresh");
});

$(document).ready(function(){
  $("#id_canal").change(function(){
    let codigo_canal = $(this).val();
    
    $('#codigo_canal').val($(this).val());
    console.log('CANAL: '+codigo_canal);

    // Generamos fecha, para 1 día después 
    let fecha = generaFecha();

    console.log('Fecha: ' + fecha);

    $('#fecha_inicio').val(fecha);
    $('#fecha_fin').val(fecha);
    listar();
  
    $.post("../ajax/varios.php?op=listarAgencias", 
      {codigo_canal : codigo_canal}, 
      function(r){
        $("#id_agencia").html(r);
        $("#id_agencia").selectpicker("refresh");
      }
    );

  });

  $("#id_agencia").change(function(){
    let codigo_agencia = $(this).val();
    $('#codigo_agencia').val($(this).val());
    console.log('AGENCIA: '+codigo_agencia);

    listar();
  });
  
});

function generaFecha(){

  let fecha = new Date();
  let dia = fecha.getDate(); // Obtiene el día como número (1-31)
  let mes = fecha.getMonth() + 1; // Obtiene el mes como número (0-11), se suma 1 porque enero es 0
  let anio = fecha.getFullYear(); // Obtiene el año en formato de cuatro dígitos (yyyy)

  // Asegúrate de agregar un cero delante de los días y meses menores a 10
  dia = dia < 10 ? '0' + dia : dia;
  mes = mes < 10 ? '0' + mes : mes;

  let today = anio + '-' + mes + '-' + dia;
  
  return today;

}

//Función Listar
function listar() {

  let fecha_inicio = $('#fecha_inicio').val();
  let fecha_fin = $('#fecha_fin').val();
  let codigo_canal = $('#codigo_canal').val();
  let codigo_agencia = $('#codigo_agencia').val();

  console.log(fecha_inicio);
  console.log(fecha_fin);
  console.log('CodCanal: '+codigo_canal);
  console.log('CodAgencia: '+codigo_agencia);

  if(codigo_canal !== ""){

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
          url: "../ajax/consultas.php?op=ventasfechacanalagencia",
          data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, codigo_canal: codigo_canal, codigo_agencia: codigo_agencia},
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

}




init();
