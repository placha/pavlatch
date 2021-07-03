<?php

namespace pavlatch;

use pavlatch\Exception\ServerException;

class RouteResolver
{
    private string $requestMethod;

    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @throws ServerException
     */
    public function getRoute(): Route
    {
        foreach ($this->routes() as $route) {
            if ($this->requestMethod !== $route->getMethod()) {
                continue;
            }
            if (($_POST['action'] ?? $_GET['action'] ?? null) !== $route->getActionName()) {
                continue;
            }
            return $route;
        }

        return $this->getDefaultRoute();
    }

    /**
     * @return Route[]
     */
    private function routes(): array
    {
        return [
            new Route('GET', 'count', false),
            new Route('GET', 'view', false),
            new Route('GET', 'exist', false),
        ];
    }

    /**
     * @return Route
     * @throws ServerException
     */
    private function getDefaultRoute(): Route
    {
        if ($this->requestMethod === 'GET') {
            return new Route('GET', 'get', false);
        }
        if ($this->requestMethod === 'POST') {
            return new Route('POST', 'upload', true);
        }
        throw new ServerException('Invalid method', 404);
    }
}

