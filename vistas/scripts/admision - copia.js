let valorSeleccionado; 
let respuesta;

function init(){

	limpiar();
	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});

	// Cargamos los planes Activos del Canal
	$.post("../ajax/varios.php?op=listarPlanesCanal", function (r) {
		$("#planes").html(r);
		$("#planes").selectpicker("refresh");
	});

	con_beneficiario = 'NO';
	$('#plan_con_ben').val(con_beneficiario);

	$('#datos_titular').hide();
	$('#formularioregistros').show();
	$('#registra_beneficiario').hide();

	$('#idmensaje').hide();
	$('#idmensaje_final').hide();

	$('#planes').change(function() {
		// Obtener el valor de la opción seleccionada
		valorSeleccionado = $(this).val();

		// Hacer algo con el valor seleccionado
		console.log("Plan Elegido: " + valorSeleccionado);

		let cedula = $('#cedula').val();
		$.post("../ajax/varios.php?op=buscaClienteAntiguo", 
			{cedula : cedula, valorSeleccionado:valorSeleccionado}, 
			function(encontrado){
				if(encontrado){

				}
				bootbox.alert(e);
				tabla.ajax.reload();
		});

	  });

	$(document).ready(function () {
		$("#planesXX").change(function () {
			$("#planes option:selected").each(function () {

				plan_elegido = $(this).val();
				console.log("PLAN ELEGIDO:" + plan_elegido);

		  	});
		});
	});

}



function handleChange(checkbox){

	if(checkbox.checked === true){
		console.log('Mostrando Titular . . . . . .');
		igual = 'NO';
        $('#despliega_titular').show();
    }else{
		igual = 'SI';
		console.log('Ocultando Titular');
        $('#despliega_titular').hide();
   }
   $('#tit_idem_con').val(igual);

}


// Buscamos al PACIENTE x medio de su Cédula
function buscaPaciente(flag){

	let cedula;


	cedula = $('#num_documento').val();

	console.log('FLAG:'+flag);
	console.log('CEDULA:'+cedula);


	//Buscamos al Cliente
	$.ajax({
		type:'POST',
		url:'../ajax/buscarCliente.php?op=buscarCliente',
		dataType: "json",
		async: false,
		data:{cedula: cedula},
		success:function(data){

			console.log(data);
			if(data.status == 'ok'){

				ap_pat = data.result.ap_paterno;
				ap_mat = data.result.ap_materno;
				f_name = data.result.nombres;
				c_exte = data.result.extension;
				s_phon = data.result.telefono;
				f_gene = data.result.genero;
				f_fecn = data.result.fecha_nacimiento;
				f_plan = data.result.plan;
				tipdoc = data.result.tipo_documento;

				nu_ced = data.result.num_documento;
				tipdoc = tipdoc!==null ? data.result.tipo_documento : '';
				c_exte = c_exte!==null ? data.result.extension : " ";
				ap_pat = ap_pat!==null ? data.result.ap_paterno : " ";
				ap_mat = ap_mat!==null ? data.result.ap_materno : " ";
				f_name = f_name!==null ? data.result.nombres : " ";
				f_gene = f_gene!==null ? data.result.genero : " ";
				s_phon = s_phon!==null ? data.result.telefono : " ";
				f_fecn = f_fecn!==null ? data.result.fecha_nacimiento : " ";
				f_plan = f_plan!==null ? data.result.planes : " ";
				encontrado = 'SI';
				mensaje = 'Cliente encontrado. Completando sus datos!!';

				limpiar();
				$('#num_documento').val(nu_ced);
				$('#extension').val(c_exte);
				$('#ap_paterno').val(ap_pat);
				$('#ap_materno').val(ap_mat);
				$('#nombres').val(f_name);
				$('#genero').val(f_gene);
				$("#genero").selectpicker("refresh");
				$('#telefono').val(s_phon);
				$('#fecha_nacimiento').val(f_fecn);
				$('#encontrado').val(encontrado);
				$('#id_cliente').val(data.result.id);
				$('#tipo_documento').val(tipdoc);
				$('#tipo_documento').selectpicker("refresh");
				$('#datos_titular').show();


			}else{
				encontrado = 'NO';
				mensaje = 'Cliente no encontrado. Favor ingrese sus datos!';
				console.log(data);
				$('#encontrado').val(encontrado);

				$("#idmensaje").css("border-style", "solid");
				$("#idmensaje").css("border-width", "1px");
				$("#idmensaje").css("color", "red");
				$("#idmensaje").css("border-color", "black");
				$("#idmensaje").css("padding", "3px");
				$("#idmensaje").text('Cliente no encontrado. Favor ingrese sus datos!');

				text = 'Esta seguro del número de esta cédula?\nCEDULA: ' + cedula;
				if (confirm(text) === true) {
					text = "You pressed OK!";
				} else {
					$(location).attr("href", "admision.php");
				}

				$('#idmensaje').show();
				$('#datos_titular').show();

			}

		}
			
	});


}

function limpiar(){

	$('#nombre_usuario').val('');
	$('#cargo').val('');
	$('#id_role').val('');
	$('#id_agencia').val('');
	$('#tipo_documento').val('');
	$('#numero_documento').val('');
	$('#extension').val('');
	$('#expedido').val('');
	$('#login').val('');
	$('#clave').val('');
	$('#telefono').val('');
	$('#id_condicion').val('');

}



function guarda_info(){

	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/cliente.php?op=guardarContratante",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {

			console.log(datos);				
			data = JSON.parse(datos)

			if(data.status == 'ok'){
				$('#idmensaje_final').show();
				$("#mensaje_final").css("color", "green");
				$("#mensaje_final").css("border-color", "black");
				$("#mensaje_final").css("padding", "3px");
				$("#mensaje_final").text('Cliente registrado de manera satisfactoria!');
				$("#mensaje_final").css("font-weight", "bold");

				setTimeout(()=> {
					$(location).attr("href", "admision.php");
				}
				,1500);
			}else{
				console.log('BAAAD!');
				alert('Ocurrió un error al guardar el beneficiario');
				$(location).attr("href", "escritorio.php");
			}


		},
	});

}


function guardaryeditar(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento

	var debug;
	debug = 0;

	let planes = $('#planes').val();
	let telefono = $('#telefono').val();
    let len_tel = telefono.length;
	let generoCliente = $('#genero').val();
    
	console.log('PLANES: '+planes);


	if(planes === '0'){
		alert('Debe elegir un plan!!');
	}else{
	
		console.log(telefono);
	    if(len_tel < 7 || len_tel > 8){
	        alert('Cantidad de digitos del teléfono debe contener 7 u 8 digítos');
	    }else{
	        
    		//this.disabled = true;
    		$('#btnGuardar').prop('disabled', true);
    
			console.log("Pidiendo Genero del Plan");
    
			$.post(
				'../ajax/varios.php?op=obtieneGeneroDelPlan',
				{valorSeleccionado:valorSeleccionado},
				function (generoPlan) {
				  	console.log("GENERO PLAN:" + generoPlan);
					console.log("GENERO CLIENTE: " + generoCliente);
				  	console.log("Procediendo a GUARDAR la INFORMACION");
		
					if(generoPlan === generoCliente){

						console.log("TRATANDO DE GUARDAR LA INFO");

						guarda_info();

						//alert("Genero Correcto");
						$('#respuesta_c').val("Genero Correcto");
					}else{

						let text = "El género del cliente es diferente al tipo de Plan\nPresione OK para grabar o Cancel para modificar.";
						if (confirm(text) == true) {
							text = "You pressed OK!";
							guarda_info();
						} else {
							text = "You canceled!";
							$('#btnGuardar').prop('disabled', false);
						}
						//alert("Cruce de Generos");
						console.log("Cruce de Generos");
						console.log(text);
						//$('#respuesta_c').val("Cruce de Generos");
						
					}
		
				}
			);




    		
	    }
    		
	}



}



init();
