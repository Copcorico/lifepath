<?php

namespace App\Models;

use PDO;
use PDOException;

class OfferModel
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

    public function getOfferById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM OFFRES WHERE id_offre = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getOffersByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM OFFRES WHERE id_entreprise = :id_entreprise');
        $stmt->execute(['id_entreprise' => $companyId]);
        return $stmt->fetchAll();
    }

    public function createOffer(int $companyId, string $title, string $detail, string $location, string $level): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO OFFRES (id_entreprise, titre, détail, localisation, niveau)
             VALUES (:id_entreprise, :titre, :détail, :localisation, :niveau)'
        );

        $stmt->execute([
            'id_entreprise' => $companyId,
            'titre' => $title,
            'détail' => $detail,
            'localisation' => $location,
            'niveau' => $level
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateOffer(int $offerId, string $title, string $detail, string $location, string $level): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE OFFRES SET titre = :titre, détail = :détail, localisation = :localisation, niveau = :niveau 
             WHERE id_offre = :id_offre'
        );

        return $stmt->execute([
            'id_offre' => $offerId,
            'titre' => $title,
            'détail' => $detail,
            'localisation' => $location,
            'niveau' => $level
        ]);
    }

    public function deleteOffer(int $offerId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM OFFRES WHERE id_offre = :id_offre');
        return $stmt->execute(['id_offre' => $offerId]);
    }
}