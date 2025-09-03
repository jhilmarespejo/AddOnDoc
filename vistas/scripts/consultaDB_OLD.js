var tabla;

//Función que se ejecuta al inicio
function init() {

	$('#idmensaje').hide();
	$('#datos_titular').hide();
	$('#volver_a_buscar_d').hide();


}


// Buscamos al PACIENTE x medio de su Cédula
function buscaPaciente(){


	let num_documento = $('#num_documento').val();
	let tipo_documento = $('#tipo_documento').val();

	console.log('CEDULA:'+num_documento);
	console.log('TIP DOC: '+tipo_documento);

	$('#cliente_no_encontrado').val('');

	$('#btnAgregarArt').prop('disabled',true);

	limpiar();
	//Buscamos al Cliente
	$.ajax({
		type:'POST',
		url:'../ajax/cliente.php?op=consulta_clientes_antiguos',
		dataType: "json",
		async: false,
		data:{num_documento: num_documento, tipo_documento: tipo_documento},
		success:function(data){

			$('#volver_a_buscar_d').show();
			console.log(data);

			if(data.status == 'ok'){

				c_name = data.nombre;
				c_cedu = data.cedula;
				n_plan = data.plan;
				c_plan = data.codigo_plan;
				f_finn = data.fecha_fin;
				f_able = data.habilitado;

				$('#datos_titular').show();
				//$('#idmensaje').show();
				mensajeYES = '<strong>Cliente encontrado!!</strong>';
				$('#cliente_no_encontrado').html(mensajeYES);

				limpiar();
				$('#numero_cedula').val(c_cedu);
				$('#nombre_cliente').val(c_name);
				$('#codigo_plan').val(c_plan);
				$('#nombre_plan').val(n_plan);
				$('#fecha_fin').val(f_finn);
				if(f_able === 'S'){
					estado_pac = 'HABILITADO';
					$("#status_pac").css("color", "green");
				}else{
					estado_pac = 'NO HABILITADO';
					$("#status_pac").css("color", "red");
				}
				console.log('HABILITADO: ' + f_able);
				$('#status_pac').val(estado_pac);
				$('#status_pac').css("font-weight", "bold");
				$('#datos_titular').show();


			}else{
				encontrado = 'NO';
				mensaje = 'Cliente no encontrado!';
				console.log(data);
				$('#encontrado').html(encontrado);

				$("#idmensaje").css("border-style", "solid");
				$("#idmensaje").css("border-width", "1px");
				$("#idmensaje").css("color", "red");
				$("#idmensaje").css("border-color", "black");
				$("#idmensaje").css("padding", "3px");
				$("#idmensaje").text('Cliente no encontrado!');
				$("#idmensaje").show();

				$('#datos_titular').hide();
				//alert(mensaje);
				$('#volver_a_buscar_d').show();

			}



		}

	});


}


function volver_a_buscar(){


	$('#btnAgregarArt').prop('disabled',false);
	$(location).attr("href", "consultaClientesAntiguos.php");


}


function limpiar(){

	$('#nombre_cliente').val('');
	$('#fecha_fin').val('');
	$('#numero_cedula').val('');
	$('#codigo_plan').val('');
	$('#nombre_plan').val('');


}


init();
