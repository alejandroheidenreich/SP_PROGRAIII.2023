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
    $cripto = Criptomoneda::obtenerUno($id);

    if ($cripto != false) {
      $uploadedFiles = $request->getUploadedFiles();
      $parametros = $request->getParsedBody();

      $actualizado = false;

      if (isset($parametros['precio'])) {
        $actualizado = true;
        $cripto->precio = $parametros['precio'];
      }
      if (isset($parametros['nombre'])) {
        $actualizado = true;
        $cripto->nombre = $parametros['nombre'];
      }
      if (isset($parametros['nacionalidad'])) {
        $actualizado = true;
        $cripto->tipo = $parametros['nacionalidad'];
      }

      if (isset($uploadedFiles['foto'])) {
        $actualizado = true;
        $fotoVieja = explode('./imgs_cripto/', $cripto->foto);
        rename($cripto->foto, './imgs_cripto/Backup/' . $fotoVieja[1]);

        $targetPath = './imgs_cripto/' . $cripto->nombre . '.jpg';
        $uploadedFiles['foto']->moveTo($targetPath);
      }

      if ($actualizado) {
        Criptomoneda::modificar($cripto);
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
    $id = $args['id'];

    if (Criptomoneda::obtenerUno($id)) {

      Criptomoneda::borrar($id);
      $payload = json_encode(array("mensaje" => "Criptomoneda borrada con exito"));

    } else {

      $payload = json_encode(array("mensaje" => "ID no coincide con una criptomoneda"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}