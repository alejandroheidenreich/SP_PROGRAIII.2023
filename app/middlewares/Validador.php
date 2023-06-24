<?php

require_once "./models/Usuario.php";
require_once "./models/Criptomoneda.php";
require_once './middlewares/AutentificadorJWT.php';

class Validador
{

    public static function ValidarNuevoUsuario($request, $handler)
    {
        $parametros = $request->getParsedBody();

        $mail = $parametros['mail'];
        $tipo = $parametros['tipo'];
        if (Usuario::ValidarTipo($tipo) && Usuario::ValidarMail($mail) == null) {
            return $handler->handle($request);
        }

        throw new Exception("Error en la creacion del Usuario");
    }

    public static function ValidarIDCripto($request, $handler)
    {
        $parametros = $parametros = $request->getParsedBody();

        if (Criptomoneda::obtenerUno($parametros['idCripto']) == false) {
            throw new Exception("No existe esa criptomoneda");
        }

        return $handler->handle($request);
    }

    public static function ValidarCantidad($request, $handler)
    {
        $parametros = $parametros = $request->getParsedBody();

        if ($parametros['cantidad'] <= 0) {
            throw new Exception("La cantidad debe ser mayor a cero");
        }

        return $handler->handle($request);
    }

}