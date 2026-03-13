<?php

namespace App\Models;

use PDO;
use PDOException;

class CandidatureModel
{
    private PDO $pdo;

    public function __construct()
    {
        try {
            $dsn = $_ENV['DB_DSN'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASS'];

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function createCandidature(int $studentId, int $offerId, string $cvPath): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO CANDIDATURE (id_etudiant, id_offre, cv) VALUES (:id_etudiant, :id_offre, :cv)'
        );

        $stmt->execute([
            'id_etudiant' => $studentId,
            'id_offre' => $offerId,
            'cv' => $cvPath
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getCandidatureById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM CANDIDATURE WHERE id_candidature = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getCandidatureByStudentAndOffer(int $studentId, int $offerId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM CANDIDATURE WHERE id_etudiant = :id_etudiant AND id_offre = :id_offre'
        );
        $stmt->execute(['id_etudiant' => $studentId, 'id_offre' => $offerId]);
        return $stmt->fetch() ?: null;
    }

    public function getCandidaturesByOffer(int $offerId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM CANDIDATURE WHERE id_offre = :id_offre');
        $stmt->execute(['id_offre' => $offerId]);
        return $stmt->fetchAll();
    }
}
