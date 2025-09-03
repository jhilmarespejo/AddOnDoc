function init(){

	//limpiar();
	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});

	$('#vamos_a_cobrar').hide();
	$('#id_genera_factura').hide();
	$('#id_imprime_recibo').hide();

	$('#listadoregistros').hide();
	

	$('#btnImprimir').attr('disabled',true);
	

	$(document).ready(function()
	{
		$("input[name=cobra]").click(function () {    
			//alert("La edad seleccionada es: " + $('input:radio[name=edad]:checked').val());
			//alert("La edad seleccionada es: " + $(this).val());
			console.log('ELEGIDO: ' + $(this).val());
		});
	});

}
function buscaClienteConDeuda() {

	let cedula = $('#num_documento').val();
	let codigo_agencia = $('#codigo_agencia').val();

	console.log('CEDULAX:'+cedula);
	console.log('Cod AgenciaX:'+codigo_agencia);
  
	tabla = $("#tbllistado")
	  .dataTable({
		aProcessing: true, //Activamos el procesamiento del datatables
		aServerSide: true, //Paginación y filtrado realizados por el servidor
		dom: "Bfrtip", //Definimos los elementos del control de tabla
		buttons: ["copyHtml5", "excelHtml5", "csvHtml5", "pdf"],
		ajax: {
		  url: '../ajax/cobranzas.php?op=buscaClienteConDeuda',
		  data: {
			cedula: cedula,
			codigo_agencia: codigo_agencia
		  },
		  type: "post",
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


	$('#formularioregistros').hide();
	$('#listadoregistros').show();
	//$('#vamos_a_cobrar').show();
	//buscarDatosCobranzas(cedula,codigo_agencia);
}

function buscarDatosCobranzas(cedula,codigo_agencia,id){

//Buscamos al Cliente  
	console.log('buscarDatosCobranzas');
	console.log('cedula: ' + cedula); 
	console.log('codigo_agencia: ' + codigo_agencia);
	console.log('Reg Facturar: ' + id);


	$.ajax({
		type:'POST',
		url:'../ajax/cobranzas.php?op=buscarDatosCobranzas',
		dataType: "json",
		async: false,
		data:{cedula: cedula, codigo_agencia:codigo_agencia, id: id},
		success:function(data){
			
			//data = JSON.parse(datos)
			console.log(data);
			console.log('STATUS: ' + data['status']);
			console.log('NOMBRE: ' + data['nombre']);
			console.log('DEUDA: ' + data['deuda']);
			
			if(data['status'] == 'ok'){
				deuda = parseInt(data.deuda);
				$('#nombre_pac').val(data.nombre);
				$('#monto_a_pagar1').val(deuda);
				$('#monto_a_pagar2').val(data.deuda);
				$('#total_paid').focus();
				$('#id_cambio').css("color", "red");
				$('#registro_a_facturar').val(id);


			}else{
				encontrado = 'NO';
				console.log(data);
				$('#encontrado').val(encontrado);

			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			alert("Status: " + textStatus); alert("Error: " + errorThrown); 
		}    
		
	});

	
	
}


function limpiar(){

	$('#ap_paterno').val('');
	$('#ap_materno').val('');
	$('#nombres').val('');
	$('#fecha_nacimiento').val('');
	$('#genero').val('');
	$('#telefono').val('');

	$('#ap_paterno_ben').val('');
	$('#ap_materno_ben').val('');
	$('#nombres_ben').val('');
	$('#fecha_nacimiento_ben').val('');
	$('#genero_ben').val('');
	$('#telefono_ben').val('');

}



function generarFactura(codigo_agencia){

	let cedula = $('#num_documento').val();
	let nombre = $('#nombre_pac').val();
	let precio = $('#monto_a_pagar1').val();
	let registro_a_facturar = $('#registro_a_facturar').val();

	console.log("CUST CI: " + cedula);
	console.log("CUSTOMER: " + nombre);
	console.log("PRECIO: " + precio);
	console.log("COD AGENCIA: " + codigo_agencia);
	console.log("ID REGISTRO: " + registro_a_facturar);


	$('#registro_a_imprimir').val(registro_a_facturar);

 	//$("id_genera_factura").css("cursor", "progress");
	document.body.style.cursor = 'wait';


	$.ajax({
        url: "../ajax/cobranzas.php?op=generarFactura",
        type: "POST", 
        dataType: "json",
		async: false,
		data:{cedula: cedula, precio: precio, codigo_agencia: codigo_agencia, registro_a_facturar: registro_a_facturar},

        success: function (datos) {

			console.log('Status Fact: ' + datos);
			console.log('Status Fact: ' + datos.status_fact);
			//datos.status_fact = 'E';

			
			if(datos.status_fact == 'ok'){
				$('#btnImprimir').attr('disabled',false);
				$('#btnCancelar').attr('disabled',true);
				$('#num_documento').val('');
				$('#total_paid').val('');

				////------------------
				//$.post("../reportes/muestra_recibo.php", {id_registro_a_facturar : registro_a_facturar}, function(e){
				//	console.log(e);
				//});	
				//-----------------

			}else{
				alert('Ocurrió un error al generar la Factura\nMensaje: ' + datos.mensaje_fact + '!');
				//$(location).attr("href", "escritorio.php");
			}

        },
    });

	//$("id_genera_factura").css("cursor", "default");
	document.body.style.cursor = 'default';

	


}


function registroSeleccionado(id, cod_plan){

	//let mvar = $this.val();

	console.log('QUIEN: ' + id);
	console.log('COD PLAN: ' + cod_plan);

	let cedula = $('#num_documento').val();
	let codigo_agencia = $('#codigo_agencia').val();

	console.log('CEDULAZ:'+cedula);
	console.log('Cod AgenciaZ:'+codigo_agencia);
  
	//$('#formularioregistros').hide();
	$('#vamos_a_cobrar').show();
	buscarDatosCobranzas(cedula,codigo_agencia,id);


}

function muestraDatos(){

	console.log('muestraDatos');
	
}

init();
