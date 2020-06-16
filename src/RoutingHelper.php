<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;

/**
 * Trait RoutingHelper.
 *
 * Helper used to extract useful information related to routing from the
 * request.
 *
 * It's use in your middleware is encouraged.
 */
trait RoutingHelper
{
    /**
     * @param Request $request
     *
     * @return UriInterface
     */
    public function getRoutingUri(Request $request): UriInterface
    {
        return $request->getAttribute(
            QuiltRouter::ROUTING_URI_ATTR,
            $request->getUri()
        );
    }

    /**
     * Check if the request matched a path but not a method.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isMethodNotAllowed(Request $request): bool
    {
        return $request->getAttribute(QuiltRouter::METHOD_NOT_ALLOWED_ATTR) !== null;
    }

    /**
     * Returns the allowed methods for the matched path.
     *
     * @param Request $request
     *
     * @return array
     */
    public function getAllowedMethods(Request $request): array
    {
        return $request->getAttribute(QuiltRouter::METHOD_NOT_ALLOWED_ATTR, []);
    }

    /**
     * Modifies the URI of the Request.
     *
     * @param Request      $request
     * @param UriInterface $uri
     *
     * @return Request
     */
    public function setRoutingUri(Request $request, UriInterface $uri): Request
    {
        return $request->withAttribute(QuiltRouter::ROUTING_URI_ATTR, $uri);
    }
}
