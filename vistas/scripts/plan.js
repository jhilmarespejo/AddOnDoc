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

function guardaryeditar(e)
{
	e.preventDefault(); //No se activará la acción predeterminada del evento
	$("#btnGuardar").prop("disabled",true);
	var formData = new FormData($("#formulario")[0]);
	
	// console.log('ID ID CANAL:' + formData);

	$.ajax({
		url: "../ajax/plan.php?op=guardaryeditar",
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
	$("#codigo_plan").val("");
	$("#plan").val("");
	$("#contrato").val("");
 	$("#plan").val("");
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
				url: '../ajax/plan.php?op=listar',
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
	console.log("ID PLAN:"+id);
	
	$.post("../ajax/plan.php?op=mostrar",{id : id}, function(data)
	{
		data = JSON.parse(data);
		console.log(data);
		mostrarform(true);
		
		var canal;
		canal = data.canal + " - " + data.nombre_canal;
		$("#id").val(data.id);
		$("#codigo_plan").val(data.codigo_plan);
		$("#plan").val(data.plan);
		$("#contrato").val(data.contrato);
		$("#canal").val(canal);
		$("#estado").val(data.estado);

 	})
}

function cancelarform() {
  limpiar();
  mostrarform(false);
}

//Función para desactivar registros
function desactivar(id)
{
	bootbox.confirm("¿Está Seguro de desactivar el Plan?", function(result){
		if(result)
        {
        	$.post("../ajax/plan.php?op=desactivar", {id : id}, function(e){
        		bootbox.alert(e);
	            tabla.ajax.reload();
        	});	
        }
	})
}

//Función para activar registros
function activar(id)
{
	bootbox.confirm("¿Está Seguro de activar el Plan?", function(result){
		if(result)
        {
        	$.post("../ajax/plan.php?op=activar", {id : id}, function(e){
        		bootbox.alert(e);
	            tabla.ajax.reload();
        	});	
        }
	})
}


init();