<?php
declare(strict_types=1);

// Active l'affichage des erreurs en local pour diagnostiquer les 500.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    exit("Configuration manquante : fichier introuvable -> $autoloadPath\n"
        . "Installe les dependances avec: composer install");
}

require_once $autoloadPath;

if (!class_exists(Dotenv\Dotenv::class)) {
    http_response_code(500);
    exit('phpdotenv est introuvable. Verifie composer.json et execute composer install.');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$requiredKeys = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($requiredKeys as $key) {
    if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
        http_response_code(500);
        exit("Variable .env manquante ou vide : $key");
    }
}

$dsn = $_ENV['DB_DSN'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query('SELECT * FROM ETUDIANTS');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo 'Connexion BDD OK. Lignes recuperees : ' . count($rows);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Erreur PDO : ' . $e->getMessage());
}
?> 