<?php

require_once './interfaces/IPersistencia.php';


class Criptomoneda implements IPersistencia
{
    public $id;
    public $precio;
    public $nombre;
    public $foto;
    public $nacionalidad;


    public function __get($propiedad)
    {
        if (property_exists($this, $propiedad)) {
            return $this->$propiedad;
        } else {
            return null;
        }
    }

    public function __set($propiedad, $valor)
    {
        if (property_exists($this, $propiedad)) {
            $this->$propiedad = $valor;
        } else {
            echo "No existe " . $propiedad;
        }
    }

    public static function crear($cripto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO criptomonedas (precio, nombre, foto, nacionalidad) VALUES (:precio, :nombre, :foto, :nacionalidad)");
        $consulta->bindValue(':precio', $cripto->precio, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $cripto->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $cripto->foto, PDO::PARAM_STR);
        $consulta->bindValue(':nacionalidad', strtolower($cripto->nacionalidad), PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, precio, nombre, foto, nacionalidad FROM criptomonedas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Criptomoneda');
    }

    public static function obtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, precio, nombre, foto, nacionalidad FROM criptomonedas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Criptomoneda');
    }
    public static function obtenerUnoPorNombre($nombre)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, precio, nombre, foto, nacionalidad FROM criptomonedas WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Criptomoneda');
    }

    public static function obtenerTodosPorNacionalidad($nacionalidad)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, precio, nombre, foto, nacionalidad FROM criptomonedas WHERE nacionalidad = :nacionalidad");
        $consulta->bindValue(':nacionalidad', $nacionalidad, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Criptomoneda');
    }


    public static function modificar($cripto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE criptomonedas SET precio = :precio, nombre = :nombre, foto = :foto, nacionalidad = :nacionalidad WHERE id = :id");
        $consulta->bindValue(':precio', $cripto->precio, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $cripto->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $cripto->foto, PDO::PARAM_STR);
        $consulta->bindValue(':nacionalidad', $cripto->nacionalidad, PDO::PARAM_STR);
        $consulta->bindValue(':id', $cripto->nacionalidad, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrar($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE criptomonedas WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

}