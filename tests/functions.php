<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Legatus\Http\HttpError;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\RequestHandlerInterface as Next;

/**
 * @param Req  $req
 * @param Next $next
 *
 * @return ResponseInterface
 */
function handle_error_html(Req $req, Next $next): ResponseInterface
{
    try {
        return $next->handle($req);
    } catch (HttpError $exception) {
        return html($exception->getMessage())
            ->withStatus($exception->getCode());
    }
}

/**
 * @param Req  $req
 * @param Next $next
 *
 * @return ResponseInterface
 *
 * @throws JsonException
 */
function handle_error_json(Req $req, Next $next): ResponseInterface
{
    try {
        return $next->handle($req);
    } catch (HttpError $exception) {
        return json(['msg' => $exception->getMessage()])
            ->withStatus($exception->getCode());
    }
}

/**
 * @param string $body
 * @param int    $status
 *
 * @return ResponseInterface
 */
function response(string $body, int $status = 200): ResponseInterface
{
    return new Response($status, [], $body);
}

/**
 * @param array|null $data
 * @param int        $status
 *
 * @return ResponseInterface
 *
 * @throws JsonException
 */
function json(array $data = null, int $status = 200): ResponseInterface
{
    return response($data ? json_encode($data, JSON_THROW_ON_ERROR) : '', $status)
        ->withHeader('Content-Type', 'application/json');
}

/**
 * @param string $html
 * @param int    $status
 *
 * @return ResponseInterface
 */
function html(string $html, int $status = 200): ResponseInterface
{
    return response($html, $status)
        ->withHeader('Content-Type', 'text/html');
}

/**
 * @param string $uri
 *
 * @return ResponseInterface
 */
function redirect(string $uri): ResponseInterface
{
    return html('', 302)
        ->withHeader('Location', $uri);
}
