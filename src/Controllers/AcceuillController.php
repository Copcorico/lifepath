<?php
declare(strict_types=1);

use App\Model\RegistrationModel;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/src/Models/RegistrationModel.php';

Dotenv::createImmutable($projectRoot)->safeLoad();

$loader = new FilesystemLoader($projectRoot . '/templates');
$twig = new Environment($loader);

$offres_stage = [
[
'titre' => 'Développement d’une application web interne',
'poste' => 'Développeur Web',
'entreprise' => 'TechSolutions',
'lieu' => 'Paris',
'niveau' => 'Bac +5'
],

[
'titre' => 'Création d’une plateforme e-commerce',
'poste' => 'Développeur Web',
'entreprise' => 'DigitalMarket',
'lieu' => 'Lyon',
'niveau' => 'Bac +4'
],

[
'titre' => 'Développement d’un site de gestion documentaire',
'poste' => 'Développeur Web',
'entreprise' => 'InfoSys',
'lieu' => 'Marseille',
'niveau' => 'Bac +5'
],

[
'titre' => 'Conception d’une API pour application mobile',
'poste' => 'Développeur Backend',
'entreprise' => 'CodeFactory',
'lieu' => 'Toulouse',
'niveau' => 'Bac +5'
],

[
'titre' => 'Amélioration de l’interface utilisateur d’une plateforme SaaS',
'poste' => 'Développeur Frontend',
'entreprise' => 'WebInnov',
'lieu' => 'Bordeaux',
'niveau' => 'Bac +3'
],

[
'titre' => 'Développement d’un tableau de bord analytique',
'poste' => 'Développeur Web',
'entreprise' => 'DataVision',
'lieu' => 'Lille',
'niveau' => 'Bac +5'
],

[
'titre' => 'Création d’une application de gestion des stocks',
'poste' => 'Développeur Full Stack',
'entreprise' => 'SoftLogistics',
'lieu' => 'Nantes',
'niveau' => 'Bac +4'
],

[
'titre' => 'Optimisation d’un site web existant',
'poste' => 'Développeur Web',
'entreprise' => 'NextTech',
'lieu' => 'Strasbourg',
'niveau' => 'Bac +3'
],

[
'titre' => 'Développement d’un portail client',
'poste' => 'Développeur Full Stack',
'entreprise' => 'CloudNet',
'lieu' => 'Montpellier',
'niveau' => 'Bac +5'
],

[
'titre' => 'Conception d’un système de gestion des utilisateurs',
'poste' => 'Développeur Web',
'entreprise' => 'InnovApps',
'lieu' => 'Nice',
'niveau' => 'Bac +4'
]

];


?>