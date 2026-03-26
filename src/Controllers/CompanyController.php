<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\CompanyModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CompanyController extends Controller
{
    private CompanyModel $companyModel;
    private Environment $twig;
    private string $projectRoot;

    public function __construct(?Environment $templateEngine = null)
    {
        $this->projectRoot = dirname(__DIR__, 2);

        require_once $this->projectRoot . '/vendor/autoload.php';
        require_once $this->projectRoot . '/src/Models/CompanyModel.php';

        Dotenv::createImmutable($this->projectRoot)->safeLoad();

        if ($templateEngine === null) {
            $loader = new FilesystemLoader($this->projectRoot . '/templates');
            $this->twig = new Environment($loader);
            $this->templateEngine = $this->twig;
        } else {
            $this->twig = $templateEngine;
            $this->templateEngine = $templateEngine;
        }

        $this->companyModel = new CompanyModel();
    }

    public function displayCompanyPage(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $companyId = (int) ($_GET['id'] ?? 0);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $query = trim((string) ($_GET['q'] ?? ''));
        $noteMin = isset($_GET['note_min']) && $_GET['note_min'] !== '' ? (float) $_GET['note_min'] : null;
        $noteMax = isset($_GET['note_max']) && $_GET['note_max'] !== '' ? (float) $_GET['note_max'] : null;

        if ($noteMin !== null) {
            $noteMin = max(0.0, min(5.0, $noteMin));
        }
        if ($noteMax !== null) {
            $noteMax = max(0.0, min(5.0, $noteMax));
        }
        if ($noteMin !== null && $noteMax !== null && $noteMin > $noteMax) {
            [$noteMin, $noteMax] = [$noteMax, $noteMin];
        }

        // If company id is provided, show company profile
        if ($companyId > 0) {
            $company = $this->companyModel->getCompanyById($companyId);

            if (!$company) {
                http_response_code(404);
                echo $this->twig->render('entreprise_search.twig', [
                    'query' => $query,
                    'companies' => [],
                ]);
                return;
            }

            $offres = $this->companyModel->getOffresByCompanyId($companyId);
            $offresPerPage = 1;
            $totalOffres = count($offres);
            $totalPages = max(1, (int) ceil($totalOffres / $offresPerPage));
            $page = min($page, $totalPages);

            $offset = ($page - 1) * $offresPerPage;
            $offresPage = array_slice($offres, $offset, $offresPerPage);
            $currentOffre = $offresPage[0] ?? null;

            $prevPage = $page > 1 ? $page - 1 : null;
            $nextPage = $page < $totalPages ? $page + 1 : null;

            echo $this->twig->render('entreprise.twig', [
                'company' => $company,
                'offres' => $offres,
                'currentOffre' => $currentOffre,
                'page' => $page,
                'totalPages' => $totalPages,
                'prevPage' => $prevPage,
                'nextPage' => $nextPage,
            ]);
            return;
        }

        // Otherwise show search page
        if ($query === '') {
            if ($noteMin !== null || $noteMax !== null) {
                $companies = $this->companyModel->getAllCompaniesByRating($noteMin, $noteMax);
            } else {
                $companies = $this->companyModel->getAllCompanies();
            }
        } else {
            if ($noteMin !== null || $noteMax !== null) {
                $companies = $this->companyModel->searchCompaniesByNameAndRating($query, $noteMin, $noteMax);
            } else {
                $companies = $this->companyModel->searchCompaniesByName($query);
            }
        }

        echo $this->twig->render('entreprise_search.twig', [
            'query' => $query,
            'companies' => $companies,
            'note_min' => $noteMin,
            'note_max' => $noteMax,
        ]);
    }
}