<?php
declare(strict_types=1);

use App\Model\RegistrationModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function postString(string $name, string $default = ''): string
{
    return trim((string) ($_POST[$name] ?? $default));
}

function postBool(string $name): bool
{
    return isset($_POST[$name]);
}

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/src/Models/RegistrationModel.php';

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

$entrepriseForm = [
    'societe' => '',
    'adresse' => '',
    'email' => '',
    'telephone' => '',
    'nombre_employes' => '',
];

$errors = [];
$successMessage = '';
$pilots = [];
$registrationModel = null;

try {
    $registrationModel = RegistrationModel::fromEnv($_ENV);
    $pilots = $registrationModel->getPilots();
} catch (Throwable $exception) {
    $errors[] = $exception->getMessage();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $form['mode'] = postString('mode', 'particulier');

    if ($form['mode'] !== 'particulier' && $form['mode'] !== 'entreprise') {
        $form['mode'] = 'particulier';
    }

    if ($form['mode'] === 'entreprise') {
        $entrepriseForm['societe'] = postString('societe');
        $entrepriseForm['adresse'] = postString('adresse');
        $entrepriseForm['email'] = postString('email-pro');
        $entrepriseForm['telephone'] = postString('telephone-pro');
        $entrepriseForm['nombre_employes'] = postString('nombre_employes');
        $passwordEntreprise = (string) ($_POST['password-pro'] ?? '');
        $passwordEntrepriseConfirmation = (string) ($_POST['password-pro-confirm'] ?? '');

        if ($entrepriseForm['societe'] === '') {
            $errors[] = 'Le nom de la societe est obligatoire.';
        }

        if ($entrepriseForm['adresse'] === '') {
            $errors[] = 'L\'adresse du siege social est obligatoire.';
        }

        if (!filter_var($entrepriseForm['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email entreprise est invalide.';
        }

        if ($entrepriseForm['telephone'] === '') {
            $errors[] = 'Le telephone est obligatoire pour une entreprise.';
        }

        if (strlen($passwordEntreprise) < 8) {
            $errors[] = 'Le mot de passe entreprise doit contenir au moins 8 caracteres.';
        }

        if ($passwordEntreprise !== $passwordEntrepriseConfirmation) {
            $errors[] = 'La confirmation du mot de passe entreprise ne correspond pas.';
        }

        $nombreEmployes = null;
        if ($entrepriseForm['nombre_employes'] !== '') {
            if (!ctype_digit($entrepriseForm['nombre_employes'])) {
                $errors[] = 'Le nombre d\'employes est invalide.';
            } else {
                $nombreEmployes = (int) $entrepriseForm['nombre_employes'];
            }
        }

        if (empty($errors) && $registrationModel !== null) {
            try {
                $registrationModel->registerEntreprise(
                    $entrepriseForm['societe'],
                    $entrepriseForm['email'],
                    $entrepriseForm['telephone'],
                    $passwordEntreprise,
                    $nombreEmployes
                );

                $successMessage = 'Inscription entreprise enregistree.';
                $form['mode'] = 'entreprise';
                $entrepriseForm = [
                    'societe' => '',
                    'adresse' => '',
                    'email' => '',
                    'telephone' => '',
                    'nombre_employes' => '',
                ];
            } catch (RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    } else {
        $form['statut'] = postString('statut', 'etudiant');
        $form['nom'] = postString('nom');
        $form['prenom'] = postString('prenom');
        $form['classe'] = postString('classe');
        $form['pilot_id'] = postString('pilot_id');
        $form['email'] = postString('email');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirm'] ?? '');
        $form['accept_terms'] = postBool('accept_terms');

        if ($form['statut'] !== 'etudiant' && $form['statut'] !== 'pilote') {
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
                    $successMessage = 'Inscription etudiant enregistrée.';
                } else {
                    $successMessage = 'Inscription pilote enregistrée.';
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
    'entreprise_form' => $entrepriseForm,
    'errors' => $errors,
    'success_message' => $successMessage,
    'pilots' => $pilots,
]);
