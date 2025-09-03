//Función que se ejecuta al inicio
function init(){

    $('#canal_inhabilitado').hide();
	mostrarform(true);


}

function mostrarform(flag)
{
	if (flag)
	{
		$("#listadoregistros").hide();
		$("#mensajedeerror").hide();
		$("#formularioregistros").show();
		$('#canal_inhabilitado').hide();
	}
	else
	{
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
	
}

function envia_correo(){

	let correo = $('#correo').val();

	console.log('Correo: ' + correo);

	$.post("../ajax/forgot_password.php?op=recibecorreo",
		{correo : correo}, 
		function(data){

			data = JSON.parse(data);		
			console.log(data);
			if(data['status'] == 'ok'){
				console.log(data['status']);
				console.log(data['mensaje']);
				mostrarform(false);

			}
			msg = data['mensaje'].substring(0,9)
			if(data['status'] == 'Error' && msg == 'No existe'){
			//if(data['status'] == 'Error'){
				console.log(data['mensaje']);
				$('#listadoregistros').hide();
				$('#formularioregistros').hide();
				$('#mensajedeerror').show();
			}

			msg = data['mensaje'].substring(0,7);
			if(data['status'] === 'Error' && msg === 'Funcion'){
				console.log('MENSAJE: '+data['mensaje']);
				$('#listadoregistros').hide();
				$('#formularioregistros').hide();
				$('#canal_inhabilitado').show();

			}

 	})
}


function limpiar(){

	$("#correo").val("");

}



init();