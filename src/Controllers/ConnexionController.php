<?php
declare(strict_types=1);

use App\Controllers\AuthController;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';

$authController = new AuthController();
$authController->connexion();
