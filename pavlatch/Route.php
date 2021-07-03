<?php

namespace pavlatch;

class Route
{
    private string $method;
    private string $actionName;
    private bool $secure;

    public function __construct(string $method, string $actionName, bool $secure = false)
    {
        $this->method = $method;
        $this->actionName = $actionName;
        $this->secure = $secure;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function isAllowed(string $secureKey): bool
    {
        if ($this->secure) {
            return !(($_POST['secureKey'] ?? null) !== $secureKey);
        }
        return true;
    }
}

