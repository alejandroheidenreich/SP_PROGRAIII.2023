<?php

require_once './interfaces/IPersistencia.php';


class VentaCriptomoneda implements IPersistencia
{
    public $id;
    public $fecha;
    public $cantidad;
    public $idCripto;
    public $idCliente;
    public $imagenVenta;


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
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ventascriptomonedas (fecha, cantidad, idCripto, idCliente, imagenVenta) VALUES (:fecha, :cantidad, :idCripto, :idCliente, :imagenVenta)");
        $consulta->bindValue(':fecha', date_format(new DateTime(), "Y-m-d"), PDO::PARAM_STR);
        $consulta->bindValue(':cantidad', $cripto->cantidad, PDO::PARAM_STR);
        $consulta->bindValue(':idCripto', $cripto->idCripto, PDO::PARAM_INT);
        $consulta->bindValue(':idCliente', $cripto->idCliente, PDO::PARAM_INT);
        $consulta->bindValue(':imagenVenta', $cripto->imagenVenta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, cantidad, idCripto, idCliente, imagenVenta FROM ventascriptomonedas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'VentaCriptomoneda');
    }

    public static function obtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, cantidad, idCripto, idCliente, imagenVenta FROM ventascriptomonedas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('VentaCriptomoneda');
    }

    public static function obtenerTodosPorNacionalidad($nacionalidad)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, cantidad, idCripto, idCliente, imagenVenta FROM ventascriptomonedas WHERE nacionalidad = :nacionalidad");
        $consulta->bindValue(':nacionalidad', $nacionalidad, PDO::PARAM_STR);

        $consulta->execute();

        return $consulta->fetchObject('VentaCriptomoneda');
    }

    public static function obtenerTodosPorNacionalidadFecha($nacionalidad, $fechaInicio, $fechaFinal)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT ventascriptomonedas.id, ventascriptomonedas.fecha, ventascriptomonedas.cantidad, ventascriptomonedas.idCripto, ventascriptomonedas.idCliente, ventascriptomonedas.imagenVenta FROM ventascriptomonedas INNER JOIN criptomonedas ON ventascriptomonedas.idCripto = criptomonedas.id WHERE criptomonedas.nacionalidad = :nacionalidad AND (ventascriptomonedas.fecha BETWEEN :fechaInicio AND :fechaFinal)");
        $consulta->bindValue(':nacionalidad', $nacionalidad, PDO::PARAM_STR);
        $consulta->bindValue(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $consulta->bindValue(':fechaFinal', $fechaFinal, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('VentaCriptomoneda');
    }

    public static function obtenerTodosPorMoneda($moneda)
    {
        $cripto = Criptomoneda::obtenerUnoPorNombre($moneda);
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT usuarios.id, usuarios.mail, usuarios.clave, usuarios.tipo FROM usuarios INNER JOIN ventascriptomonedas ON ventascriptomonedas.idCliente = usuarios.id INNER JOIN criptomonedas ON ventascriptomonedas.idCripto = criptomonedas.id WHERE criptomonedas.nombre = :moneda");
        $consulta->bindValue(':moneda', $moneda, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function modificar($cripto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE ventascriptomonedas SET fecha = :fecha, cantidad = :cantidad, idCripto = :idCripto, idCliente = :idCliente, imagenVenta = :imagenVenta WHERE id = :id");
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