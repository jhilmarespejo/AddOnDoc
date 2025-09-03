var tabla;

//Función que se ejecuta al inicio
function init(){
	mostrarform(false);

	listar();

	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);	
	});
    // $('#mAlmacen').addClass("treeview active");
    // $('#lCategorias').addClass("active");
}

function mostrarform(flag)
{
	if (flag)
	{
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled",false);
		$("#btnagregar").hide();
	}
	else
	{
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
	
}

function cancelarform() {
  limpiar();
  mostrarform(false);
}

function guardaryeditar(e)
{
	e.preventDefault(); //No se activará la acción predeterminada del evento
	$("#btnGuardar").prop("disabled",true);
	var formData = new FormData($("#formulario")[0]);
	
	// console.log('ID ID CANAL:' + formData);

	$.ajax({
		url: "../ajax/canal.php?op=guardaryeditar",
	    type: "POST",
	    data: formData,
	    contentType: false,
	    processData: false,

	    success: function(datos)
	    {                    
	          bootbox.alert(datos);	          
	          mostrarform(false);
	          tabla.ajax.reload();
	    }

	});
	limpiar();
}


function limpiar(){

	$("#id").val("");
	$("#id_canal").val("");
	$("#nombre_canal").val("");
	$("#comision").val("");
 	$("#estado").val("");
	
}

//Función Listar
function listar()
{
	tabla=$('#tbllistado').dataTable(
	{
		"lengthMenu": [ 5, 10, 25, 75, 100],//mostramos el menú de registros a revisar
		"aProcessing": true,//Activamos el procesamiento del datatables
	    "aServerSide": true,//Paginación y filtrado realizados por el servidor
	    dom: '<Bl<f>rtip>',//Definimos los elementos del control de tabla
	    buttons: [		          
		     
		        ],
		"ajax":
				{
					url: '../ajax/canal.php?op=listar',
					type : "get",
					dataType : "json",						
					error: function(e){
						console.log(e.responseText);	
					}
				},
		"language": {
            "lengthMenu": "Mostrar : _MENU_ registros",
            "buttons": {
            "copyTitle": "Tabla Copiada",
            "copySuccess": {
                    _: '%d líneas copiadas',
                    1: '1 línea copiada'
                }
            }
        },
		"bDestroy": true,
		"iDisplayLength": 5,//Paginación
	    "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
	}).DataTable();
}
//Función mostrar
function mostrar(id)
{
	//console.log("ID CANAL:"+id);
	
	$.post("../ajax/canal.php?op=mostrar",{id : id}, function(data)
	{
		data = JSON.parse(data);		
		// console.log(data);
		mostrarform(true);

		$("#id").val(data.id);
		$("#id_canal").val(data.id_canal);
		$("#nombre_canal").val(data.nombre_canal);
		$("#comision").val(data.comision);
 		$("#estado").val(data.estado);

 	})
}

//Función para desactivar registros
function desactivar(id)
{
	console.log('ID CANAL: '+id);
	bootbox.confirm("¿Está Seguro de desactivar el Canal?", function(result){
		if(result)
        {
        	$.post("../ajax/canal.php?op=desactivar", {id : id}, function(e){
        		bootbox.alert(e);
	            tabla.ajax.reload();
        	});	
        }
	})
}

//Función para activar registros
function activar(id)
{
	bootbox.confirm("¿Está Seguro de activar el Canal?", function(result){
		if(result)
        {
        	$.post("../ajax/canal.php?op=activar", {id : id}, function(e){
        		bootbox.alert(e);
	            tabla.ajax.reload();
        	});	
        }
	})
}


init();