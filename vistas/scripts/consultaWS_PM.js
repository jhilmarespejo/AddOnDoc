var tabla;

//Función que se ejecuta al inicio
function init() {

	$('#idmensaje').hide();
	$('#datos_titular').hide();
	$('#volver_a_buscar_d').hide();


}


// Buscamos al PACIENTE x medio de su Cédula
function buscaPaciente(){

	let cedula;
	num_documento = $('#num_documento').val();
	tipo_documento = $('#tipo_documento').val();

	console.log('CEDULA:'+num_documento);
	console.log('TIP DOC:' + tipo_documento);

	$('#cliente_no_encontrado').val('');

	$('#btnAgregarArt').prop('disabled',true);

	limpiar();
	//Buscamos al Cliente
	$.ajax({
		type:'POST',
		url:'../ajax/cliente.php?op=verificar_cliente_en_PM',
		dataType: "json",
		async: false,
		data:{num_documento: num_documento, tipo_documento:tipo_documento},
		success:function(data){

			$('#volver_a_buscar_d').show();
			console.log(data);

			if(data.status == 'ok'){

				ap_pat = data.ap_paterno;
				ap_mat = data.ap_materno;
				f_name = data.nombres;
				c_exte = data.extension;
				s_phon = data.telefono;
				f_gene = data.genero;
				f_fecn = data.fecha_nacimiento;
				tipdoc = data.tipo_documento;
				donde  = data.donde;

				nu_ced = data.num_documento;
				tipdoc = tipdoc!==null ? data.tipo_documento : '';
				c_exte = c_exte!==null ? data.extension : " ";
				ap_pat = ap_pat!==null ? data.ap_paterno : " ";
				ap_mat = ap_mat!==null ? data.ap_materno : " ";
				f_name = f_name!==null ? data.nombres : " ";
				f_gene = f_gene!==null ? data.genero : " ";
				s_phon = s_phon!==null ? data.telefono : " ";
				f_fecn = f_fecn!==null ? data.fecha_nacimiento : " ";
				donde  = donde!==null ? data.donde : " ";

				$('#datos_titular').show();
				//$('#idmensaje').show();
				mensajeYES = '<strong>Cliente encontrado!!</strong>';
				$('#cliente_no_encontrado').html(mensajeYES);

				limpiar();
				$('#donde').val(donde);
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
				//$('#id_cliente').val(data.id);
				$('#tipo_documento').val(tipdoc);
				$('#tipo_documento').selectpicker("refresh");
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

			/*
				text = 'Esta seguro del número de esta cédula?\nCEDULA: ' + cedula;
				if (confirm(text) === true) {
					text = "You pressed OK!";
				} else {
					$(location).attr("href", "admision.php");
				}
			*/
				//$('#idmensaje').show();
				//$('#datos_titular').hide();

			}



		}

	});


}


function volver_a_buscar(){


	$('#btnAgregarArt').prop('disabled',false);
	$(location).attr("href", "consultaWS_PM.php");


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


init();
