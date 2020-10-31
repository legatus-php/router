<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Legatus\Http\Router;
use Legatus\Http\RoutingContext;
use Legatus\Http\RoutingContextError;
use Psr\Http\Message\ResponseInterface as Resp;
use Psr\Http\Message\ServerRequestInterface as Req;
use function Legatus\Http\handle_func;

/**
 * @param Req $req
 * @return Resp
 * @throws RoutingContextError
 */
function show_user(Req $req): Resp {
    $id = RoutingContext::of($req)->getParam('id');
    return new Nyholm\Psr7\Response(200, [], 'Hello User ' . $id);
}

$router = new Router();
$router->get('/users/:id', handle_func('show_user'));

$request = new Nyholm\Psr7\ServerRequest('GET', '/users/1');
$response = $router->handle($request);

echo $response->getBody() . PHP_EOL; // Hello User 1