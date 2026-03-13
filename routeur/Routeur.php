<?php

namespace Routeur;

use exceptions\RouteurNotFoundException;


class Routeur 
{
    private array $routes = [];

    public function register(string $path, callable|array $action): void
    {
        $this->routes[$path] = $action;
    }

    public function run(string $uri): mixed 
    {
        $path = explode('?', $uri)[0]; // on garde que la partie avant le ? dans l'url pour eviter les problèmes
        $path = '/' . ltrim($path, '/');

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
    
        $action = $this->routes[$path] ?? null;

        if (is_callable($action)) {
            return $action();
        }

        else if (is_array($action)) { //&& count($action) === 2
            [$class, $method] = $action;
            if (class_exists($class) && method_exists($class, $method)) {
                $instance = new $class();
                return call_user_func_array([$instance, $method], []);
            }
        }

        // Si aucune route n'est trouvée, erreur
        throw new RouteurNotFoundException();
    }
}