<?php

use Routeur\Routeur;
use exceptions\RouteurNotFoundException;

require dirname(__DIR__) . '/vendor/autoload.php';

$routeur = new Routeur();

$routeur->register('/', ['App\Controllers\routeurController', 'welcomePage']);

$routeur->register('/connexion', ['App\Controllers\routeurController', 'connexionPage']);

$routeur->register('/inscription', ['App\Controllers\routeurController', 'inscriptionPage']);

$routeur->register('/entreprise', ['App\Controllers\routeurController', 'entreprisePage']);

$routeur->register('/contact', ['App\Controllers\routeurController', 'contactPage']);

$routeur->register('/offres', ['App\Controllers\routeurController', 'offresPage']);

$routeur->register('/avis', ['App\Controllers\routeurController', 'avisPage']);

$routeur->register('/legale', ['App\Controllers\routeurController', 'legalePage']);

$routeur->register('/profil', ['App\Controllers\routeurController', 'profilPage']);

$routeur->register('/deconnexion', ['App\Controllers\routeurController', 'deconnexion']);

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
];

foreach ($legacyRoutes as $path => $action) {
    $routeur->register($path, $action);
}

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

try {
    echo $routeur->run($uri);
} catch (RouteurNotFoundException $e) {
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