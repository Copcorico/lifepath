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
}