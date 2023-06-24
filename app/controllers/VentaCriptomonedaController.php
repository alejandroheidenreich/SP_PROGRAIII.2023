<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once './models/VentaCriptomoneda.php';
require_once './middlewares/AutentificadorJWT.php';
require_once './interfaces/IApiUse.php';

class VentaCriptomonedaController extends VentaCriptomoneda implements IApiUse
{
  public static function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $cookies = $request->getCookieParams();

    $token = AutentificadorJWT::ObtenerData($cookies['token']);
    $uploadedFiles = $request->getUploadedFiles();
    $targetPath = './FotoCripto2023/' . date_format(new DateTime(), 'Y-m-d_H-i-s') . '_' . $token->id . '_' . $parametros['idCripto'] . '.jpg';
    $uploadedFiles['imagen']->moveTo($targetPath);


    $ventacripto = new VentaCriptomoneda();
    $ventacripto->cantidad = $parametros['cantidad'];
    $ventacripto->idCripto = $parametros['idCripto'];
    $ventacripto->idCliente = $token->id;
    $ventacripto->imagenVenta = $targetPath;

    VentaCriptomoneda::crear($ventacripto);

    $payload = json_encode(array("mensaje" => "Criptomoneda vendida con existo"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function TraerUno($request, $response, $args)
  {
    $cripto = VentaCriptomoneda::obtenerUno($args['id']);
    $payload = json_encode($cripto);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public static function TraerTodosPorNacionalidad($request, $response, $args)
  {
    $parametros = $request->getQueryParams();
    $cripto = VentaCriptomoneda::obtenerTodosPorNacionalidad($parametros['nacionalidad']);

    $payload = json_encode($cripto);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public static function TraerTodosPorNacionalidadFecha($request, $response, $args)
  {
    $ventascripto = VentaCriptomoneda::obtenerTodosPorNacionalidadFecha($args['nacionalidad'], $args['fechaInicio'], $args['fechaFinal']);
    $payload = json_encode($ventascripto);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function TraerTodosPorMoneda($request, $response, $args)
  {
    $ventascripto = VentaCriptomoneda::obtenerTodosPorMoneda($args['moneda']);
    $payload = json_encode($ventascripto);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public static function TraerTodos($request, $response, $args)
  {

    $lista = VentaCriptomoneda::obtenerTodos();
    $payload = json_encode(array("listaCriptos" => $lista));


    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function ModificarUno($request, $response, $args)
  {

    $id = $args['id'];

    $usuario = Usuario::obtenerUnoPorID($id);

    if ($usuario != false) {
      $parametros = $request->getParsedBody();

      $actualizado = false;
      if (isset($parametros['mail'])) {
        $actualizado = true;
        $usuario->mail = $parametros['mail'];
      }
      if (isset($parametros['clave'])) {
        $actualizado = true;
        $usuario->clave = password_hash($parametros['clave'], PASSWORD_DEFAULT);
      }
      if (isset($parametros['tipo'])) {
        $actualizado = true;
        $usuario->tipo = $parametros['tipo'];
      }

      if ($actualizado) {
        Usuario::modificar($usuario);
        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
      } else {
        $payload = json_encode(array("mensaje" => "Usuario no modificar por falta de campos"));
      }

    } else {
      $payload = json_encode(array("error" => "Usuario no existe"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function BorrarUno($request, $response, $args)
  {
    $usuarioId = $args['id'];

    if (Usuario::obtenerUnoPorID($usuarioId)) {

      Usuario::borrar($usuarioId);
      $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
    } else {

      $payload = json_encode(array("mensaje" => "ID no coincide con un usuario"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}