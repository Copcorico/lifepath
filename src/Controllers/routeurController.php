<?php 
namespace App\Controllers;

use App\Models\routeurModel;

class routeurController extends Controller {

    public function __construct($templateEngine = null) {
        $this->model = new routeurModel();
        if ($templateEngine === null) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__, 2) . '/templates');
            $this->templateEngine = new \Twig\Environment($loader);
        } else {
            $this->templateEngine = $templateEngine;
        }
    }

    public function welcomePage() {
        echo $this->templateEngine->render('accueil.twig');
    }

    public function inscriptionPage() {
        echo $this->templateEngine->render('Connexion/inscription.twig');
    }

    public function connexionPage() {
        echo $this->templateEngine->render('Connexion/connexion.twig');
    }

    public function entreprisePage() {
        echo $this->templateEngine->render('entreprise.twig');
    }

    public function offresPage() {
        echo $this->templateEngine->render('offres.twig');
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
        echo $this->templateEngine->render('profil.twig');
    }

    public function deconnexion() {
        session_destroy();
        header('Location: /');
        exit();
    }

}
