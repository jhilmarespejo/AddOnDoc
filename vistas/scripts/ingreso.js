var tabla;

//Función que se ejecuta al inicio
function init() {
  mostrarform(true);
  limpiar();
  
  $("#formulario").on("submit", function (e) {
    guardaryeditar(e);
  }); 

  //Cargamos los almacenes al select
  /* $.post("../ajax/articulo.php?op=listarArticulos", function (r) {
    $("#idarticulo").html(r);
    $("#idarticulo").selectpicker("refresh");
  }); */
  
}

//Función limpiar
function limpiar() {

  $(".filas").remove();
  $("#idalmacen").val("");
  $("#idalmacen").selectpicker("refresh");
  $("#num_comprobante").val("");
  $("#fecha_compra").val("");
  $("#mensaje_final").val("");

  //Obtenemos la fecha actual
  var now = new Date();
  var day = ("0" + now.getDate()).slice(-2);
  var month = ("0" + (now.getMonth() + 1)).slice(-2);
  var today = now.getFullYear() + "-" + month + "-" + day;
  $("#fecha_compra").val(today);
  
}

function mostrarform(flag) {
  //limpiar();
  //console.log("ID ALM DEST:" + idalmacen);
  
  if (flag) {
    $("#listadoregistros").hide();
    $("#formularioregistros").show();
    //$("#btnGuardar").prop("disabled",false);
    $("#btnagregar").hide();
    listarArticulos();

    //$("#guardar").hide();
    $("#btnGuardar").hide();
    $("#btnCancelar").show();
    detalles = 0;
    $("#btnAgregarArt").show();
  } else {
    $("#listadoregistros").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();
  }
}

function cancelarform() {
  limpiar();
  $("#btnGuardar").hide();
  $("#idmensaje").hide();
  //mostrarform(false);
  $(location).attr("href", "escritorio.php");
}



//Función Listar
function listar() {
  tabla = $("#tbllistado")
    .dataTable({
      lengthMenu: [5, 10, 25, 75, 100], //mostramos el menú de registros a revisar
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "<Bl<f>rtip>", //Definimos los elementos del control de tabla
      buttons: ["copyHtml5", "excelHtml5", "csvHtml5", "pdf"],
      ajax: {
        url: "../ajax/ingreso.php?op=listar",
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      language: {
        lengthMenu: "Mostrar : _MENU_ registros",
        buttons: {
          copyTitle: "Tabla Copiada",
          copySuccess: {
            _: "%d líneas copiadas",
            1: "1 línea copiada",
          },
        },
      },
      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[0, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();
}

//Función ListarArticulos
function listarArticulos() {
  tabla = $("#tblarticulos")
    .removeAttr("width")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [],
      columnDefs: [
        { width: "10%", targets: 0 },
        { width: "10%", targets: 1 },
        { width: "60%", targets: 2 },
        { width: "20%", targets: 3 },
      ],
      ajax: {
        url: "../ajax/ingreso.php?op=listarArticulos",
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

//Función para guardar o editar
function guardaryeditar(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento

  idalmacen_destino = $('#idalmacen').val();
  console.log(idalmacen_destino);

  if(idalmacen_destino != 0){
      
      var formData = new FormData($("#formulario")[0]);

      $.ajax({
        url: "../ajax/ingreso.php?op=guardaryeditar",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,

        success: function (datos) {

          $("#mensaje_final").css("color", "green");
          $("#mensaje_final").text(datos);

          msg = datos.substring(0,20);
        
          if(msg == "Ingreso registrado!!"){
            setTimeout(()=> {
              $(location).attr("href", "escritorio.php");
           }
           ,3000);
  
          }
          
        },
      });
      limpiar();

  }else{

      alert('Debe Elegir Almacen de Destino');

  }


}

function mostrar(idingreso) {
  //console.log("idingreso:" + idingreso);
  $.post(
    "../ajax/ingreso.php?op=mostrar",
    { idingreso: idingreso },
    function (data, status) {
      data = JSON.parse(data);
      mostrarform(true);

      $("#idproveedor").val(data.idproveedor);
      $("#idproveedor").selectpicker("refresh");
      $("#tipo_comprobante").val(data.tipo_comprobante);
      $("#tipo_comprobante").selectpicker("refresh");
      $("#num_comprobante").val(data.num_comprobante);
      $("#fecha_registro").val(data.fecha);
      $("#idingreso").val(data.idingreso);
      $("#idalmacen").val(data.idalmacen);
      $("#idalmacen").selectpicker("refresh");
      //console.log(data);

      $("#monto").val(data.total_compra);

      //Ocultar y mostrar los botones
      $("#btnGuardar").hide();
      $("#btnCancelar").show();
      $("#btnAgregarArt").hide();
    }
  );

  $.post("../ajax/ingreso.php?op=listarDetalle&id=" + idingreso, function (r) {
    $("#detalles").html(r);
  });
}

//Función para anular registros
function anular(idingreso) {
  bootbox.confirm("¿Está Seguro de anular el ingreso?", function (result) {
    if (result) {
      $.post(
        "../ajax/ingreso.php?op=anular",
        { idingreso: idingreso },
        function (e) {
          bootbox.alert(e);
          tabla.ajax.reload();
        }
      );
    }
  });
}

//Declaración de variables necesarias para trabajar con las compras y
//sus detalles
var impuesto = 18;
var cont = 0;
var detalles = 0;
//$("#guardar").hide();
$("#btnGuardar").hide();
$("#tipo_comprobante").change(marcarImpuesto);

function marcarImpuesto() {
  var tipo_comprobante = $("#tipo_comprobante option:selected").text();
  if (tipo_comprobante == "Factura") {
    $("#impuesto").val(impuesto);
  } else {
    $("#impuesto").val("0");
  }
}

function agregarDetalle(idarticulo, articulo, lote,idcategoria,categoria) {
  var cantidad = 1;
  var precio_compra = 1;
  var numero_lote = "";

  var now = new Date();
  var day = ("0" + now.getDate()).slice(-2);
  var month = ("0" + (now.getMonth() + 1)).slice(-2);
  var fecha_vencimiento = now.getFullYear() + "-" + month + "-" + day;

  //console.log('CATEGORIA:' +categoria);

  if (idarticulo != "") {
    $.post(
      "../ajax/articulo.php?op=lotes",
      { codigoArticulo: idarticulo },
      function (r) {
        //console.log("RR:" + r);
        num_lote = parseInt("0");
        num_lote = parseInt(r) + 1;
        $("#num_lot").val(num_lote);
        numero_lote = "LOTE " + num_lote;
        //console.log("NUM LOTE33: " + numero_lote);

        if(lote != 'tYES'){
          fecha_vencimiento = '2999-12-31';
          numero_lote = "No maneja Lotes!!";
        }

      var fila =
        '<tr class="filas" id="fila' +
        cont +
        '">' +
        '<td><button type="button" class="btn btn-danger" onclick="eliminarDetalle(' +cont +')">X</button></td>' +
        '<td><input type="hidden" name="idarticulo[]" value="' + idarticulo +'">' + articulo + "</td>" +
        '<td><input type="hidden" name="idcategoria[]" value="' + idcategoria +'">' + categoria + "</td>" +
        '<td><input type="number" name="cantidad[]" id="cantidad[]" tabindex="1" value="' + cantidad + '"></td>' +
        '<td><input type="text" name="precio_compra[]" id="precio_compra[]" tabindex="1" value="' + precio_compra +'"></td>' +
        '<td><input type="text" name="numero_lote[]" id="numero_lote[]" value="' + numero_lote + '"></td>' +
        '<td><input type="date" name="fecha_vencimiento[]" id="fecha_vencimiento[]" tabindex="1" value="' + fecha_vencimiento +'"></td>';
        $("#detalles").append(fila);
      }
    );
        
    cont++;
    detalles = detalles + 1;
    //console.log(fila);
    
    $("#btnGuardar").show();
  } else {
    alert("Error al ingresar el detalle, revisar los datos del artículo");
  }
}

function eliminarDetalle(indice){
  $("#fila"+indice).remove();
  detalles = detalles - 1;
  if(detalles == 0){
    $("#btnGuardar").hide();
  }
}
init();
