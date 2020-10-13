<?php
namespace AppBundle\Controller;
use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\Productos; 
use AppBundle\Entity\Categoria;
use AppBundle\Entity\Compra;
use AppBundle\Entity\CompraDetalle;

class DefaultController extends Controller
{
    private $productosAll = [];
    private $categoriasAll = [];
    // private $tokenUsuarioMercadoPago = 'TEST-4781387091404042-070623-8a87ec946b38ced604a26a1f56a180cb-174256233';
    private $tokenUsuarioMercadoPago = 'APP_USR-4781387091404042-070623-7558de2fe40f4e53caab31fd6179788b-174256233';
    


    /**
     * @Route("/", name="homepage")
     */
    public function indexAction() {
        $this->productosAll = $this->getDoctrine()->getRepository(Productos::class)->findByActivo(true);
        $this->categoriasAll = $this->getDoctrine()->getRepository(Categoria::class)->findByActivo(true);
        return $this->render('default/index.html.twig', [
            'categorias' => $this->categoriasAll,
            'productos' => $this->productosAll,
            'mercadoPagoPrecio' => ''
        ]);
    }


    //obtiene el detalle del producto seleccionado.
    /**
     * @Route("/producto/{nombre}/", name="producto")
     */
    public function producto($nombre) { 
        $producto = $this->getDoctrine()->getRepository(Productos::class)->findOneByNombre($nombre);
        $categoria = $this->getDoctrine()->getRepository(Categoria::class)->findOneByNombre($producto->getCategoria()->getNombre());
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("
            SELECT p FROM AppBundle:Productos p 
            INNER JOIN AppBundle:Categoria c WITH (p.categoria = c)
            WHERE p.nombre != :nombreProd 
            AND c = :idCat
            ");
        $query->setParameter('nombreProd', $producto->getNombre());
        $query->setParameter('idCat', $categoria->getId());
        $query->setMaxResults(3);
        $productosSimilares = $query->getResult();
        return $this->render('default/detalle.html.twig', [
            'producto' => $producto,
            'categoria' => $categoria,
            'productosSimilares' => $productosSimilares,
        ]);
    }


    //filtra la categoria seleccionada en el menu izquierdo.
    /**
    * @Route("/filtrarPorCat", name="filtrarPorCat")
    */
    public function filtrarPorCat(Request $request) {
        $categoria = $request->request->get('categoria');
        $categoriaObject = $this->getDoctrine()->getRepository(Categoria::class)->findOneByNombre($categoria);
        $productos = $this->getDoctrine()->getRepository(Productos::class)->findByCategoria($categoriaObject);

        return new JsonResponse($productos);
    }


    //obtiene todos los precios de los productos actualiazdos para cargarlos directamente en el frontend solo una vez.
    /**
    * @Route("/preciosProductos", name="preciosProductos")
    */
    public function preciosProductos(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("
            SELECT p.id, p.nombre, p.precio, p.url FROM AppBundle:Productos p 
            ");
        $o = [];
        $arrayRetorno = $query->getResult();
        //le agrego una posicion para que los id coincidan con las posiciones y evitar movieimtos raros en el front.
        array_unshift($arrayRetorno, $o);
        return new JsonResponse($arrayRetorno);
    }


    //obtiene el cupon de pago para que pueda efectuarse la compra una vez que confirma los productos del carrito.
    /**
    * @Route("/obtenerPreferenceMP", name="obtenerPreferenceMP")
    */
    public function obtenerPreferenceMP(Request $request) {
        $url = "https://api.mercadopago.com/checkout/preferences?access_token=$this->tokenUsuarioMercadoPago";
        $items = [];
        $parametros = $request->request->all();

        foreach ($parametros as $key) {
                $object = new \stdClass();
                $object->title = $key;
                $object->description =  $key;
                $object->quantity = next($parametros);
                $object->currency_id = 'ARS';
                $object->unit_price = 120;
                $object->totalitem = 120;
                array_push($items, $object);
        }

        //no puedo borrarlos ahora porque php es una poronga, así que lo completo y despues borro lo que no me sirven.
        for ($i=0; $i < count($items); $i++) { 
            if ($i % 2 == 0) {
                $precioProducto = $this->getDoctrine()->getRepository(Productos::class)->findOneByNombre(['nombre' => $items[$i]->description]);
                $items[$i]->quantity = (int)$items[$i]->quantity;
                $items[$i]->unit_price = (int)$precioProducto->getPrecio();
                $items[$i]->totalitem = (int)$precioProducto->getPrecio() * (int)$items[$i]->quantity;
            } 
        }
        //borro los items incorrectos o que no me sirven
        for ($i=0; $i < count($items); $i++) { 
            if ($i % 2 != 0) {
                unset($items[$i]);
            } 
        }
        $items = array_values($items);
        if (count($items) > 1) {
            array_pop($items);
        }
        $precioTotalFinal = 0;
        foreach ($items as $i) {
            $precioTotalFinal += $i->totalitem;
        }

        $object = new \stdClass();
        $object->items = $items;
        $object = json_encode($object);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $object);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $items = json_decode(json_encode($items), true);

        $respuesta = json_decode($response);
        $respuesta = substr($respuesta->init_point, strpos($respuesta->init_point,'=') + 1);
        return $this->render('default/compraMP.html.twig', [
            'respuesta' => $respuesta,
            'items' => $items,
            'precioTotalFinal' => $precioTotalFinal,
            
        ]);
        // return new JsonResponse($respuesta);
    }


    //ruta a la que le pega mercadopago una vez que se pagó el producto.
    /**
    * @Route("/procesar-pago", name="procesar-pago")
    */
    public function procesarPago(Request $request) {
        $payment = $request->request->get('payment_id');
    	$status = $request->request->get('payment_status');
    	$details = $request->request->get('payment_status_detail');
    	if ($status === 'approved' && $details === 'accredited') {
            try {
                $cargoDatos = $this->guardarCompra($payment);
                // $cargoDatos = $this->guardarCompra('7697767277');
                if($cargoDatos) {
                    $msj = '¡Gracias por la compra ' . $cargoDatos[1] . '! Te enviamos un correo a '. $cargoDatos[2] .'. Saludos.';
                    $color = 'green';
                    $this->envioCorreoPostCompra($cargoDatos[1], $cargoDatos[2]);
                } else {
                    $msj = 'Hubo un error. Te pedimos disculpas. Comunicate con nosotros vía mail o red social por favor para poder ayudarte. Muchas gracias!';
                    $color = 'red';
                } 
            } catch (Error $e) {
                return [500, $e];
            }

    	} else {
    		$msj = 'El pago no pudo procesarse por motivos externos. Intente nuevamente con otra cuenta de MercadoPago o contactate directamente con nostros. ¡Gracias!';
    		$color = 'red';
    	}

    	return $this->render('default/finCompra.html.twig', [
            'mensaje' => $msj,
            'color' => $color
        ]);
    }


    //función que guarda la compra en la Base de datos. COMPRA y COMPRADETALLE
    private function guardarCompra($payment) {
        try {
            $datosCompra = $this->detalleMercadoPagoURL($payment, 'compra'); //busco datos de la compra
            $datosCompraProductos = $this->detalleMercadoPagoURL($datosCompra['order']['id'], 'productos'); //con id de orden busco datos de productos

            //Datos comprador:
            $comprador = $datosCompra['payer'];
            //Datos compra:
            $fecha = $datosCompra['date_approved'];
            $fecha = explode('T',  substr($fecha, 0, strpos($fecha, '.000'))); //formateo fecha.

            //estos son los datos de la compra
            $entityManager = $this->getDoctrine()->getManager();
            $compra = new Compra();
            $compra->setNombre($comprador['first_name'] . $comprador['last_name']);
            $compra->setDNI($comprador['identification']['number']);
            $compra->setTelefono($comprador['phone']['area_code'] . $comprador['phone']['number']);
            $compra->setMail($comprador['email']);
            $compra->setDireccion('-');
            $compra->setFecha($fecha[0] . ' ' . $fecha[1]);
            $compra->setPrecioBruto($datosCompra['transaction_details']['total_paid_amount']);
            $compra->setPrecioNeto($datosCompra['transaction_details']['net_received_amount']);
            $compra->setEstadoCompra($datosCompra['status_detail']);
            $compra->setMercadoPagoId($datosCompra['order']['id']);
            $compra->setMercadoPagoUsuario($comprador['id']);
            $entityManager->persist($compra);
            $entityManager->flush();

            //y estos son los datos de los productos que se compraron.
            $compra = $this->getDoctrine()->getRepository(Compra::class)->findOneByFecha($fecha[0] . ' ' . $fecha[1]);
            foreach ($datosCompraProductos['items'] as $item) {
                $productosCompra = new CompraDetalle();
                $productosCompra->setCompraId($compra->getId());
                $productosCompra->setProducto($item['description']);
                $productosCompra->setCantidad($item['quantity']);
                $productosCompra->setPrecio($item['unit_price']);
                $entityManager->persist($productosCompra);
            }
            
            $entityManager->flush();
            return [200, $comprador['first_name'] . $comprador['last_name'], $comprador['email']];
        } catch (Error $e) {
            return [500, $e];
        }
    }
    

    //función que es llamada por guardarCompra() para obtener de la API de mercadopago todos los detalles de la compra.
    private function detalleMercadoPagoURL($var, $tipo) {
            if ($tipo == 'compra') {
                $url = "https://api.mercadopago.com/v1/payments/$var?access_token=$this->tokenUsuarioMercadoPago";                
            } else {
                $url = "https://api.mercadopago.com/merchant_orders/$var?access_token=$this->tokenUsuarioMercadoPago";                
            }
            $ch = curl_init();// Empiezo.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// Will return the response, if false it print the response
            curl_setopt($ch, CURLOPT_URL,$url);// Set the url
            $result=curl_exec($ch);// Execute
            curl_close($ch);// Closing
            $jsonCompra = json_decode($result, true);
            return $jsonCompra;
    }


    //funciión que envía por correo la confirmación de la compra.
    private function envioCorreoPostCompra($nombre, $to) {
        try {
                ini_set( 'display_errors', 1 );
                error_reporting( E_ALL );
                $subject = 'Compra Wasabi';
                $asunto =  'Hola ' . $nombre .'! Gracias por comprarnos. Te gustaría que te lo enviemos o preferis retirarlo? Saludos.';
                $from = 'soy.alejo.castillo@gmail.com';
                $headers ="From: " . $from;      
                mail($to, $subject, $asunto, $headers);
                return 200;
            } catch (exception $e) {
                return 500;
            }
    }


    //envia correo de contacto ante duda o consulta.
    /**
    * @Route("/enviarCorreo", name="enviarCorreo")
    */
    public function enviarCorreo(Request $request) {
        $datosForm = $request->request->get('formdata');
        ini_set( 'display_errors', 1 );
        error_reporting( E_ALL );
        $to = 'soy.alejo.castillo@gmail.com';
        $subject = 'Consulta Web. ('. $datosForm['nombre'] .')';
        $asunto = $datosForm['asunto'];
        $from = $datosForm['mail'];
        $headers ="From: " . $from;      
        mail($to, $subject, $asunto, $headers);
        die;
    }


}
