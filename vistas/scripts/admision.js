let valorSeleccionado; 
let respuesta;
let planes_cargados = 'NO';

function init(){

	// Limpiamos la pantalla de datos anteriores //
	limpiar();

	// Registramos el evento para registro de datos **
	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});

	let datos_asesor = $('#datos_asesor').val();
	if(datos_asesor == 'NO'){
		$('.datos_asesor').hide();
	}
	console.log('Datos Asesor: ' + datos_asesor);
	
	// No mostramos la seccion del contratante      //
	// hasta que se sepa si el cliente existe o no. //
	$('#datos_titular').hide();


	// Mostramos la pantalla principal de la admision //
	$('#formularioregistros').show();
	$('#registra_beneficiario').hide();

	$('#idmensaje').hide();
	$('#idmensaje_final').hide();

	// Esto lo ponemos por si acaso el genero haya estado   //
	// errado y se cambie. Entonces se debe volver a cargar //
	// la lista en funcion al nuevo género.                 //
	$('#genero').change(function() {

		listarPlanes();

	});

	$('#planes').change(function() {
		// Obtener el valor de la opción seleccionada
		valorSeleccionado = $(this).val();

		// Hacer algo con el valor seleccionado
		console.log("Plan Elegido: " + valorSeleccionado);

		let cedula = $('#num_documento').val();
		let codigo_canal = $('#codigo_canal').val();

	});

}

// ------------------------------------------------------------------- //
// Lista los planes tomando en cuenta el Canal y el género del cliente //
// ------------------------------------------------------------------- //
function listarPlanes(){

	$("#genero option:selected").each(function () {

		genero_paciente = $(this).val();
		console.log("GENERO CLIENTE:" + genero_paciente);

		$.post("../ajax/varios.php?op=listarPlanesCanal", 
		{genero_paciente:genero_paciente},
		function (r) {
			$("#planes").html(r);
			$("#planes").selectpicker("refresh");
		});
	
	});
	
}

// ----------------------------------------- //
// Buscamos al PACIENTE x medio de su Cédula //
// ----------------------------------------- //
function buscaPaciente(){

	let cedula;
	let extension;
        cedula = $('#num_documento').val();
        extension = $('#extension').val();

        console.log('CEDULA:'+cedula);
        console.log('EXTENSION',extension);

        //Buscamos al Cliente
        $.ajax({
                type:'POST',
                url:'../ajax/buscarCliente.php',
                dataType: "json",
                async: false,
                data:{cedula: cedula,extension:extension},
				success:function(data){

			if(data.status == 'ok'){

				nombres = data.result.nombre1;
				if (data.result.nombre2) {
					nombres += ' ' + data.result.nombre2;
				}
				console.log(nombres);

				ap_pat = data.result.paterno;
				ap_mat = data.result.materno;
				num_pres = data.result.numPrestamo;
				// f_name = data.result.nombres;
				f_name = nombres;

				c_exte = data.result.extension;
				s_phon = data.result.celular;
				f_gene = data.result.genero;
				f_fecn = data.result.fechaNac;
				//f_plan = data.result.plan;
				tipdoc = data.result.tipoDoc;

				nu_ced = data.result.documento;
				tipdoc = tipdoc!==null ? data.result.tipoDoc : '';
				c_exte = c_exte!==null ? data.result.extension : " ";
				ap_pat = ap_pat!==null ? data.result.paterno : " ";
				ap_mat = ap_mat!==null ? data.result.materno : " ";
				f_name = f_name!==null ? nombres : " ";
				f_gene = f_gene!==null ? data.result.genero : " ";
				s_phon = s_phon!==null ? data.result.celular : " ";
				f_fecn = f_fecn!==null ? data.result.fechaNac : " ";
				num_pres = num_pres!==null ? data.result.numPrestamo : " ";
				//f_plan = f_plan!==null ? data.result.planes : " ";
				if(data.donde === 'I'){ // Se encuentra en tablas locales
				    encontrado = 'SI';
				}else{                  // Se lo obtuvo desde PM
				    encontrado = 'NO';
				}
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
				$('#numero_prestamo').val(num_pres);
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

			listarPlanes();

		}
			
	});


}

function limpiar(){

	$('#ap_paterno').val('');
	$('#ap_materno').val('');
	$('#nombres').val('');
	$('#fecha_nacimiento').val('');
	$('#genero').val('');
	$('#genero').selectpicker("refresh");
	$('#telefono').val('');
	$('#planes').val('');
	$('#planes').selectpicker("refresh");
	$('#cedula_asesor').val('');


}



function guarda_info(){

	var formData = new FormData($("#formulario")[0]);

/*
	let a1 = $('#num_documento').val();
	let a2 = $('#ap_paterno').val();
	let a3 = $('#ap_materno').val();
	let a4 = $('#nombres').val();
	let a5 = $('#genero').val();
	let a6 = $('#telefono').val();
	let a7 = $('#fecha_nacimiento').val();
	let a8 = $('#encontrado').val();
	let a9 = $('#id_cliente').val();
	let a10 = $('#tipo_documento').val();
	let a11 = $('#planes').val();
	

	console.log('AP PAT: ' + a2);
	console.log('AP MAT: ' + a3);
	console.log('NOMBRE: ' + a4);
	console.log('TP DOC: ' + a10);
	console.log('CEDULA: ' + a1);
	console.log('GENERO: ' + a5);
	console.log('FC NAC: ' + a7);
	console.log('FOUND : ' + a8);
	console.log('ID CLI: ' + a9);
	console.log('PHONE : ' + a6);
	console.log('PLAN  : ' + a11);
*/


	$.ajax({
		url: "../ajax/cliente.php?op=guardarContratante",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,


		success: function (datos) {

			data = JSON.parse(datos)
			console.log(data);		

			
			if(data.status == 'ok'){

				crearOV(data.numero_prestamo);
				
				$('#idmensaje_final').show();
				/*
				$("#mensaje_final").css("color", "green");
				$("#mensaje_final").css("border-color", "black");
				$("#mensaje_final").css("padding", "3px");
				// $("#mensaje_final").text('Cliente registrado de manera satisfactoria!');
				$("#mensaje_final").html(`
					Cliente registrado de manera satisfactoria! &nbsp;&nbsp;&nbsp; <button id="btnAceptar">Aceptar</button>
				`);
				$("#mensaje_final").css("font-weight", "bold");

				$('#nombre_pac').val(data.nombre);
				$('#monto_a_pagar1').val(data.deuda);
				$('#monto_a_pagar2').val(data.deuda);
				$('#id_pagado').css("color", "green");
				$('#id_total_a_pagar').css("color", "red");
				$('#registro_a_facturar').val(data.id_temp);

				// Asigna el evento al botón para redirigir
				$(document).on("click", "#btnAceptar", function() {
					window.location.href = "admision.php"; // Cambia por la URL a la que quieras redirigir
				});

				*/
				//$('#formularioregistros').hide();
				// setTimeout(()=> {
				//     limpiar();
				// 	$(location).attr("href", "admision.php");
				// }
				// ,1500);


			}else{
				console.log('BAAAD!');
				alert(data.message);
				$(location).attr("href", "escritorio.php");
			}

		},
	});

}

function crearOV(numeroPrestamo) {
    $.ajax({
        url: '../ajax/crea_ov.php',
        type: 'POST',
        dataType: 'json',
        data: { numero_prestamo: numeroPrestamo },

        beforeSend: function () {
            // Mostrar mensaje de procesamiento
            $("#mensaje_final").html(
                '<div class="alert alert-info" role="alert">' +
                ' Procesando creación del registro, por favor espere...' +
				
                '</div>'
            );
        },

        success: function (respuesta) {
            if (respuesta.status === 'ok') {
                $("#mensaje_final").html(
                    '<div class="alert alert-success" role="alert">' +
                	 respuesta.message +
					'<button id="btnAceptar" class="ml-5 btn btn-primary btn-sm" style="margin-left:25px;">' +
						'Aceptar' +
					'</button>'
					+
                    '</div>'
                );
				$(document).on("click", "#btnAceptar", function() {
					window.location.href = "admision.php"; // Cambia por la URL a la que quieras redirigir
				});
            } else {
                $("#mensaje_final").html(
                    '<div class="alert alert-danger" role="alert">' +
                    ' ' + respuesta.message +
                    '</div>'
                );
            }
        },

        error: function (xhr, status, error) {
            $("#mensaje_final").html(
                '<div class="alert alert-danger" role="alert">' +
                'Error en la comunicación con el servidor: ' + error +
                '</div>'
            );
        },

        complete: function () {
            // Aquí podrías hacer algo cuando termine, sin importar éxito o error
            console.log("Petición AJAX finalizada");
        }
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
			console.log('Plan Elegido GYE: ' + valorSeleccionado);
    
			$.post(
				'../ajax/varios.php?op=obtieneGeneroDelPlan',
				{valorSeleccionado:valorSeleccionado},
				function (generoPlan) {
				  	console.log("GENERO PLAN:" + generoPlan);
					console.log("GENERO CLIENTE: " + generoCliente);
				  	console.log("Procediendo a GUARDAR la INFORMACION");
		
					let codigo_canal = $('#codigo_canal').val();
					console.log('CODIGO DE CANAL xx: ' + codigo_canal);

					if(codigo_canal === 'C001'){ // PROMUJER

						if((generoPlan === generoCliente) || (generoPlan === 'X')){

							console.log("TRATANDO DE GUARDAR LA INFO");

							guarda_info();

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
			
					}else{   // CIDRE y OTROS

						guarda_info();
						//$('#vamos_a_cobrar').hide();

						// $('#idmensaje_final').show();
						// $("#mensaje_final").css("color", "green");
						// $("#mensaje_final").css("border-color", "black");
						// $("#mensaje_final").css("padding", "3px");
						// $("#mensaje_final").text('Cliente registrado de manera satisfactoria!');
						// $("#mensaje_final").css("font-weight", "bold");

						// setTimeout(()=> {
						// 	$(location).attr("href", "admision.php");
						// }
						// ,5000);

						// setTimeout(()=> {
						// 	$(location).attr("href", "admision.php");
						// }
						// ,10);

	
					}

				}

			);
    		
	    }
    		
	}

}

function generarFactura(){

	let cedula = $('#num_documento').val();
	let nombre = $('#nombre_pac').val();
	let precio = $('#monto_a_pagar1').val();
	let registro_a_facturar = $('#registro_a_facturar').val();

	console.log("CUST CI: " + cedula);
	console.log("CUSTOMER: " + nombre);
	console.log("PRECIO: " + precio);
	console.log("REG A Facturar: " + registro_a_facturar);


	$('#registro_a_imprimir').val(registro_a_facturar);


	//data:{cedula: cedula, precio: precio, codigo_agencia: codigo_agencia, registro_a_facturar: registro_a_facturar},

	$.ajax({
        url: "../ajax/cobranzas.php?op=generarFactura",
        type: "POST", 
        dataType: "json",
		async: false,
		data:{registro_a_facturar: registro_a_facturar},

        success: function (datos) {

			console.log('Status Fact: ' + datos);
			console.log('Status Fact: ' + datos.status_fact);
			//datos.status_fact = 'E';

			
			if(datos.status_fact == 'ok'){
				$('#btnImprimir').attr('disabled',false);
				$('#btnCancelar').attr('disabled',true);
				$('#num_documento').val('');
				$('#total_paid').val('');
				$('#cedula_asesor').val('');
				

				////------------------
				//$.post("../reportes/muestra_recibo.php", {id_registro_a_facturar : registro_a_facturar}, function(e){
				//	console.log(e);
				//});	
				//-----------------

			}else{
				console.log('TODO SALIO MALLLL!!!');
				alert('Ocurrió un error al generar la Factura\nMensaje: ' + datos.mensaje_fact + '!');
				//$(location).attr("href", "escritorio.php");
			}

        },
    });

	//$("id_genera_factura").css("cursor", "default");
	document.body.style.cursor = 'default';

}



init();
