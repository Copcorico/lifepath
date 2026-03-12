<?php
/**
 * This is the router, the main entry point of the application.
 * It handles the routing and dispatches requests to the appropriate controller methods.
 */

require "vendor/autoload.php";

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