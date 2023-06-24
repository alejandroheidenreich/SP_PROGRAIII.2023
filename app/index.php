<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Slim\Cookie\Cookie;


require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/AutentificadorJWT.php';
require_once './middlewares/Autentificador.php';
require_once './middlewares/Validador.php';
require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/CriptomonedaController.php';
require_once './controllers/VentaCriptomonedaController.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
//$app->setBasePath('/app');

// Add error middleware
$errorMiddleware = function ($request, $exception, $displayErrorDetails) use ($app) {
  $statusCode = 500;
  $errorMessage = $exception->getMessage();
  $response = $app->getResponseFactory()->createResponse($statusCode);
  $response->getBody()->write(json_encode(['error' => $errorMessage]));

  return $response->withHeader('Content-Type', 'application/json');
};

$app->addErrorMiddleware(true, true, true)
  ->setDefaultErrorHandler($errorMiddleware);

$app->addBodyParsingMiddleware();



// ABM Routes
// Usuarios
$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . '::TraerTodos')->add(\Autentificador::class . '::ValidarAdmin');
  //$group->get('/{usuario}', \UsuarioController::class . '::TraerUno');
  $group->post('[/]', \UsuarioController::class . '::CargarUno')->add(\Validador::class . '::ValidarNuevoUsuario')->add(\Autentificador::class . '::ValidarAdmin');
  //$group->put('/{id}', \UsuarioController::class . '::ModificarUno');
  //$group->delete('/{id}', \UsuarioController::class . '::BorrarUno');
});
//Criptomoneda
$app->group('/criptomonedas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \CriptomonedaController::class . '::TraerTodos');
  $group->get('/nacionalidad[/]', \CriptomonedaController::class . '::TraerTodosPorNacionalidad');
  $group->get('/{id}', \CriptomonedaController::class . '::TraerUno')->add(\Autentificador::class . '::ValidarCliente');
  $group->post('[/]', \CriptomonedaController::class . '::CargarUno')->add(\Autentificador::class . '::ValidarAdmin');
});
// VentaCriptomoneda
$app->group('/ventascriptomonedas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \VentaCriptomonedaController::class . '::TraerTodos');
  //$group->get('/nacionalidad[/]', \CriptomonedaController::class . '::TraerTodosPorNacionalidad');
  $group->get('/{nacionalidad}/{fechaInicio}_{fechaFinal}[/]', \VentaCriptomonedaController::class . '::TraerTodosPorNacionalidadFecha');
  $group->get('/{moneda}[/]', \VentaCriptomonedaController::class . '::TraerTodosPorMoneda')->add(\Autentificador::class . '::ValidarAdmin');
  $group->post('[/]', \VentaCriptomonedaController::class . '::CargarUno')->add(\Validador::class . '::ValidarCantidad')->add(\Validador::class . '::ValidarIDCripto')->add(\Autentificador::class . '::ValidarCliente');
});



// LOG IN 
$app->group('/login', function (RouteCollectorProxy $group) {
  $group->post('[/]', \UsuarioController::class . '::LogIn')->add(\Logger::class . '::ValidarLogin');
});

// ADMIN
$app->group('/admin', function (RouteCollectorProxy $group) {
  $group->get('[/]', function ($request, $response, $args) {
    $user = new Usuario();
    $user->mail = "admin";
    $user->clave = "admin";
    $user->tipo = "admin";

    Usuario::crear($user);
    $payload = json_encode(array("mensaje" => "Admin creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });
});

// GENERICA
$app->get('[/]', function (Request $request, Response $response) {
  $payload = json_encode(array("SP" => "Segundo Parcial Programacion 3"));
  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->run();