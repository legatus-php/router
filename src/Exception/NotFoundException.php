<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Exception;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class NotFoundException.
 */
class NotFoundException extends RoutingException
{
    /**
     * NotFoundException constructor.
     *
     * @param ServerRequestInterface $request
     * @param Throwable|null         $previous
     */
    public function __construct(ServerRequestInterface $request, Throwable $previous = null)
    {
        parent::__construct($request, 404, $previous);
    }
}
