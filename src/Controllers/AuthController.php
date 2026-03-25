<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Profil;
use App\Models\Entreprise;
use App\Models\Particulier;
use App\Models\Etudiant;
use App\Models\Pilot;

class AuthController extends Controller 
{
    private $twig;

    private $profilModel;
    private $entrepriseModel;
    private $particulierModel;
    private $etudiantModel;
    private $pilotModel;

    public function __construct($twig, $db)
    {
        $this->twig = $twig;

        $this->profilModel = new Profil($db);
        $this->entrepriseModel = new Entreprise($db);
        $this->particulierModel = new Particulier($db);
        $this->etudiantModel = new Etudiant($db);
        $this->pilotModel = new Pilot($db);
    }

    /* ============ PAGE CONNEXION ============ */
    public function connexion()
    {
        $errors = [];
        $form = [];

        if(isset($_POST["connect"]))
        {
            $email = $_POST["email"];
            $password = $_POST["password"];

            $form["email"] = $email;

            $user = $this->profilModel->getProfilFromEmail($email);

            if(!$user)
            {
                $errors[] = "Email incorrect";
            }
            else if(!password_verify($password, $user["mot_de_passe"]))
            {
                $errors[] = "Mot de passe incorrect";
            }
            else
            {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION["user_id"] = $user["id_profil"];
                $_SESSION["email"] = $user["adresse_mail"];
                $_SESSION["type"] = $user["type"];

                // Charger les infos complètes de l'utilisateur
                if($user["type"] === "particulier") {
                    $particulier = $this->particulierModel->getByProfilId($user["id_profil"]);
                    if($particulier) {
                        $_SESSION["nom"] = $particulier["nom"];
                        $_SESSION["prenom"] = $particulier["prenom"];
                    }
                } else if($user["type"] === "entreprise") {
                    $entreprise = $this->entrepriseModel->getByProfilId($user["id_profil"]);
                    if($entreprise) {
                        $_SESSION["societe"] = $entreprise["nom"];
                    }
                }

                // Fermer la session pour la sauvegarder proprement
                session_write_close();

                // Rediriger vers l'accueil
                header("Location: /");
                exit;
            }
        }

        echo $this->twig->render("Connexion/connexion.twig",["errors"=>$errors, "form"=>$form]);
    }

    /* ============ PAGE INSCRIPTION ============ */

    public function inscription()
    {
        $errors = [];
        $form = [];
        $entreprise_form = [];

        $pilots = $this->pilotModel->getPilots();

        /* ----------- PARTICULIER ----------- */
        if(isset($_POST["submit"]))
        {
            $statut = $_POST["statut"];

            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $classe = $_POST["classe"];
            $pilot_id = $_POST["pilot_id"] ?? "";

            $email = $_POST["email"];
            $password = $_POST["password"];
            $confirm = $_POST["password_confirm"];

            $form = $_POST;

            if($password != $confirm)
            {
                $errors[] = "Les mots de passe ne correspondent pas";
            }

            if($this->profilModel->getProfilFromEmail($email))
            {
                $errors[] = "Cette adresse email est deja utilisee";
            }

            if($statut == "etudiant")
            {
                if(empty($pilot_id) || !ctype_digit((string) $pilot_id) || !$this->pilotModel->exists((int) $pilot_id))
                {
                    $errors[] = "Le pilote selectionne est invalide";
                }
            }

            if(empty($errors))
            {
                try {
                    $profil_id = $this->profilModel->create("", $email, $password, $statut);
                    
                    $particulier_id = $this->particulierModel->create($profil_id, $nom, $prenom);

                    if($statut == "pilote")
                    {
                        $this->pilotModel->create($particulier_id);
                    }

                    if($statut == "etudiant")
                    {
                        $this->etudiantModel->create($particulier_id, $classe, (int) $pilot_id);
                    }

                    header("Location: connexion");
                    exit;
                } catch (\PDOException $e) {
                    if((string) $e->getCode() === "23000") {
                        $errors[] = "Cette adresse email est deja utilisee";
                    } else {
                        $errors[] = "Une erreur est survenue pendant l'inscription";
                    }
                }
            }
        }

        /* ----------- ENTREPRISE ----------- */

        if(isset($_POST["submit-entreprise"]))
        {
            $societe = $_POST["societe"];
            $adresse = $_POST["adresse"];
            $email = $_POST["email-pro"];
            $telephone = $_POST["telephone-pro"];
            $password = $_POST["password-pro"];
            $confirm = $_POST["password-pro-confirm"];

            $entreprise_form = $_POST;

            if($password != $confirm)
            {
                $errors[] = "Les mots de passe ne correspondent pas";
            }

            if($this->profilModel->getProfilFromEmail($email))
            {
                $errors[] = "Cette adresse email est deja utilisee";
            }

            if(empty($errors))
            {
                try {
                    $profil_id = $this->profilModel->create($telephone, $email, $password, 'entreprise');

                    $this->entrepriseModel->create($profil_id, $societe, $adresse);

                    header("Location: connexion");
                    exit;
                } catch (\PDOException $e) {
                    if((string) $e->getCode() === "23000") {
                        $errors[] = "Cette adresse email est deja utilisee";
                    } else {
                        $errors[] = "Une erreur est survenue pendant l'inscription entreprise";
                    }
                }
            }
        }

        echo $this->twig->render("Connexion/inscription.twig", ["errors"=>$errors, "form"=>$form, "entreprise_form"=>$entreprise_form, "pilots"=>$pilots]);
    }
}