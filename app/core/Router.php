<?php

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $basePath = rtrim(config('base_path', ''), '/');
        if ($basePath && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        $path = '/' . ltrim($path, '/');

        foreach ($this->routes as $route) {
            $pattern = "#^" . preg_replace('#\{[a-zA-Z_]+\}#', '([\\w-]+)', $route['path']) . "$/";
            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        view('errors/404');
    }
}
