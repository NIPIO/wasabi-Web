<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Compra
 *
 * @ORM\Table(name="compra")
 * @ORM\Entity
 */
class Compra
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
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=50, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="dni", type="string", length=50, nullable=true)
     */
    private $dni;

    /**
     * @var string
     *
     * @ORM\Column(name="telefono", type="string", length=50, nullable=false)
     */
    private $telefono;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=50, nullable=false)
     */
    private $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="string", length=50, nullable=true)
     */
    private $direccion;

    /**
     * @var string
     *
     * @ORM\Column(name="fecha", type="string", length=50, nullable=false)
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="precioBruto", type="string", length=50, nullable=true)
     */
    private $preciobruto;

    /**
     * @var string
     *
     * @ORM\Column(name="precioNeto", type="string", length=50, nullable=true)
     */
    private $precioneto;

    /**
     * @var string
     *
     * @ORM\Column(name="estadoCompra", type="string", length=50, nullable=true)
     */
    private $estadocompra;

    /**
     * @var string
     *
     * @ORM\Column(name="mercadopago_id", type="string", length=50, nullable=true)
     */
    private $mercadopagoId;

    /**
     * @var string
     *
     * @ORM\Column(name="mercadopago_usuario", type="string", length=50, nullable=true)
     */
    private $mercadopagoUsuario;



    /**
     * @return string
     */
    public function getMercadopagoUsuario()
    {
        return $this->mercadopagoUsuario;
    }

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
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param string $nombre
     *
     * @return self
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return string
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * @param string $dni
     *
     * @return self
     */
    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param string $telefono
     *
     * @return self
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     *
     * @return self
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * @param string $direccion
     *
     * @return self
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * @return string
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param string $fecha
     *
     * @return self
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreciobruto()
    {
        return $this->preciobruto;
    }

    /**
     * @param string $preciobruto
     *
     * @return self
     */
    public function setPreciobruto($preciobruto)
    {
        $this->preciobruto = $preciobruto;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrecioneto()
    {
        return $this->precioneto;
    }

    /**
     * @param string $precioneto
     *
     * @return self
     */
    public function setPrecioneto($precioneto)
    {
        $this->precioneto = $precioneto;

        return $this;
    }

    /**
     * @return string
     */
    public function getEstadocompra()
    {
        return $this->estadocompra;
    }

    /**
     * @param string $estadocompra
     *
     * @return self
     */
    public function setEstadocompra($estadocompra)
    {
        $this->estadocompra = $estadocompra;

        return $this;
    }

    /**
     * @return string
     */
    public function getMercadopagoId()
    {
        return $this->mercadopagoId;
    }

    /**
     * @param string $mercadopagoId
     *
     * @return self
     */
    public function setMercadopagoId($mercadopagoId)
    {
        $this->mercadopagoId = $mercadopagoId;

        return $this;
    }

    /**
     * @param string $mercadopagoUsuario
     *
     * @return self
     */
    public function setMercadopagoUsuario($mercadopagoUsuario)
    {
        $this->mercadopagoUsuario = $mercadopagoUsuario;

        return $this;
    }
}

