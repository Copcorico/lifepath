<?php
declare(strict_types=1);

use App\Controllers\AuthentificationController;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';

$authController = new AuthentificationController();
$authController->inscriptionPage();
