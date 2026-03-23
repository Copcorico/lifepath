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

    public function getOfferWithCompanyById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, e.nom AS entreprise_nom, e.note AS entreprise_note
             FROM OFFRES o
             LEFT JOIN ENTREPRISES e ON e.id_entreprise = o.id_entreprise
             WHERE o.id_offre = :id'
        );

        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function searchOffersByTitle(string $query): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, e.nom AS entreprise_nom, e.note AS entreprise_note
             FROM OFFRES o
             LEFT JOIN ENTREPRISES e ON e.id_entreprise = o.id_entreprise
             WHERE o.titre LIKE :query
             ORDER BY o.titre ASC'
        );

        $stmt->execute(['query' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }

    public function getAllOffers(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, e.nom AS entreprise_nom, e.note AS entreprise_note
             FROM OFFRES o
             LEFT JOIN ENTREPRISES e ON e.id_entreprise = o.id_entreprise
             ORDER BY o.id_offre DESC
             LIMIT :limit'
        );

        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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

    public function addToFavoris(int $offerId, int $favoriId): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO FAVORIS (id_offre, id_favori) VALUES (:id_offre, :id_favori)'
        );

        return $stmt->execute([
            'id_offre' => $offerId,
            'id_favori' => $favoriId
        ]);
    }

    public function removeFromFavoris(int $offerId, int $favoriId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM FAVORIS WHERE id_offre = :id_offre AND id_favori = :id_favori'
        );

        return $stmt->execute([
            'id_offre' => $offerId,
            'id_favori' => $favoriId
        ]);
    }

    public function isFavori(int $offerId, int $favoriId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_favori FROM FAVORIS WHERE id_offre = :id_offre AND id_favori = :id_favori'
        );

        $stmt->execute([
            'id_offre' => $offerId,
            'id_favori' => $favoriId
        ]);

        return $stmt->fetch() !== false;
    }

    public function getFavorisByUser(int $favoriId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.* FROM OFFRES o 
             INNER JOIN FAVORIS f ON o.id_offre = f.id_offre 
             WHERE f.id_favori = :id_favori'
        );

        $stmt->execute(['id_favori' => $favoriId]);
        return $stmt->fetchAll();
    }

    public function getFavorisCount(int $offerId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) as count FROM FAVORIS WHERE id_offre = :id_offre'
        );

        $stmt->execute(['id_offre' => $offerId]);
        $result = $stmt->fetch();

        return $result['count'] ?? 0;
    }
}