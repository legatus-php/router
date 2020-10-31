<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Throwable;

/**
 * PlainTextErrorHandler returns plain text responses when errors occur.
 *
 * You should use this error handler only for fast prototyping. Usually, you would
 * implement your own error handler middleware to handle errors in an appropriate
 * fashion for your own application.
 *
 * This handler is mostly intended for development and debugging purposes, and to
 * serve as an example on how you could build your own error handler.
 */
final class PlainTextErrorHandler implements MiddlewareInterface
{
    private ResponseFactoryInterface $response;
    private bool $trace;

    /**
     * PlainTextErrorHandler constructor.
     *
     * @param ResponseFactoryInterface $response
     * @param bool                     $trace
     */
    public function __construct(ResponseFactoryInterface $response, bool $trace = true)
    {
        $this->response = $response;
        $this->trace = $trace;
    }

    /**
     * @param Request $request
     * @param Handler $handler
     *
     * @return Response
     */
    public function process(Request $request, Handler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            if (!$e instanceof HttpError) {
                $e = new InternalServerError($request, null, $e);
            }
            $string = $this->createErrorText($e);
            $code = (int) $e->getCode();
            $response = $this->response->createResponse($code);
            $response->getBody()->write($string);

            return $response
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Length', (string) strlen($string));
        }
    }

    /**
     * @param HttpError $error
     *
     * @return string
     */
    private function createErrorText(HttpError $error): string
    {
        $message = sprintf('HTTP ERROR %s: %s', $error->getCode(), $error->getMessage()).PHP_EOL;

        if ($this->trace === true) {
            $message .= PHP_EOL;
            $message .= $error->getTraceAsString();

            while (($error = $error->getPrevious()) !== null) {
                $message .= PHP_EOL;
                $message .= $error->getPrevious();
            }
        }

        return $message;
    }
}
