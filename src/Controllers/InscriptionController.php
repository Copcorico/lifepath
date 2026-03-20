<?php
declare(strict_types=1);

use App\Controllers\AuthController;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable($projectRoot);
$dotenv->load();

// Créer la connexion PDO
$dsn = $_ENV['DB_DSN'] ?? "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4";
$db = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);

// Créer le moteur Twig
$loader = new \Twig\Loader\FilesystemLoader($projectRoot . '/templates');
$twig = new \Twig\Environment($loader);

$authController = new AuthController($twig, $db);
$authController->inscription();
