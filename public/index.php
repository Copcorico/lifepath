<?php

use Routeur\Routeur;
use exception\RouteurNotFoundException;

require "./../vendor/autoload.php";

$routeur = new Routeur();

$routeur->register('/', function() {
    return 'Welcome page';
});

$routeur->register('/entreprise', function() {
    return 'Entreprise page';
});

$routeur->register('/contact', function() {
    return 'Contact page';
});

$routeur->register('/offres', function() {
    return 'Offres page';
});

$routeur->register('/avis', function() {
    return 'Avis page';
});

try {
    echo $routeur->run($_SERVER['REQUEST_URI']);
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