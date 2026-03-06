<?php
// 1. On charge l'outil Composer (qui inclut phpdotenv)
require_once __DIR__ . '/vendor/autoload.php';

// 2. On indique où se trouve le fichier .env et on le charge
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 3. On récupère les informations de manière sécurisée
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

// 4. On lance la connexion PDO habituelle
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->query("SELECT * FROM ETUDIANT"); // Juste pour tester la connexion
    $pdo->fetchAll(); // On récupère les résultats pour s'assurer que la requête fonctionne
    echo $pdo;
    
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>