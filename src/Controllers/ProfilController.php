<?php

namespace App\Controllers;

use App\Models\Profil;

class ProfilController extends Controller
{
	private $db;

	public function __construct($templateEngine = null, $db = null)
	{
		$this->templateEngine = $templateEngine;
		$this->db = $db;
		$this->model = new Profil($db);
	}

	public function updateProfil()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (!isset($_SESSION['user_id'])) {
			header('Location: /connexion');
			exit;
		}

		$idProfil = (int) $_SESSION['user_id'];
		$type = (string) ($_SESSION['type'] ?? '');

		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

		if (!$email) {
			$_SESSION['profil_error'] = 'Email invalide';
			header('Location: /profil');
			exit;
		}

		if ($type === 'etudiant' || $type === 'pilote') {
			$nom = htmlspecialchars((string) ($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
			$prenom = htmlspecialchars((string) ($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');

			$this->model->updateParticulier($idProfil, $nom, $prenom);

			$_SESSION['nom'] = $nom;
			$_SESSION['prenom'] = $prenom;
		} else {
			$societe = htmlspecialchars((string) ($_POST['societe'] ?? ''), ENT_QUOTES, 'UTF-8');
			$adresse = htmlspecialchars((string) ($_POST['adresse'] ?? ''), ENT_QUOTES, 'UTF-8');
			$description = htmlspecialchars((string) ($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');

			$this->model->updateEntreprise($idProfil, $societe, $adresse, $description);

			$_SESSION['societe'] = $societe;
			$_SESSION['adresse'] = $adresse;
			$_SESSION['description'] = $description;
		}

		$this->model->updateEmail($idProfil, $email);

		$_SESSION['email'] = $email;
		unset($_SESSION['profil_error']);

		header('Location: /profil');
		exit;
	}

    public function uploadPhoto()
    {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

        if (!isset($_SESSION['user_id'])) {
			header('Location: /connexion');
			exit;
        }

		if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
			$uploadError = (int) ($_FILES['photo']['error'] ?? -1);
			$errorMessages = [
				UPLOAD_ERR_INI_SIZE => 'Le fichier depasse la limite serveur (2 Mo actuellement).',
				UPLOAD_ERR_FORM_SIZE => 'Le fichier depasse la limite autorisee par le formulaire.',
				UPLOAD_ERR_PARTIAL => 'Le fichier n\'a ete envoye que partiellement.',
				UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a ete envoye.',
				UPLOAD_ERR_NO_TMP_DIR => 'Le dossier temporaire est manquant.',
				UPLOAD_ERR_CANT_WRITE => 'Echec d\'ecriture sur le disque.',
				UPLOAD_ERR_EXTENSION => 'Upload bloque par une extension PHP.',
			];

			$_SESSION['profil_error'] = $errorMessages[$uploadError] ?? ('Erreur upload (code ' . $uploadError . ')');
			header('Location: /profil');
			exit;
        }

		$idProfil = (int) $_SESSION['user_id'];
        $file = $_FILES['photo'];
		$maxSize = 2 * 1024 * 1024;

		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		$mimeType = $finfo->file($file['tmp_name']);
		$allowedTypes = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/webp' => 'webp',
		];

		if (!isset($allowedTypes[$mimeType])) {
			$_SESSION['profil_error'] = 'Format non autorise';
			header('Location: /profil');
			exit;
        }

		if ($file['size'] > $maxSize) {
			$_SESSION['profil_error'] = 'Fichier trop volumineux (max 2 Mo)';
			header('Location: /profil');
			exit;
        }

        $anciennePhoto = $this->model->getPhoto($idProfil);
		$uploadDir = dirname(__DIR__, 2) . '/public/images/profils/';

		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		if (!is_writable($uploadDir)) {
			@chmod($uploadDir, 0775);
		}

		if (!is_writable($uploadDir)) {
			$_SESSION['profil_error'] = 'Dossier images/profils non inscriptible';
			header('Location: /profil');
			exit;
		}

		if ($anciennePhoto && !in_array($anciennePhoto, ['profile.png'], true)) {
			$path = $uploadDir . $anciennePhoto;
            if (file_exists($path)) {
                unlink($path);
            }
        }

		$filename = 'profil_' . bin2hex(random_bytes(8)) . '.' . $allowedTypes[$mimeType];
		$destination = $uploadDir . $filename;

		if (!is_uploaded_file($file['tmp_name'])) {
			$_SESSION['profil_error'] = 'Fichier upload invalide';
			header('Location: /profil');
			exit;
		}

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
			$_SESSION['profil_error'] = 'Erreur sauvegarde (verifie permissions dossier images/profils)';
			header('Location: /profil');
			exit;
        }

        $this->model->updatePhoto($idProfil, $filename);

        $_SESSION['photo'] = $filename;
		unset($_SESSION['profil_error']);

		header('Location: /profil');
		exit;
    }
}
