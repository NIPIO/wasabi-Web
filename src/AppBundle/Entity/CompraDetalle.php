<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompraDetalle
 *
 * @ORM\Table(name="compra_detalle")
 * @ORM\Entity
 */
class CompraDetalle
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="compra_id", type="integer", nullable=false)
     */
    private $compraId;

    /**
     * @var string
     *
     * @ORM\Column(name="producto", type="string", length=50, nullable=false)
     */
    private $producto;

    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad", type="integer", nullable=false)
     */
    private $cantidad;

    /**
     * @var string
     *
     * @ORM\Column(name="precio", type="string", length=50, nullable=false)
     */
    private $precio;



    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer
     */
    public function getCompraId()
    {
        return $this->compraId;
    }

    /**
     * @param integer $compraId
     *
     * @return self
     */
    public function setCompraId($compraId)
    {
        $this->compraId = $compraId;

        return $this;
    }

    /**
     * @return string
     */
    public function getProducto()
    {
        return $this->producto;
    }

    /**
     * @param string $producto
     *
     * @return self
     */
    public function setProducto($producto)
    {
        $this->producto = $producto;

        return $this;
    }

    /**
     * @return integer
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * @param integer $cantidad
     *
     * @return self
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * @param string $precio
     *
     * @return self
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }
}

