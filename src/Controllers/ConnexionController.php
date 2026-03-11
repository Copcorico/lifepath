<?php
declare(strict_types=1);

use App\Model\LoginModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/src/Models/LoginModel.php';

Dotenv::createImmutable($projectRoot)->safeLoad();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loader = new FilesystemLoader($projectRoot . '/templates');
$twig = new Environment($loader);

$form = [
    'email' => '',
];

$errors = [];
$successMessage = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    }

    if ($password === '') {
        $errors[] = 'Le mot de passe est obligatoire.';
    }

    if (empty($errors)) {
        try {
            $loginModel = LoginModel::fromEnv($_ENV);
            $user = $loginModel->authenticate($form['email'], $password);

            $_SESSION['user_id'] = $user['id_profil'];
            $_SESSION['user_email'] = $user['email'];

            $successMessage = 'Connexion reussie.';
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

echo $twig->render('Connexion/connexion.twig', [
    'form' => $form,
    'errors' => $errors,
    'success_message' => $successMessage,
]);
