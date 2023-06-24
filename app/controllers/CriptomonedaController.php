<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once './models/Criptomoneda.php';
require_once './interfaces/IApiUse.php';

class CriptomonedaController extends Criptomoneda implements IApiUse
{
  public static function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();

    $targetPath = './imgs_cripto/' . $parametros['nombre'] . '.jpg';
    $uploadedFiles['foto']->moveTo($targetPath);

    $cripto = new Criptomoneda();
    $cripto->precio = $parametros['precio'];
    $cripto->nombre = $parametros['nombre'];
    $cripto->foto = $targetPath;
    $cripto->nacionalidad = $parametros['nacionalidad'];



    Criptomoneda::crear($cripto);

    $payload = json_encode(array("mensaje" => "Criptomoneda creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function TraerUno($request, $response, $args)
  {
    $cripto = Criptomoneda::obtenerUno($args['id']);
    $payload = json_encode($cripto);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public static function TraerTodosPorNacionalidad($request, $response, $args)
  {
    $parametros = $request->getQueryParams();
    $cripto = Criptomoneda::obtenerTodosPorNacionalidad($parametros['nacionalidad']);
    $payload = json_encode($cripto);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public static function TraerTodos($request, $response, $args)
  {

    $lista = Criptomoneda::obtenerTodos();
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