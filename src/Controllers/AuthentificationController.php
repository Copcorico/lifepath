<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Model\LoginModel;
use App\Model\RegistrationModel;
use Dotenv\Dotenv;
use RuntimeException;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AuthentificationController extends Controller
{
    private string $projectRoot;

    private Environment $twig;

    public function __construct(?Environment $templateEngine = null)
    {
        $this->projectRoot = dirname(__DIR__, 2);

        require_once $this->projectRoot . '/vendor/autoload.php';
        require_once $this->projectRoot . '/src/Models/LoginModel.php';
        require_once $this->projectRoot . '/src/Models/RegistrationModel.php';

        Dotenv::createImmutable($this->projectRoot)->safeLoad();

        if ($templateEngine === null) {
            $loader = new FilesystemLoader($this->projectRoot . '/templates');
            $this->twig = new Environment($loader);
            $this->templateEngine = $this->twig;
        } else {
            $this->twig = $templateEngine;
            $this->templateEngine = $templateEngine;
        }
    }

    public function connexionPage(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

                    header('Location: /', true, 302);
                    exit;
                } catch (RuntimeException $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        echo $this->twig->render('Connexion/connexion.twig', [
            'form' => $form,
            'errors' => $errors,
            'success_message' => $successMessage,
        ]);
    }

    public function inscriptionPage(): void
    {
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
            $form['mode'] = $this->postString('mode', 'particulier');

            if ($form['mode'] !== 'particulier' && $form['mode'] !== 'entreprise') {
                $form['mode'] = 'particulier';
            }

            if ($form['mode'] === 'entreprise') {
                $entrepriseForm['societe'] = $this->postString('societe');
                $entrepriseForm['adresse'] = $this->postString('adresse');
                $entrepriseForm['email'] = $this->postString('email-pro');
                $entrepriseForm['telephone'] = $this->postString('telephone-pro');
                $entrepriseForm['nombre_employes'] = $this->postString('nombre_employes');
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
                $form['statut'] = $this->postString('statut', 'etudiant');
                $form['nom'] = $this->postString('nom');
                $form['prenom'] = $this->postString('prenom');
                $form['classe'] = $this->postString('classe');
                $form['pilot_id'] = $this->postString('pilot_id');
                $form['email'] = $this->postString('email');
                $password = (string) ($_POST['password'] ?? '');
                $passwordConfirmation = (string) ($_POST['password_confirm'] ?? '');
                $form['accept_terms'] = $this->postBool('accept_terms');

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
                            $successMessage = 'Inscription etudiant enregistree.';
                        } else {
                            $successMessage = 'Inscription pilote enregistree.';
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

        echo $this->twig->render('Connexion/inscription.twig', [
            'form' => $form,
            'entreprise_form' => $entrepriseForm,
            'errors' => $errors,
            'success_message' => $successMessage,
            'pilots' => $pilots,
        ]);
    }

    private function postString(string $name, string $default = ''): string
    {
        return trim((string) ($_POST[$name] ?? $default));
    }

    private function postBool(string $name): bool
    {
        return isset($_POST[$name]);
    }
}