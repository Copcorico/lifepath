<?php 
namespace App\Controllers;

use App\Models\routeurModel;
use App\Models\Pilot;

class routeurController extends Controller {
    
    private $db;

    public function __construct($templateEngine = null, $db = null) {
        $this->model = new routeurModel();
        $this->db = $db;
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
                'titre' => 'Développement d\'une application web interne',
                'poste' => 'Développeur Web',
                'entreprise' => 'TechSolutions',
                'lieu' => 'Paris',
                'niveau' => 'Bac +5'
            ],
            [
                'titre' => 'Création d\'une plateforme e-commerce',
                'poste' => 'Développeur Full Stack',
                'entreprise' => 'DigitalMarket',
                'lieu' => 'Lyon',
                'niveau' => 'Bac +4'
            ],
            [
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
        // Gérer le POST du formulaire d'inscription
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->db) {
            $authController = new AuthController($this->templateEngine, $this->db);
            $authController->inscription();
            return;
        }
        
        // Charger les pilots si on a une connexion BDD
        $pilots = [];
        if ($this->db) {
            $pilotModel = new Pilot($this->db);
            $pilots = $pilotModel->getPilots();
        }
        
        echo $this->templateEngine->render('Connexion/inscription.twig', [
            'errors' => [],
            'success_message' => null,
            'form' => [],
            'entreprise_form' => [],
            'pilots' => $pilots,
        ]);
    }

    public function connexionPage() {
        // Gérer le POST du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->db) {
            $authController = new AuthController($this->templateEngine, $this->db);
            $authController->connexion();
            return;
        }
        
        echo $this->templateEngine->render('Connexion/connexion.twig', [
            'errors' => [],
            'success_message' => null,
            'form' => [],
        ]);
    }

    public function entreprisePage() {
        echo $this->templateEngine->render('entreprise.twig');
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
