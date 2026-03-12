<?php

use exception\RouteurNotFoundException;

namespace Routeur;

class Routeur 
{
    private array $routes;

    public function register(string $path, callable $action): void
    {
        $this->routes[$path] = $action;
    }

    public function run(string $uri): mixed 
    {
        $path = explode('?', $uri)[0]; // on garde que la partie avant le ? dans l'url pour eviter les problèmes
    
        $action = $this->routes[$path] ?? null;

        if (!is_callable($action)) {
            // Si aucune route n'est trouvée, erreur
            throw new RouteurNotFoundException();
        }

        return $action();
    }
}