<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

require __DIR__.'/../vendor/autoload.php';

$app = new \Slim\App();
$container = $app->getContainer();

$container['upload_directory'] = __DIR__ . '/uploads/img';

$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response, $methods) use ($container) {
        return $response->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withJson(['message' => 'Method must be one of: ' . implode(', ', $methods)]);
    };
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $response->withStatus(404)
            ->withJson(['message' => 'Endpint not found']);
    };
};

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*') // put client domain here
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/manufacturers', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Manufacturer.php");
    $manufacturer = new Manufacturer();
    $res = $manufacturer->index();
    return $response->withJson($res['response'], $res['code']);
});

$app->post('/manufacturers/add', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Manufacturer.php");
    $manufacturer = new Manufacturer();
    $postData = json_decode(file_get_contents('php://input'), true);
    $res = $manufacturer->add($postData);
    return $response->withJson($res['response'], $res['code']);
});

$app->get('/models', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Model.php");
    $mdoel = new Model();
    $res = $mdoel->index();
    return $response->withJson($res['response'], $res['code']);
});

$app->post('/models/add', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Model.php");
    $model = new Model();
    $postData = json_decode(file_get_contents('php://input'), true);
    $res = $model->add($postData);
    return $response->withJson($res['response'], $res['code']);
});

$app->get('/cars', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Car.php");
    $car = new Car();
    $res = $car->index($_GET);
    return $response->withJson($res['response'], $res['code']);
});

$app->get('/cars/dashboard', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Car.php");
    $car = new Car();
    $res = $car->dashboard();
    return $response->withJson($res['response'], $res['code']);
});

$app->post('/cars/add', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Car.php");
    $car = new Car();
    $res = $car->add();
    return $response->withJson($res['response'], $res['code']);
});

$app->post('/cars/upload_image', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Car.php");
    $car = new Car();
    $res = $car->uploadFile($request, $this->get('upload_directory'));
    return $response->withJson($res['response'], $res['code']);
});

$app->options('/cars/{id}', function (Request $request, Response $response, array $args) {
    return $response->withJson([]);
});

$app->delete('/cars/{id}', function (Request $request, Response $response, array $args) {
    require_once(__DIR__."/../models/Car.php");
    $car = new Car();
    $res = $car->delete($args['id']);
    return $response->withJson($res['response'], $res['code']);
});

$app->run();
