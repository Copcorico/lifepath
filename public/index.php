<?php

use Routeur\Routeur;
use exceptions\RouteurNotFoundException;
use App\Controllers\routeurController;

require dirname(__DIR__) . '/vendor/autoload.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger les variables d'environnement
$projectRoot = dirname(__DIR__);
$dotenv = \Dotenv\Dotenv::createImmutable($projectRoot);
$dotenv->load();

// Créer la connexion PDO
$dsn = $_ENV['DB_DSN'] ?? "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4";
$db = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);

// Créer le moteur Twig
$loader = new \Twig\Loader\FilesystemLoader($projectRoot . '/templates');
$twig = new \Twig\Environment($loader);

// Initialiser Twig
// Ajouter les variables globales Twig
$twig->addGlobal('app', [
    'session' => new \App\Twig\SessionBag()
]);

// Parser l'URI et extraire le chemin de route (une seule fois)
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

if ($basePath !== '' && $basePath !== '.' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

if ($uri === '/index.php') {
    $uri = '/';
} elseif (str_starts_with($uri, '/index.php/')) {
    $uri = substr($uri, strlen('/index.php'));
}

$uri = '/' . ltrim((string) $uri, '/');
$path = explode('?', $uri)[0];
$path = '/' . ltrim($path, '/');
if ($path !== '/') {
    $path = rtrim($path, '/');
}

// Traiter POST /inscription avec AuthController
if ($path === '/inscription' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new \App\Controllers\AuthController($twig, $db);
    $authController->inscription();
    exit;
}

// Traiter GET /inscription avec routeurController ayant la BD
if ($path === '/inscription' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $routeurController = new \App\Controllers\routeurController($twig, $db);
    $routeurController->inscriptionPage();
    exit;
}

// Traiter POST /connexion avec AuthController
if ($path === '/connexion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new \App\Controllers\AuthController($twig, $db);
    $authController->connexion();
    exit;
}

// Traiter GET /connexion avec routeurController ayant la BD
if ($path === '/connexion' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $routeurController = new \App\Controllers\routeurController($twig, $db);
    $routeurController->connexionPage();
    exit;
}

// Traiter /creeroffre
if ($path === '/creeroffre' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $routeurController = new \App\Controllers\routeurController($twig, $db);
    $routeurController->creerOffrePage();
    exit;
}

if ($path === '/creeroffre' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $offerController = new \App\Controllers\OfferController($twig, $db);
    $offerController->createOfferFromForm();
    exit;
}

// Traiter /editoffre/{id}
if (preg_match('#^/editoffre/(\d+)$#', $path, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $offerId = (int) $matches[1];
        $routeurController = new \App\Controllers\routeurController($twig, $db);
        $routeurController->editOffrePage($offerId);
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $offerId = (int) $matches[1];
        $offerController = new \App\Controllers\OfferController($twig, $db);
        $offerController->editOfferFromForm($offerId);
        exit;
    }
}

// Traiter /deleteoffre/{id}
if (preg_match('#^/deleteoffre/(\d+)$#', $path, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $offerId = (int) $matches[1];
    $offerController = new \App\Controllers\OfferController($twig, $db);
    $offerController->deleteOfferFromForm($offerId);
    exit;
}

// Traiter /déconnexion
if ($path === '/deconnexion') {
    $routeurController = new \App\Controllers\routeurController($twig, $db);
    $routeurController->deconnexion();
    exit;
}

$routeur = new Routeur($db, $twig);

$routeur->register('/', ['App\Controllers\routeurController', 'welcomePage']);
$routeur->register('/connexion', ['App\Controllers\routeurController', 'connexionPage']);
$routeur->register('/inscription', ['App\Controllers\routeurController', 'inscriptionPage']);
$routeur->register('/deconnexion', ['App\Controllers\routeurController', 'deconnexion']);
$routeur->register('/entreprise', ['App\Controllers\routeurController', 'entreprisePage']);
$routeur->register('/contact', ['App\Controllers\routeurController', 'contactPage']);
$routeur->register('/offres', ['App\Controllers\routeurController', 'offresPage']);
$routeur->register('/avis', ['App\Controllers\routeurController', 'avisPage']);
$routeur->register('/legale', ['App\Controllers\routeurController', 'legalePage']);
$routeur->register('/profil', ['App\Controllers\routeurController', 'profilPage']);
$routeur->register('/profil/update', ['App\Controllers\ProfilController', 'updateProfil']);
$routeur->register('/profil/photo', ['App\Controllers\ProfilController', 'uploadPhoto']);
$routeur->register('/mes_etudiants', ['App\Controllers\routeurController', 'mesEtudiantsPage']);
$routeur->register('/mes-etudiants', ['App\Controllers\routeurController', 'mesEtudiantsPage']);
$routeur->register('/a_propos', ['App\Controllers\routeurController', 'aProposPage']);
$routeur->register('/gereroffres', ['App\Controllers\routeurController', 'gererOffresPage']);
$routeur->register('/creeroffre', ['App\Controllers\routeurController', 'creerOffrePage']);

// Keep legacy links functional while templates are progressively migrated.
$legacyRoutes = [
    '/index.html' => ['App\Controllers\routeurController', 'welcomePage'],
    '/connexion.html' => ['App\Controllers\routeurController', 'connexionPage'],
    '/inscription.html' => ['App\Controllers\routeurController', 'inscriptionPage'],
    '/offres.html' => ['App\Controllers\routeurController', 'offresPage'],
    '/entreprise.html' => ['App\Controllers\routeurController', 'entreprisePage'],
    '/avis.html' => ['App\Controllers\routeurController', 'avisPage'],
    '/legale.html' => ['App\Controllers\routeurController', 'legalePage'],
    '/contact.html' => ['App\Controllers\routeurController', 'contactPage'],
    '/src/Controllers/InscriptionController.php' => ['App\Controllers\routeurController', 'inscriptionPage'],
    '/src/Controllers/ConnexionController.php' => ['App\Controllers\routeurController', 'connexionPage'],
    '/templates/Connexion/connexion.twig' => ['App\Controllers\routeurController', 'connexionPage'],
    '/templates/Offres/offres.twig' => ['App\Controllers\routeurController', 'offresPage'],
    '/templates/Entreprises/entreprise.twig' => ['App\Controllers\routeurController', 'entreprisePage'],
    '/templates/Avis/avis.twig' => ['App\Controllers\routeurController', 'avisPage'],
    '/templates/Legale/legale.twig' => ['App\Controllers\routeurController', 'legalePage'],
    '/templates/Contact/contact.twig' => ['App\Controllers\routeurController', 'contactPage'],
    '/templates/Profil/profil.twig' => ['App\Controllers\routeurController', 'profilPage'],
    '/templates/MesEtudiants/mes_etudiants.twig' => ['App\Controllers\routeurController', 'mesEtudiantsPage'],
    '/templates/APropos/a_propos.twig' => ['App\Controllers\routeurController', 'aProposPage'],

];

foreach ($legacyRoutes as $legacyPath => $action) {
    $routeur->register($legacyPath, $action);
}

try {
    echo $routeur->run($uri);
} catch (RouteurNotFoundException $e) {
    http_response_code(404);
    echo $e->getMessage();
}

/*
use App\Controllers\routeurController;

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true
]);

if (isset($_GET['uri'])) {
    $uri = $_GET['uri'];
} else {
    $uri = '/';
}

$controller = new routeController($twig);

switch ($uri) {
    case '/':
        echo 'Welcome page';
        $controller->welcomePage();
        break;
    case 'entreprise':
        echo 'Entreprise page';
        $controller->entreprisePage();
        break;
    case 'contact':
        echo 'Contact page';
        $controller->contactPage();
        break;
    case 'offres':
        echo 'Offres page';
        $controller->offresPage();
        break;
    case 'avis':
        echo 'Avis page';
        $controller->avisPage();
        break;
    default:
        //  return a 404 error
        echo '404 Not Found';
        break;
}
        */