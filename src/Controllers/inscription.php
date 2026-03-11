<?php
declare(strict_types=1);

use App\Model\StudentRegistrationModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/src/Models/StudentRegistrationModel.php';

Dotenv::createImmutable($projectRoot)->safeLoad();

$loader = new FilesystemLoader($projectRoot . '/templates');
$twig = new Environment($loader);

$form = [
    'statut' => 'etudiant',
    'nom' => '',
    'prenom' => '',
    'classe' => '',
    'pilot_id' => '',
    'email' => '',
    'accept_terms' => false,
    'mode' => 'particulier',
];

$errors = [];
$successMessage = '';
$pilots = [];
$registrationModel = null;

try {
    $registrationModel = StudentRegistrationModel::fromEnv($_ENV);
    $pilots = $registrationModel->getPilots();
} catch (Throwable $exception) {
    $errors[] = $exception->getMessage();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $form['mode'] = isset($_POST['mode']) ? trim((string) $_POST['mode']) : 'particulier';

    if ($form['mode'] !== 'particulier') {
        $errors[] = 'Inscription entreprise non disponible pour le moment.';
    } else {
        $form['statut'] = isset($_POST['statut']) ? trim((string) $_POST['statut']) : 'etudiant';
        $form['nom'] = isset($_POST['nom']) ? trim((string) $_POST['nom']) : '';
        $form['prenom'] = isset($_POST['prenom']) ? trim((string) $_POST['prenom']) : '';
        $form['classe'] = isset($_POST['classe']) ? trim((string) $_POST['classe']) : '';
        $form['pilot_id'] = isset($_POST['pilot_id']) ? trim((string) $_POST['pilot_id']) : '';
        $form['email'] = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        $passwordConfirmation = isset($_POST['password_confirm']) ? (string) $_POST['password_confirm'] : '';
        $form['accept_terms'] = isset($_POST['accept_terms']);

        if (!in_array($form['statut'], ['etudiant', 'pilote'], true)) {
            $errors[] = 'Le statut selectionne est invalide.';
        }

        if ($form['nom'] === '' || $form['prenom'] === '') {
            $errors[] = 'Le nom et le prenom sont obligatoires.';
        }

        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email est invalide.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
        }

        if ($password !== $passwordConfirmation) {
            $errors[] = 'La confirmation du mot de passe ne correspond pas.';
        }

        if (!$form['accept_terms']) {
            $errors[] = 'Tu dois accepter les conditions d\'utilisation.';
        }

        $pilotId = null;
        if ($form['statut'] === 'etudiant') {
            if ($form['classe'] === '') {
                $errors[] = 'La classe est obligatoire pour un etudiant.';
            }

            if ($form['pilot_id'] === '') {
                $errors[] = 'Tu dois selectionner un pilote.';
            } elseif (!ctype_digit($form['pilot_id'])) {
                $errors[] = 'Le pilote selectionne est invalide.';
            } else {
                $pilotId = (int) $form['pilot_id'];
            }

            if (count($pilots) === 0) {
                $errors[] = 'Aucun pilote disponible. Cree un compte pilote en premier.';
            }
        }

        if (empty($errors) && $registrationModel !== null) {
            try {
                $registrationModel->registerParticulier(
                    $form['nom'],
                    $form['prenom'],
                    $form['email'],
                    $password,
                    $form['statut'],
                    $form['classe'] !== '' ? $form['classe'] : null,
                    $pilotId
                );

                if ($form['statut'] === 'etudiant') {
                    $successMessage = 'Inscription etudiant enregistree dans PROFIL, PARTICULIER et ETUDIANTS.';
                } else {
                    $successMessage = 'Inscription pilote enregistree dans PROFIL, PARTICULIER et PILOTS.';
                    $pilots = $registrationModel->getPilots();
                }

                $form = [
                    'statut' => 'etudiant',
                    'nom' => '',
                    'prenom' => '',
                    'classe' => '',
                    'pilot_id' => '',
                    'email' => '',
                    'accept_terms' => false,
                    'mode' => 'particulier',
                ];
            } catch (RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    }
}

echo $twig->render('Connexion/inscription.twig', [
    'form' => $form,
    'errors' => $errors,
    'success_message' => $successMessage,
    'pilots' => $pilots,
]);
