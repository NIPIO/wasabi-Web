/*
	Es un código mejorable, pero FooBar.
	NMP.
*/
noHayProductosvar = false;
var precioFinalCompra = 0;
var productosPrecio = [];
var cantidadCarrito;
//CARGO EL CARRITO CON EL STORAGE
$(document).ready(function(e) {
	if (window.location.pathname != '/') { //si no es la pagina principal escondo el menu de categorias porque se rompe el filtro.
		$(".iconoBack").show();
	} else {
		$(".iconoMenu").show();
	}

	productosEnCarrito = [];
    productosEnCarrito = JSON.parse(localStorage.getItem('productos')) || [];
	d = document.getElementById("formularioCarrito");
	var noHayProductos = document.getElementById("noHayProductos");

    if(productosEnCarrito.length == 0) {
    	noHayProductos.style.display = 'block'
    } 
    //voy a buscar ¡TODOS! los precios actualizados de los productos una sola vez para no ir a bsucarlos a cada rato que se arma el carrito.
	preciosProductos();

	headerTag = document.querySelector("header");
	mainTag = document.querySelector("main");
	footerTag = document.querySelector("footer");
})

function preciosProductos() {
	$('#loader').show(); //oculto todos para poner icono de cargar
	$.ajax({
            url:"/preciosProductos",
            type: "POST",
            dataType: "json",
            data: {
            },
            async: true,
            success: function (data)
            {
            	$('#loader').hide(); 
            	productosPrecio = data;
            	loadCarrito();
            }
        });
}

function openNav() {
	closeCarrito();
	document.getElementById("mySidenav").style.width = "250px";
}
function openCarrito() {
	closeNav();
	if (window.innerWidth < 576) {
		document.getElementById("mySideCarrito").style.width = "330px";
	}else {
		document.getElementById("mySideCarrito").style.width = "550px";
	}
	blur3();

	// cargarModalCompra();
}
function closeNav() {
	document.getElementById("mySidenav").style.width = "0";
	blur0();
}
function closeCarrito() {
	document.getElementById("mySideCarrito").style.width = "0";
	blur0();
}
function blur0(){
	headerTag.style.filter =  'blur(0px)';
	mainTag.style.filter =  'blur(0px)';
	footerTag.style.filter =  'blur(0px)';
}
function blur3(){
	headerTag.style.filter =  'blur(3px)';
	mainTag.style.filter =  'blur(3px)';
	footerTag.style.filter =  'blur(3px)';
}
$(document).click(function(e) {
	if (!$(e.target).is('.esCarrito')) {
		closeCarrito();
    }
    if ($(e.target).is('.esNav')) {
		closeNav();
    }
});

//AGREGA PRODUCTOS AL CARRITO
$("#agregarCarrito").click(function(e) {
	//chequeo si el producto ya existe en el carrito.
	var productoAlStorage = $('#agregarCarrito').data('producto');
	let idProd = productoAlStorage.split(',')[0];

	let existe = false;
	for (var i = 0; i < $('div.item-carrito').length; i++) {
		if (idProd === $('div.item-carrito')[i].id) {
			alert('El producto ya existe en el carrito. Puede cambiar la cantidad si lo desea.');
			let existe = true;
			return;
		}
	}

	if(!existe) {
		$('div.item-carrito').remove();
		$('#realizarPedido').remove();
		noHayProductos.style.display = 'none'
		let idProd = productoAlStorage.split(',')[0];
		var producto = {'id':idProd};
	    productosEnCarrito.push(producto);
	    localStorage.setItem('productos', JSON.stringify(productosEnCarrito));
	    loadCarrito();
	}
});

//ELIMINA PRODUCTOS DEL CARRITO
function eliminarCarrito(e) {
	//me pasa un numero de posicion del array que hay en el carrito, lo busco en el localstorage por psicion, lo borro y refresco.
	productosEnCarrito.splice(e,1);
	$('.item-carrito').remove();
	$('#realizarPedido').remove();

	localStorage.setItem('productos', JSON.stringify(productosEnCarrito));
	if(productosEnCarrito.length == 0) {
    	noHayProductos.style.display = 'block';
    } 
    loadCarrito()    	
}

//RECARGA EL CARRITO CUANDO SE AGREGA O CUANDO SE ELIMINA UN PRODUCTO.
function loadCarrito() {
	if (productosEnCarrito.length > 0) {
		for (var i = 0; i < productosEnCarrito.length; i++) {
			var html = document.createElement('div'); 
			html.className = (`item-carrito row esCarrito mb-4 `);
			html.id = productosEnCarrito[i]['id']; 
			html.innerHTML += `<div class="imagen-item col-3 esCarrito"><img src="/web/img/productos/` + productosPrecio[html.id]['url'] + `" class="esCarrito esImagen"></div>`;
			html.innerHTML += `<div class="descripcion-item col-9 row esCarrito"><div class="titulo-item text-left col-6 esCarrito"><div class="esCarrito"> <input type="text" name="_producto`+[i]+`" class="border-none esCarrito esDescripcion" value="` + productosPrecio[html.id]['nombre'] + `" readonly ></input></div> <div class="cantidad-precio-item esCarrito col-12"><input type="number" class="col-4 esCarrito" name='_cantidad`+[i]+`' id="`+ [i] +`" class="esCarrito esCantidad" style="padding:0;border:none" min="1" data-bind="cantidadProducto" value="1"/></div></div><div class="text-right col-6 esCarrito"><div class="esCarrito" onclick="eliminarCarrito(` + [i] + `)"><img src="/web/img/trash.svg" class="esCarrito trash" style="margin:0 15px"></div> <div class="esCarrito"><input class="border-none col-12 esCarrito text-right esPrecio" type='text' id='precioFinal`+[i]+`' value="$` + productosPrecio[html.id]['precio'] + `.-" name='_precio`+[i]+`' disabled></input></div>  </div> </div></div>`;
			d.appendChild(html);
		}

		$('#cantidadCarrito').css("display","inline");
		$('#cantidadCarrito span').text(productosEnCarrito.length);
		var button = document.createElement("input");
	    button.type = "submit";
	    button.id = "realizarPedido";
	    button.className = "col-6 m-auto button";
	    button.value ="¡LO QUIERO!"
        $("#formularioCarrito").append(button);
	} else {
		$('#cantidadCarrito').css("display","none");
	}
}

//OBSERVO LOS INPUT A VER SI QUIERE 1 UNIDAD O MÁS DE ALGUN PRODUCTO.
onlyOnce = true;
inputAnterior = '';
$('#formularioCarrito').on('keyup change paste', 'input, select, textarea', function(e){
	if(inputAnterior != e.target.id) {
		onlyOnce = true;
		inputAnterior = e.target.id;
	} 
	if (onlyOnce) {
		productoPrecio = $("#precioFinal" + e.target.id).val();
		productoPrecio = productoPrecio.substring(1,(productoPrecio.length - 2))
		onlyOnce = false;
		//el precio se va a modificar, por lo tanto entro una sola vez para obtener el precio inicial
	}
	productoCantidad = $("#" + e.target.id).val();
	if (!productoCantidad.includes("-")) {
		document.getElementById("precioFinal" + e.target.id).value = "$" + (productoPrecio * productoCantidad) + ".-"
	}
});

// FILTROS DE CATEGORIA FILTROS DE CATEGORIA FILTROS DE CATEGORIA FILTROS DE CATEGORIA FILTROS DE CATEGORIA//
$(document).on('click', 'a.esNav', function(e){
	$('.item-producto').hide(); //oculto todos
	$('#loader').show(); //oculto todos para poner icono de cargar
	e.preventDefault();
    var cat = $(this).data('categoryType'); //busco a ver qué categoria se clickeó
		$.ajax({
            url:"/filtrarPorCat",
            type: "POST",
            dataType: "json",
            data: {
                "categoria": e.target.innerHTML
            },
            async: true,
            success: function (data)
            {
            	$('#loader').hide(); 
			    $('.item-producto[data-category-name="'+cat+'"]').show(); //solo muesto las qe tengan valor cat
            }
        });
});
// // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // 

// ENVIO DE CORREO ELECTRONICO
$("#formularioContacto").submit(function(e) {
    e.preventDefault();
    nombreContacto = $("#nombreContacto").val();
    mailContacto = $("#mailContacto").val();
    asuntoContacto = $("#asuntoContacto").val();
    //validaciones 
    if (nombreContacto.length < 6 || mailContacto.length < 6 || !mailContacto.includes("@") || asuntoContacto.length < 7 || asuntoContacto.length > 120) {
    	mostrarErrorContacto();
    } else {
    	button = $("#contactarButton")[0];
	    formdata =  {
			'nombre': nombreContacto,
			'mail': mailContacto,
			'asunto': asuntoContacto,
		}
		button.value = 'Enviado!'
		button.backgroundColor = '#4bd724'
		$.ajax({
            url:"/enviarCorreo",
            type: "POST",
            dataType: "json",
            data: {
            	'formdata': formdata
            },
            async: true,
            success: function (data)
            {

            }
        });
		setTimeout(function(){ 
			button.style.display = 'none'
		}, 2000);

    }
});

// // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // 
