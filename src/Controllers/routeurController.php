<?php 
namespace App\Controllers;

use App\Models\routeurModel;

class routeurController extends Controller {

    public function __construct($templateEngine = null) {
        $this->model = new routeurModel();
        if ($templateEngine === null) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__, 2) . '/templates');
            $this->templateEngine = new \Twig\Environment($loader);
        } else {
            $this->templateEngine = $templateEngine;
        }
    }

    public function welcomePage() {
        $offresEmploi = [
            [
                'id_entreprise' => 1,
                'titre' => 'Développement d\'une application web interne',
                'poste' => 'Développeur Web',
                'entreprise' => 'TechSolutions',
                'lieu' => 'Paris',
                'niveau' => 'Bac +5'
            ],
            [
                'id_entreprise' => 2,
                'titre' => 'Création d\'une plateforme e-commerce',
                'poste' => 'Développeur Full Stack',
                'entreprise' => 'DigitalMarket',
                'lieu' => 'Lyon',
                'niveau' => 'Bac +4'
            ],
            [
                'id_entreprise' => 3,
                'titre' => 'Conception d\'une API pour application mobile',
                'poste' => 'Développeur Backend',
                'entreprise' => 'CodeFactory',
                'lieu' => 'Toulouse',
                'niveau' => 'Bac +5'
            ]
        ];

        echo $this->templateEngine->render('accueil.twig', [
            'offres_emploi' => $offresEmploi
        ]);
    }

    public function inscriptionPage() {
        $authController = new AuthentificationController($this->templateEngine);
        $authController->inscriptionPage();
    }

    public function connexionPage() {
        $authController = new AuthentificationController($this->templateEngine);
        $authController->connexionPage();
    }

    public function entreprisePage() {
        $companyController = new CompanyController($this->templateEngine);
        $companyController->displayCompanyPage();
    }

    public function offresPage() {
        echo $this->templateEngine->render('offres.twig');
    }

    public function contactPage() {
        echo $this->templateEngine->render('formulaire/contact.twig');
    }

    public function avisPage() {
        echo $this->templateEngine->render('formulaire/avis.twig');
    }

    public function legalePage() {
        echo $this->templateEngine->render('legale.twig');
    }

    public function profilPage() {
        echo $this->templateEngine->render('profil.twig');
    }

    public function deconnexion() {
        session_destroy();
        header('Location: /');
        exit();
    }

}
