<?php
declare(strict_types=1);

use App\Model\StudentRegistrationModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/src/Models/StudentRegistrationModel.php';

Dotenv::createImmutable($projectRoot)->safeLoad();

$loader = new FilesystemLoader($projectRoot . '/templates');
$twig = new Environment($loader);

if 

?>