<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\RequestHandlerInterface as Next;

$router = Legatus\Http\Router\create();

$router->use(static function (Req $req, Next $next) {
    // Do something with the request in this middleware
    return $next->handle($req);
});

$router->get('/users/:id', static function (string $id) {
    // Create a response in this route handler
    return new Nyholm\Psr7\Response(200, [], 'Hello User ' . $id);
});

// Its highly recommended to stop the routing at the end
$router->stop();

$request = new Nyholm\Psr7\ServerRequest('GET', '/users/1');
$response = $router->handle($request);

echo $response->getBody() . PHP_EOL; // Hello User 1