<?php 
namespace App\Controllers;

use App\Models\routeurModel;
use App\Models\PilotModel;
use App\Models\OfferModel;
use App\Models\Particulier;
use App\Models\Etudiant;
use App\Models\Profil;

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
        // Gérer le POST du formulaire d'inscription
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->db) {
            $authController = new AuthController($this->templateEngine, $this->db);
            $authController->inscription();
            return;
        }
        
        // Charger les pilots si on a une connexion BDD
        $pilots = [];
        if ($this->db) {
            $pilotModel = new PilotModel($this->db);
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
        $companyController = new CompanyController($this->templateEngine);
        $companyController->displayCompanyPage();
    }

    public function offresPage() {
        $offerModel = new OfferModel();
        $offerId = (int) ($_GET['id'] ?? 0);
        $query = trim((string) ($_GET['q'] ?? ''));

        if ($offerId > 0) {
            $offer = $offerModel->getOfferWithCompanyById($offerId);

            if (!$offer) {
                http_response_code(404);
                echo $this->templateEngine->render('offres_search.twig', [
                    'query' => $query,
                    'offers' => [],
                ]);
                return;
            }

            $relatedOffers = [];
            if (!empty($offer['id_entreprise'])) {
                $relatedOffers = array_values(array_filter(
                    $offerModel->getOffersByCompanyId((int) $offer['id_entreprise']),
                    static fn(array $item): bool => (int) ($item['id_offre'] ?? 0) !== $offerId
                ));
            }

            echo $this->templateEngine->render('offres.twig', [
                'offer' => $offer,
                'relatedOffers' => $relatedOffers,
            ]);
            return;
        }

        if ($query === '') {
            $offers = $offerModel->getAllOffers();
        } else {
            $offers = $offerModel->searchOffersByTitle($query);
        }

        echo $this->templateEngine->render('offres_search.twig', [
            'query' => $query,
            'offers' => $offers,
        ]);
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
        if ($this->db && isset($_SESSION['user_id'])) {
            $profilModel = new Profil($this->db);
            $photo = $profilModel->getPhoto((int) $_SESSION['user_id']);

            if (!empty($photo)) {
                $_SESSION['photo'] = $photo;
            }
        }

        echo $this->templateEngine->render('profil.twig');
    }

    public function mesEtudiantsPage() {
        $etudiants = [];

        if (!isset($_SESSION['user_id'])) {
            header('Location: /connexion');
            exit;
        }

        if (($_SESSION['type'] ?? null) !== 'pilote') {
            echo $this->templateEngine->render('mes_etudiants.twig', [
                'etudiants' => $etudiants,
            ]);
            return;
        }

        if ($this->db) {
            $particulierModel = new Particulier($this->db);
            $pilotModel = new PilotModel($this->db);
            $etudiantModel = new Etudiant($this->db);

            $particulier = $particulierModel->getByProfilId((int) $_SESSION['user_id']);

            if ($particulier && isset($particulier['id_particulier'])) {
                $pilotId = $pilotModel->getPilotIdByParticulierId((int) $particulier['id_particulier']);

                if ($pilotId !== null) {
                    $etudiants = $etudiantModel->getByPilotId($pilotId);
                }
            }
        }

        echo $this->templateEngine->render('mes_etudiants.twig', [
            'etudiants' => $etudiants,
        ]);
    }

    public function deconnexion() {
        session_destroy();
        header('Location: /');
        exit();
    }

}
