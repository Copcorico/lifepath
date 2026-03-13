<?php
declare(strict_types=1);

use App\Models\CompanyModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/src/Models/CompagnyModel.php';

Dotenv::createImmutable($projectRoot)->safeLoad();

$loader = new FilesystemLoader($projectRoot . '/templates');
$twig = new Environment($loader);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$companyModel = new CompanyModel();
$companyId = (int)($_GET['id'] ?? 0);

$company = $companyModel->getCompagnyById($companyId);
if (!$company) {
    die('Entreprise non trouvée.');
}

$offres = $companyModel->getOffresByCompanyId($companyId);

$offresPerPage = 1;
$totalOffres = count($offres);
$totalPages = max(1, (int)ceil($totalOffres / $offresPerPage));
$page = max(1, (int)($_GET['page'] ?? 1));
$page = min($page, $totalPages);

$offset = ($page - 1) * $offresPerPage;
$offresPage = array_slice($offres, $offset, $offresPerPage);
$currentOffre = $offresPage[0] ?? null;

$prevPage = $page > 1 ? $page - 1 : null;
$nextPage = $page < $totalPages ? $page + 1 : null;

echo $twig->render('entreprise.twig', [
    'company' => $company,
    'offres' => $offres,
    'currentOffre' => $currentOffre,
    'page' => $page,
    'totalPages' => $totalPages,
    'prevPage' => $prevPage,
    'nextPage' => $nextPage,
]);





?>