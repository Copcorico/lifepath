<?php 
namespace App\Controllers;

use App\Models\routeurModel;
use App\Models\PilotModel;
use App\Models\OfferModel;
use App\Models\CompanyModel;
use App\Models\Particulier;
use App\Models\Etudiant;
use App\Models\Profil;
use App\Helpers\DateHelper;
use App\Helpers\RatingHelper;

/*
    routeurController est responsable de la gestion des différentes pages du site, 
    telles que l'accueil, l'inscription, la connexion, les entreprises, les offres, 
    le profil utilisateur, etc. 
    Il utilise Twig pour rendre les vues et interagit avec les modèles 
    pour récupérer les données nécessaires à l'affichage.
    Les méthodes principales incluent :
    - welcomePage() : Affiche la page d'accueil avec les offres, entreprises et profils récents.
    - inscriptionPage() : Affiche la page d'inscription et gère le formulaire d'inscription.
    - connexionPage() : Affiche la page de connexion et gère le formulaire de connexion.
    - deconnexion() : Gère la déconnexion de l'utilisateur en détruisant la
    
    session et redirigeant vers la page d'accueil.

*/
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

        // Ajouter le filtre personnalisé pour les étoiles
        $this->templateEngine->addFilter(new \Twig\TwigFilter('stars', function($rating) {
            return RatingHelper::convertRatingToStars($rating);
        }));
    }

    public function welcomePage() {
        $offers = [];
        $companies = [];
        $profiles = [];

        try {
            $offerModel = new OfferModel();
            $offers = $offerModel->getAllOffers(10);
        } catch (\Throwable $e) {
            $offers = [];
        }

        try {
            $companyModel = new CompanyModel();
            $companies = $companyModel->getAllCompanies(10);
        } catch (\Throwable $e) {
            $companies = [];
        }

        try {
            if ($this->db) {
                $profilModel = new Profil($this->db);
                $profiles = $profilModel->getAllProfiles(10);
            }
        } catch (\Throwable $e) {
            $profiles = [];
        }

        echo $this->templateEngine->render('accueil.twig', [
            'offers' => $offers,
            'companies' => $companies,
            'profiles' => $profiles,
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
        $localisation = trim((string) ($_GET['localisation'] ?? ''));
        $niveauRaw = trim((string) ($_GET['niveau'] ?? ''));
        $salaireMinRaw = trim((string) ($_GET['salaire_min'] ?? ''));
        $salaireMaxRaw = trim((string) ($_GET['salaire_max'] ?? ''));
        $dureeRaw = trim((string) ($_GET['duree'] ?? ''));

        $niveau = $niveauRaw === '' ? null : $niveauRaw;
        $salaireMin = $salaireMinRaw !== '' && is_numeric($salaireMinRaw) ? (int) $salaireMinRaw : null;
        $salaireMax = $salaireMaxRaw !== '' && is_numeric($salaireMaxRaw) ? (int) $salaireMaxRaw : null;
        $duree = $dureeRaw !== '' && is_numeric($dureeRaw) ? (int) $dureeRaw : null;

        if ($salaireMin !== null && $salaireMax !== null && $salaireMin > $salaireMax) {
            [$salaireMin, $salaireMax] = [$salaireMax, $salaireMin];
        }

        if ($offerId > 0) {
            $offer = $offerModel->getOfferWithCompanyById($offerId);

            if (!$offer) {
                http_response_code(404);
                echo $this->templateEngine->render('offres_search.twig', [
                    'query' => $query,
                    'offers' => [],
                    'localisation' => $localisation,
                    'niveau' => $niveau,
                    'salaire_min' => $salaireMin,
                    'salaire_max' => $salaireMax,
                    'duree' => $duree,
                ]);
                return;
            }

            // Formater les dates et calculer la durée
            $offer['date_debut_formatted'] = DateHelper::formatDateFR($offer['date_debut'] ?? '');
            $offer['date_fin_formatted'] = DateHelper::formatDateFR($offer['date_fin'] ?? '');
            $offer['duree'] = DateHelper::calculateDuration($offer['date_debut'] ?? '', $offer['date_fin'] ?? '');

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

        $hasFilters = $query !== ''
            || $localisation !== ''
            || $niveau !== null
            || $salaireMin !== null
            || $salaireMax !== null
            || $duree !== null;

        if (!$hasFilters) {
            $offers = $offerModel->getAllOffers();
        } else {
            $offers = $offerModel->searchOffers($query, [
                'localisation' => $localisation,
                'niveau' => $niveau,
                'salaire_min' => $salaireMin,
                'salaire_max' => $salaireMax,
                'duree' => $duree,
            ]);
        }

        echo $this->templateEngine->render('offres_search.twig', [
            'query' => $query,
            'offers' => $offers,
            'localisation' => $localisation,
            'niveau' => $niveau,
            'salaire_min' => $salaireMin,
            'salaire_max' => $salaireMax,
            'duree' => $duree,
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

    public function aProposPage() {
        echo $this->templateEngine->render('a_propos.twig');
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

