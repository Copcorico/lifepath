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

    public function getCompanyDescription(int $id): ?string
    {
        $stmt = $this->pdo->prepare('SELECT description FROM ENTREPRISES WHERE id_entreprise = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ? $result['description'] : null;
    }

    public function getOffresByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM OFFRES WHERE id_entreprise = :companyId');
        $stmt->execute(['companyId' => $companyId]);
        return $stmt->fetchAll();
    }

    public function searchCompaniesByName(string $name): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM ENTREPRISES
             WHERE nom LIKE :name
             ORDER BY nom ASC'
        );
        $stmt->execute(['name' => '%' . $name . '%']);
        return $stmt->fetchAll();
    }

    public function searchCompaniesByNameAndRating(string $name, ?float $noteMin, ?float $noteMax): array
    {
        $conditions = ['nom LIKE :name'];
        $params = ['name' => '%' . $name . '%'];

        if ($noteMin !== null) {
            $conditions[] = 'note >= :noteMin';
            $params['noteMin'] = $noteMin;
        }

        if ($noteMax !== null) {
            $conditions[] = 'note <= :noteMax';
            $params['noteMax'] = $noteMax;
        }

        $sql = 'SELECT *
                FROM ENTREPRISES
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY nom ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAllCompanies(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM ENTREPRISES
             ORDER BY nom ASC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllCompaniesByRating(?float $noteMin, ?float $noteMax, int $limit = 50): array
    {
        $conditions = [];
        $params = [];

        if ($noteMin !== null) {
            $conditions[] = 'note >= :noteMin';
            $params['noteMin'] = $noteMin;
        }

        if ($noteMax !== null) {
            $conditions[] = 'note <= :noteMax';
            $params['noteMax'] = $noteMax;
        }

        $sql = 'SELECT *
                FROM ENTREPRISES';

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY nom ASC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
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
