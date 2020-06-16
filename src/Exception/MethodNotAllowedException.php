<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Router\Exception;

use Legatus\Http\Router\RoutingHelper;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class MethodNotAllowedException.
 */
class MethodNotAllowedException extends RoutingException
{
    use RoutingHelper;

    /**
     * @var string[]
     */
    private array $allowed;

    /**
     * MethodNotAllowedException constructor.
     *
     * @param ServerRequestInterface $request
     * @param Throwable|null         $previous
     */
    public function __construct(ServerRequestInterface $request, Throwable $previous = null)
    {
        parent::__construct($request, 405, $previous);
        $this->message .= sprintf(' (Try: %s)', implode(', ', $this->getAllowed()));
    }

    /**
     * @return string[]
     */
    public function getAllowed(): array
    {
        return $this->getAllowedMethods($this->getRequest());
    }
}
