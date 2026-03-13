<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\CandidatureModel;

class CandidatureController
{
    private CandidatureModel $candidatureModel;
    private string $uploadDir = '/uploads/cv/';

    public function __construct()
    {
        $this->candidatureModel = new CandidatureModel();
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir, 0755, true);
        }
    }

    public function postulate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $studentId = intval($_POST['id_etudiant'] ?? 0);
            $offerId = intval($_POST['id_offre'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');

            if (!$studentId || !$offerId || !$nom || !$prenom || !$email || !$telephone) {
                http_response_code(400);
                echo json_encode(['error' => 'Tous les champs sont requis']);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Email invalide']);
                return;
            }

            if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'Erreur lors de l\'upload du CV']);
                return;
            }

            $file = $_FILES['cv'];
            if ($file['type'] !== 'application/pdf') {
                http_response_code(400);
                echo json_encode(['error' => 'Le CV doit être au format PDF']);
                return;
            }

            $maxFileSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxFileSize) {
                http_response_code(400);
                echo json_encode(['error' => 'Le fichier PDF ne doit pas dépasser 5 MB']);
                return;
            }

            $candidatureExist = $this->candidatureModel->getCandidatureByStudentAndOffer($studentId, $offerId);
            if ($candidatureExist) {
                http_response_code(400);
                echo json_encode(['error' => 'Vous avez déjà postulé à cette offre']);
                return;
            }

            $newFileName = 'CV_' . strtoupper($nom) . '_' . ucfirst($prenom) . '.pdf';
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadDir . $newFileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la sauvegarde du CV']);
                return;
            }

            $candidatureId = $this->candidatureModel->createCandidature(
                $studentId,
                $offerId,
                $this->uploadDir . $newFileName
            );

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Candidature envoyée avec succès',
                'candidature_id' => $candidatureId
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur lors de la postulation',
                'details' => $e->getMessage()
            ]);
        }
    }
}
