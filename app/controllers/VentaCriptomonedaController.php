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

    if (count($ventascripto) > 0) {
      $payload = json_encode($ventascripto);
    } else {
      $payload = json_encode(array("mensaje" => "No existen ventas de esa Criptomoneda"));
    }


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

  public static function TraerTodosPDF($request, $response, $args)
  {

    $lista = VentaCriptomoneda::obtenerTodos();
    $pdf = new TCPDF();
    $pdf->SetCreator('Criptomonedas');
    $pdf->SetAuthor('Lindo Servidor');
    $pdf->SetTitle('Ventas de criptomonedas');
    $pdf->SetMargins(10, 10, 10);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $fecha = date_format(new DateTime(), 'd/m/Y');
    $html = '<style>table { border-collapse: collapse; text-align: center;} th, td { border: 1px solid black; padding: 5px; }</style>';
    $html .= '<h1>Listado de todas las ventas</h1>';
    $html .= '<h2>Creado: ' . $fecha . ' </h2>';
    $html .= '<table>';

    $html .= '<tr><th>' . 'ID' . '</th><th>' . 'FECHA' . '</th><th>' . 'CANTIDAD' . '</th><th>' . 'IDCRIPTO' . '</th><th>' . 'IDCLIENTE' . '</th><th>' . 'IMAGEN' . '</th></tr>';
    foreach ($lista as $v) {
      $html .= '<tr><td>' . $v->id . '</td><td>' . $v->fecha . '</td><td>' . $v->cantidad . '</td><td>' . $v->idCripto . '</td><td>' . $v->idCliente . '</td><td>' . $v->imagenVenta . '</td></tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html);
    $pdfContent = $pdf->Output('', 'S');
    $response = $response->withHeader('Content-Type', 'application/pdf');
    $response = $response->withHeader('Content-Disposition', 'inline; filename="ventascriptomonedas' . $fecha . '.pdf"');
    $response->getBody()->write($pdfContent);
    return $response;
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