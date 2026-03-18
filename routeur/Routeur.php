<?php

namespace Routeur;

use exceptions\RouteurNotFoundException;


class Routeur 
{
    private array $routes = [];
    private $db = null;
    private $twig = null;

    public function __construct($db = null, $twig = null)
    {
        $this->db = $db;
        $this->twig = $twig;
    }

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
                // Essayer de passer les dépendances au constructeur
                try {
                    if ($this->db && $this->twig) {
                        $instance = new $class($this->twig, $this->db);
                    } else {
                        $instance = new $class();
                    }
                } catch (\Exception $e) {
                    $instance = new $class();
                }
                return call_user_func_array([$instance, $method], []);
            }
        }

        // Si aucune route n'est trouvée, erreur
        throw new RouteurNotFoundException();
    }
}