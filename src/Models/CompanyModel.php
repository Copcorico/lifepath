<?php

namespace App\Models;

use PDO;
use PDOException;

class CompanyModel
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

    public function getCompanyById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM ENTREPRISES WHERE id_entreprise = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getOffresByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM OFFRES WHERE id_entreprise = :companyId');
        $stmt->execute(['companyId' => $companyId]);
        return $stmt->fetchAll();
    }

    public function deleteCompanyById(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM ENTREPRISES WHERE id_entreprise = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function create_company(string $name, string $address, string $nb_employees, string $picture, string $phone, string $password): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO ENTREPRISES (id_entreprise,nom,note,nombre_employes,id_profil) VALUES (:name, :address, :note, :nb_employees, :id_profil)');
        $stmt->execute(['name' => $name, 'address' => $address, 'note' => $note, 'nb_employees' => $nb_employees, 'id_profil' => $id_profil]);
        return (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare('INSERT INTO PROFIL (id_profil,photo,adresse,nombre_employes) VALUES (:name, :address, :note, :nb_employees)');
        $stmt->execute(['name' => $name, 'address' => $address, 'note' => $note, 'nb_employees' => $nb_employees]);
        return (int) $this->pdo->lastInsertId();
    }
}
?>
