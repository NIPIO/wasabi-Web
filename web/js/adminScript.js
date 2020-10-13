nombreEdicion = '';
function mostrarContenido(cuestion) {
	switch(cuestion) {
		case 'cat':
			$("#compras").hide(); $("#prod").hide(); variable = document.getElementById("cat").style.display = 'block';
			break;
		case 'prod':
			$("#cat").hide(); $("#compras").hide(); variable = document.getElementById("prod").style.display = 'block';
			break;
		case 'compras':
			$("#cat").hide(); $("#prod").hide(); variable = document.getElementById("compras").style.display = 'block';
			break;
		}
}

$("#nuevaCategoria").submit(function(e) {
    nombreCat = $("#nombreCat").val();
		$.ajax({
			url:"/nuevaCategoria",
			type: "POST",
			dataType: "json",
			data: {
			"nombreCat": nombreCat
			},
			async: true,
			success: function (data)
			{
			}
		});
		location.reload();

})

$("#nuevoProducto").submit(function(e) {
	e.preventDefault();
	var formData = new FormData($("#nuevoProducto")[0])
	formData.append('nombreProd',$("#npNombreProd").val());
	formData.append('nombreCat',$("#npNombreCat").val());
	formData.append('descripcion',$("#npDescripcion").val());
	formData.append('precio',$("#npPrecio").val());

	$.ajax({
		url:"/nuevoProducto",
		type: "POST",
		dataType: "json",
		data: formData,
		contentType: false,
		processData: false,
		error:function(err){
		},
		success:function(data){
		}
	})
	location.reload();

})

function abrirEdicionProd(id, nombre, descripcion, precio, estado) {
	$("#edicionProducto").removeClass('d-none')
	$("#edicionProducto")[0][0].setAttribute('name','id');
	$("#edicionProducto")[0][0].setAttribute('value',id);
	$("#edicionProducto")[0][1].setAttribute('name','producto');
	$("#edicionProducto")[0][1].setAttribute('value',nombre);
	$("#edicionProducto")[0][2].setAttribute('name','descripcion');
	$("#edicionProducto")[0][2].setAttribute('value',descripcion);
	$("#edicionProducto")[0][3].setAttribute('name','precio');
	$("#edicionProducto")[0][3].setAttribute('value',precio);
	$("#edicionProducto")[0][4].setAttribute('name','estado');
	
	$("#edicionProducto").addClass('d-flex')
}

function abrirEdicionCat(id, nombre, estado) {
	$("#edicionCategoria").removeClass('d-none')
	$("#edicionCategoria")[0][0].setAttribute('name','id');
	$("#edicionCategoria")[0][0].setAttribute('value',id);
	$("#edicionCategoria")[0][1].setAttribute('name','categoria');
	$("#edicionCategoria")[0][1].setAttribute('value',nombre);
	$("#edicionCategoria")[0][2].setAttribute('name','activo');
	$("#edicionCategoria")[0][2].setAttribute('name','estado');

	$("#edicionCategoria").addClass('d-flex')
}