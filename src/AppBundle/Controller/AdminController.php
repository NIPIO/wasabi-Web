<?php
namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



use AppBundle\Entity\Productos; 
use AppBundle\Form\ProductoType; 
use AppBundle\Entity\Categoria;
use AppBundle\Entity\Compra;
use AppBundle\Entity\CompraDetalle;
use AppBundle\Entity\Usuarios;

class AdminController extends Controller {
    private $categoriasAll = [];
    private $productosAll = [];

	 /**
     * @Route("/ingresar", name="ingresar")
     */
    public function ingresarAction() {
    	return $this->render('admin/registro.html.twig', []);
    }

   	/**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request) {
    	$usuario = $request->request->get('usuario');
    	$pass = $request->request->get('pass');

        $usuario = $this->getDoctrine()->getRepository(Usuarios::class)->findOneBy([
        	'nombre' => $usuario,
        	'pass' => $pass,
        ]);

        if(!is_null($usuario)){
        	 return $this->redirectToRoute("admin");
        } else {
        	dump('usuario o contraseña incorrecta. Si te olvidaste alguno, comunicate con el Dr. Zapato');die;
        }
    }

    /**
    * @Route("/admin", name="admin")
    */
    public function adminAction() {
        $entityManager = $this->getDoctrine()->getManager();
        $this->categoriasAll = $this->getDoctrine()->getRepository(Categoria::class)->findAll();
        $this->productosAll = $this->getDoctrine()->getRepository(Productos::class)->findAll();

		$historialCompras = $this->obtenerCompras();
    	return $this->render('admin/general.html.twig', [
            'categorias' => $this->categoriasAll,
    		'productos' => $this->productosAll,
            'comprasDetalle' => $historialCompras[0],
    		'compras' => $historialCompras[1],
    	]);
    }

    /**
     * @Route("/nuevoProducto", name="nuevoProducto")
     */
    public function nuevoProducto(Request $request) {
        $file = $request->files->get('file');

        $nombreProd =  $request->request->get('nombreProd');
        $nombreCat =  $request->request->get('nombreCat');
        $descripcion =  $request->request->get('descripcion');
        $precio =  $request->request->get('precio');

        $status = array('status' => "success","fileUploaded" => false);
      
        if(!is_null($file)){
            //muevo el archivo a la carpeta productos.
            $nombreArchivo = md5(uniqid()).'.'.$file->guessExtension(); 
            $file->move($this->getParameter('photos_directory'), $nombreArchivo); 
            $status = array('status' => "success","fileUploaded" => true);
        }

        //comienzo la grabación en la base
        $entityManager = $this->getDoctrine()->getManager();
        $categoriaObjeto = $this->getDoctrine()->getRepository(Categoria::class)->findOneById($nombreCat);
        
        $producto = new Productos();
        $producto->setNombre($nombreProd);
        $producto->setCategoria($categoriaObjeto);
        $producto->setDescripcion($descripcion);
        $producto->setPrecio($precio);
        $producto->setUrl($nombreArchivo);
        $producto->setActivo(true);
        $entityManager->persist($producto);
        $entityManager->flush();

        return new JsonResponse($status);
    }

    /**
     * @Route("/nuevaCategoria", name="nuevaCategoria")
     */
    public function nuevaCategoria(Request $request) {
    	$nombreCat = $request->request->get('nombreCat');
		$entityManager = $this->getDoctrine()->getManager();
		$categoria = new Categoria();
		$categoria->setNombre($nombreCat);
		$entityManager->persist($categoria);
		$entityManager->flush();
        return new JsonResponse(200);
    }
    
    private function obtenerCompras() {
		$em = $this->getDoctrine()->getManager();
        $compras = $this->getDoctrine()->getRepository(Compra::class)->findAll();
        $comprasDetalle = $this->getDoctrine()->getRepository(CompraDetalle::class)->findAll();
	    return [$comprasDetalle, $compras];
    }
    /**
     * @Route("/editarProducto", name="editarProducto")
     */
    public function editarProducto(Request $request) {
        try{
            $items = $request->request->all();
            $entityManager = $this->getDoctrine()->getManager();
            $producto = $this->getDoctrine()->getRepository(Productos::class)->findOneById($items['id']);
            $producto->setNombre($items['producto']);
            $producto->setDescripcion($items['descripcion']);
            $producto->setPrecio((int)$items['precio']);
            if (isset($items['estado'])) {
                $producto->setActivo(true);
            } else {
                $producto->setActivo(false);
            }
            $entityManager->persist($producto);
            $entityManager->flush();

            return $this->adminAction();
        } catch (Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
    }
    /**
     * @Route("/editarCategoria", name="editarCategoria")
     */
    public function editarCategoria(Request $request) {
        try{
            $items = $request->request->all();
            $entityManager = $this->getDoctrine()->getManager();
            $categoria = $this->getDoctrine()->getRepository(Categoria::class)->findOneById($items['id']);
            $categoria->setNombre($items['categoria']);
            if (isset($items['estado'])) {
                $categoria->setActivo(true);
            } else {
                //DESACTIVO LOS PRODUCSO DE LA CATEGORIA QUE SE ACABA DE DESACTIVAR.
                $categoria->setActivo(false);
                $prodxcat = $this->getDoctrine()->getRepository(Productos::class)->findByCategoria($categoria);
                foreach ($prodxcat as $key) {
                    $key->setActivo(false);
                    $entityManager->persist($key);
                }
            }
            $entityManager->persist($categoria);
            $entityManager->flush();

            return $this->adminAction();
        } catch (Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
    }
}
