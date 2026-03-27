<?php

namespace Routeur;

use exceptions\RouteurNotFoundException;

/* 
    Routeur.php - Classe de routage pour l'application LifePath

    Cette classe gère les routes de l'application, 
    en associant des chemins d'URL à des actions spécifiques 
    (fonctions ou méthodes de contrôleurs). 
    Elle permet de centraliser la logique de routage et de faciliter la maintenance du code.

    Fonctionnalités principales :
    - Enregistrement des routes avec des actions associées
    - Exécution de l'action correspondante à une route donnée
    - Gestion des dépendances (base de données, moteur de templates) pour les contrôleurs
    - Normalisation des chemins d'URL pour une correspondance cohérente
*/
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
        // Normaliser le chemin de l'URI pour correspondre aux routes enregistrées
        $path = $this->normalizePath($uri);
    
        $action = $this->routes[$path] ?? null;

        if (is_callable($action)) {
            return $action();
        }

        if (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            if (class_exists($class) && method_exists($class, $method)) {
                // Essayer de passer les dépendances au constructeur
                try {
                    if ($this->twig !== null && $this->db !== null) {
                        $instance = new $class($this->twig, $this->db);
                    } else {
                        $instance = new $class();
                    }
                } catch (\Exception $e) {
                    $instance = new $class();
                }
                return $instance->$method();
            }
        }

        throw new RouteurNotFoundException();
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getTwig()
    {
        return $this->twig;
    }

    public function setDb($db): void
    {
        $this->db = $db;
    }

    public function setTwig($twig): void
    {
        $this->twig = $twig;
    }

    private function normalizePath(string $uri): string
    {
        $path = explode('?', $uri)[0];
        $path = '/' . ltrim($path, '/');

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    public function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = explode('?', $uri)[0];
        return $this->normalizePath($path);
    }

    public function handleRequest(): void
    {
        $path = $this->parseUri();

        // Traiter les routes spéciales en priorité
        if ($path === '/inscription' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new \App\Controllers\AuthController($this->twig, $this->db);
            $authController->inscription();
            exit;
        }

        if ($path === '/inscription' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $routeurController = new \App\Controllers\routeurController($this->twig, $this->db);
            $routeurController->inscriptionPage();
            exit;
        }

        if ($path === '/connexion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new \App\Controllers\AuthController($this->twig, $this->db);
            $authController->connexion();
            exit;
        }

        if ($path === '/connexion' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $routeurController = new \App\Controllers\routeurController($this->twig, $this->db);
            $routeurController->connexionPage();
            exit;
        }

        if ($path === '/deconnexion') {
            session_destroy();
            header('Location: /');
            exit;
        }
            // Traiter les autres routes via le routeur
        $this->run($path);

    }
}