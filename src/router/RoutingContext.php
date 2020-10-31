<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use MNC\PathToRegExpPHP\MatchResult;
use MNC\PathToRegExpPHP\NoMatchException;
use MNC\PathToRegExpPHP\PathRegExp;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * RoutingContext contains state about the routing process.
 */
class RoutingContext
{
    public const ATTR_NAME = 'legatus.router.context';

    private string $path;
    private array $allowedMethods;
    private array $parameters;

    /**
     * Injects the RoutingContext in the Request attribute.
     *
     * If the current Request already contains a RoutingContext, this method
     * will raise a RoutingContextOverride.
     *
     * @param Request $request
     *
     * @return Request The mutated request
     *
     * @internal you should not use this method in your own code
     *
     * @throws InvalidRoutingContextOverride
     */
    public static function inject(Request $request): Request
    {
        if ($request->getAttribute(self::ATTR_NAME) !== null) {
            throw new InvalidRoutingContextOverride('You cannot override the context of a Request');
        }
        $uri = $request->getUri();

        // Remove trailing slash
        if (substr($uri->getPath(), -1) !== '/') {
            $uri = $uri->withPath($uri->getPath().'/');
        }
        $context = new self($uri->getPath());

        return $request
            ->withAttribute(self::ATTR_NAME, $context);
    }

    /**
     * Extracts the RoutingContext from the given Request.
     *
     * If the Request does not contain a RoutingContext, a MissingRoutingContext
     * exception is thrown.
     *
     * @param Request $request
     *
     * @return RoutingContext
     *
     * @throws MissingRoutingContext when a context is not present in the Request
     */
    public static function of(Request $request): RoutingContext
    {
        $context = $request->getAttribute(self::ATTR_NAME);
        if ($context instanceof self) {
            return $context;
        }
        throw new MissingRoutingContext('Routing context is missing from the Request');
    }

    /**
     * RoutingContext constructor.
     *
     * @param string $path
     */
    protected function __construct(string $path)
    {
        $this->path = $path;
        $this->allowedMethods = [];
        $this->parameters = [];
    }

    /**
     * Check if regex path matches the current path stored in the context.
     *
     * @param PathRegExp $path
     *
     * @return MatchResult
     *
     * @throws NoMatchException
     */
    public function match(PathRegExp $path): MatchResult
    {
        return $path->match($this->path);
    }

    /**
     * @param MatchResult $result
     *
     * @internal
     */
    public function storeMatchResult(MatchResult $result): void
    {
        // Create a new URI for the next path to be matched
        $this->path = str_replace($result->getMatchedString(), '', $this->path);
        // Save the parameters if any
        foreach ($result->getValues() as $key => $value) {
            $this->parameters[$key] = $value;
        }
    }

    /**
     * @param string ...$methods
     *
     * @return RoutingContext
     */
    public function saveAllowedMethod(string ...$methods): RoutingContext
    {
        $this->allowedMethods = array_unique(array_merge($this->allowedMethods, $methods));

        return $this;
    }

    /**
     * @return bool
     */
    public function isMethodNotAllowed(): bool
    {
        return count($this->allowedMethods) > 0;
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws MissingRoutingParameter when parameter is not found
     */
    public function getParam(string $name): string
    {
        $value = $this->parameters[$name] ?? null;
        if ($value !== null) {
            return $value;
        }
        throw new MissingRoutingParameter(sprintf('Parameter named %s has not been found', $name));
    }
}
