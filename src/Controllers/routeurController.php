<?php 
namespace App\Controllers;

use App\Models\routeurModel;

class routeController extends Controller {

    public function __construct($templateEngine) {
        $this->model = new routeModel();
        $this->templateEngine = $templateEngine;
    }

    public function welcomePage() {
        echo $this->templateEngine->render('index.html');
    }

    public function entreprisePage() {
        echo $this->templateEngine->render('entreprise.twig');
    }

    public function offresPage() {
        echo $this->templateEngine->render('offres.twig');
    }

    public function contactPage() {
        echo $this->templateEngine->render('contact.twig');
    }

    public function avisPage() {
        echo $this->templateEngine->render('avis.twig');
    }

}
