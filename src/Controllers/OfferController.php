<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\OfferModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';

Dotenv::createImmutable($projectRoot)->safeLoad();

class OfferController
{
    private OfferModel $offerModel;
    private Environment $twig;

    public function __construct()
    {
        $this->offerModel = new OfferModel();
        $loader = new FilesystemLoader($projectRoot . '/templates');
        $this->twig = new Environment($loader);
    }

    public function createOffer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $companyId = intval($_POST['id_entreprise'] ?? 0);
            $title = trim($_POST['titre'] ?? '');
            $detail = trim($_POST['détail'] ?? '');
            $location = trim($_POST['localisation'] ?? '');
            $level = trim($_POST['niveau'] ?? '');

            if (!$companyId || !$title || !$detail || !$location || !$level) {
                http_response_code(400);
                echo json_encode(['error' => 'Tous les champs sont requis']);
                return;
            }

            $offerId = $this->offerModel->createOffer($companyId, $title, $detail, $location, $level);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Offre créée avec succès',
                'offer_id' => $offerId
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de la création de l\'offre',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function getCompanyOffers(int $companyId): void
    {
        try {
            $offers = $this->offerModel->getOffersByCompanyId($companyId);
            echo json_encode([
                'success' => true,
                'offers' => $offers
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de la récupération des offres',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function getOffer(int $offerId): void
    {
        try {
            $offer = $this->offerModel->getOfferById($offerId);
            if (!$offer) {
                http_response_code(404);
                echo json_encode(['error' => 'Offre non trouvée']);
                return;
            }

            echo json_encode([
                'success' => true,
                'offer' => $offer
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de la récupération de l\'offre',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function addToFavoris(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $offerId = intval($_POST['id_offre'] ?? 0);
            $favoriId = intval($_POST['id_favori'] ?? 0);

            if (!$offerId || !$favoriId) {
                http_response_code(400);
                echo json_encode(['error' => 'Les paramètres id_offre et id_favori sont requis']);
                return;
            }

            $result = $this->offerModel->addToFavoris($offerId, $favoriId);

            if (!$result) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'ajout aux favoris']);
                return;
            }

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Offre ajoutée aux favoris'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de l\'ajout aux favoris',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function removeFromFavoris(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $offerId = intval($_POST['id_offre'] ?? 0);
            $favoriId = intval($_POST['id_favori'] ?? 0);

            if (!$offerId || !$favoriId) {
                http_response_code(400);
                echo json_encode(['error' => 'Les paramètres id_offre et id_favori sont requis']);
                return;
            }

            $result = $this->offerModel->removeFromFavoris($offerId, $favoriId);

            if (!$result) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la suppression des favoris']);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Offre supprimée des favoris'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de la suppression des favoris',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function getUserFavoris(int $favoriId): void
    {
        try {
            if (!$favoriId) {
                http_response_code(400);
                echo json_encode(['error' => 'Le paramètre id_favori est requis']);
                return;
            }

            $offers = $this->offerModel->getFavorisByUser($favoriId);

            echo json_encode([
                'success' => true,
                'offers' => $offers
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de la récupération des offres favorites',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function checkIsFavori(): void
    {
        try {
            $offerId = intval($_GET['id_offre'] ?? 0);
            $favoriId = intval($_GET['id_favori'] ?? 0);

            if (!$offerId || !$favoriId) {
                http_response_code(400);
                echo json_encode(['error' => 'Les paramètres id_offre et id_favori sont requis']);
                return;
            }

            $isFavori = $this->offerModel->isFavori($offerId, $favoriId);
            $count = $this->offerModel->getFavorisCount($offerId);

            echo json_encode([
                'success' => true,
                'is_favori' => $isFavori,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de la vérification du favori',
                'details' => $e->getMessage()
            ]);
        }
    }
}