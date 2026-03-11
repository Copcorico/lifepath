<?php
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class StudentRegistrationModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function fromEnv(array $env): self
    {
        if (!isset($env['DB_USER']) || $env['DB_USER'] === '') {
            throw new RuntimeException('Variable .env manquante ou vide : DB_USER');
        }

        if (!isset($env['DB_PASS'])) {
            throw new RuntimeException('Variable .env manquante : DB_PASS');
        }

        $dsn = isset($env['DB_DSN']) ? trim((string) $env['DB_DSN']) : '';
        if ($dsn === '') {
            $host = isset($env['DB_HOST']) ? trim((string) $env['DB_HOST']) : '';
            $dbName = isset($env['DB_NAME']) ? trim((string) $env['DB_NAME']) : '';

            if ($host === '' || $dbName === '') {
                throw new RuntimeException('Variables .env manquantes : DB_HOST/DB_NAME ou DB_DSN');
            }

            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $dbName);
        }

        try {
            $pdo = new PDO(
                $dsn,
                (string) $env['DB_USER'],
                (string) $env['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Connexion BDD impossible : ' . $exception->getMessage());
        }

        return new self($pdo);
    }
    
    public function getPilots(): array
    {
        $sql = 'SELECT pilots.id_pilot, particulier.nom, particulier.prenom
                FROM PILOTS pilots
                INNER JOIN PARTICULIER particulier ON particulier.id_particulier = pilots.id_particulier
                ORDER BY particulier.nom, particulier.prenom';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $pilots = [];
        foreach ($rows as $row) {
            $pilots[] = [
                'id_pilot' => (int) $row['id_pilot'],
                'label' => trim((string) $row['prenom'] . ' ' . (string) $row['nom']),
            ];
        }

        return $pilots;
    }

    /**
     * @return array{id_profil:int,id_particulier:int,id_pilot:int|null,id_etudiant:int|null}
     */
    public function registerParticulier(
        string $nom,
        string $prenom,
        string $email,
        string $plainPassword,
        string $statut,
        ?string $classe,
        ?int $pilotId
    ): array {
        if ($this->emailExists($email)) {
            throw new RuntimeException('Cette adresse email est deja utilisee.');
        }

        if (!in_array($statut, ['etudiant', 'pilote'], true)) {
            throw new RuntimeException('Statut invalide.');
        }

        $idPilotCreated = null;
        $idEtudiant = null;

        $this->pdo->beginTransaction();

        try {
            $profileStatement = $this->pdo->prepare(
                'INSERT INTO PROFIL (photo, adresse_mail, telephone, mot_de_passe)
                 VALUES (:photo, :adresse_mail, :telephone, :mot_de_passe)'
            );
            $profileStatement->execute([
                'photo' => null,
                'adresse_mail' => $email,
                'telephone' => null,
                'mot_de_passe' => password_hash($plainPassword, PASSWORD_DEFAULT),
            ]);
            $idProfil = (int) $this->pdo->lastInsertId();

            $particulierStatement = $this->pdo->prepare(
                'INSERT INTO PARTICULIER (nom, prenom, id_profil)
                 VALUES (:nom, :prenom, :id_profil)'
            );
            $particulierStatement->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'id_profil' => $idProfil,
            ]);
            $idParticulier = (int) $this->pdo->lastInsertId();

            if ($statut === 'pilote') {
                $pilotStatement = $this->pdo->prepare(
                    'INSERT INTO PILOTS (id_particulier) VALUES (:id_particulier)'
                );
                $pilotStatement->execute(['id_particulier' => $idParticulier]);
                $idPilotCreated = (int) $this->pdo->lastInsertId();
            }

            if ($statut === 'etudiant') {
                if ($pilotId === null || !$this->pilotExists($pilotId)) {
                    throw new RuntimeException('Le pilote selectionne est invalide.');
                }

                $classeValue = trim((string) $classe);
                if ($classeValue === '') {
                    throw new RuntimeException('La classe est obligatoire pour un etudiant.');
                }

                $studentStatement = $this->pdo->prepare(
                    'INSERT INTO ETUDIANTS (classe, id_pilot, id_particulier)
                     VALUES (:classe, :id_pilot, :id_particulier)'
                );
                $studentStatement->execute([
                    'classe' => $classeValue,
                    'id_pilot' => $pilotId,
                    'id_particulier' => $idParticulier,
                ]);

                $idEtudiant = (int) $this->pdo->lastInsertId();
            }

            $this->pdo->commit();

            return [
                'id_profil' => $idProfil,
                'id_particulier' => $idParticulier,
                'id_pilot' => $idPilotCreated,
                'id_etudiant' => $idEtudiant,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($exception instanceof RuntimeException) {
                throw $exception;
            }

            throw new RuntimeException('Erreur SQL pendant l\'inscription.');
        }
    }

    /**
     * @return array{id_profil:int,id_entreprise:int}
     */
    public function registerEntreprise(
        string $nomSociete,
        string $email,
        string $telephone,
        string $plainPassword,
        ?int $nombreEmployes
    ): array {
        if ($this->emailExists($email)) {
            throw new RuntimeException('Cette adresse email est deja utilisee.');
        }

        $nomSociete = trim($nomSociete);
        if ($nomSociete === '') {
            throw new RuntimeException('Le nom de la societe est obligatoire.');
        }

        if (trim($telephone) === '') {
            throw new RuntimeException('Le telephone est obligatoire pour une entreprise.');
        }

        $this->pdo->beginTransaction();

        try {
            $profileStatement = $this->pdo->prepare(
                'INSERT INTO PROFIL (photo, adresse_mail, telephone, mot_de_passe)
                 VALUES (:photo, :adresse_mail, :telephone, :mot_de_passe)'
            );
            $profileStatement->execute([
                'photo' => null,
                'adresse_mail' => $email,
                'telephone' => $telephone,
                'mot_de_passe' => password_hash($plainPassword, PASSWORD_DEFAULT),
            ]);
            $idProfil = (int) $this->pdo->lastInsertId();

            $companyStatement = $this->pdo->prepare(
                'INSERT INTO ENTREPRISES (nom, note, nombre_employes, id_profil)
                 VALUES (:nom, :note, :nombre_employes, :id_profil)'
            );
            $companyStatement->execute([
                'nom' => $nomSociete,
                'note' => null,
                'nombre_employes' => $nombreEmployes,
                'id_profil' => $idProfil,
            ]);
            $idEntreprise = (int) $this->pdo->lastInsertId();

            $this->pdo->commit();

            return [
                'id_profil' => $idProfil,
                'id_entreprise' => $idEntreprise,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($exception instanceof RuntimeException) {
                throw $exception;
            }

            throw new RuntimeException('Erreur SQL pendant l\'inscription entreprise.');
        }
    }

    private function emailExists(string $email): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM PROFIL WHERE adresse_mail = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        return (bool) $statement->fetchColumn();
    }

    private function pilotExists(int $pilotId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM PILOTS WHERE id_pilot = :id_pilot LIMIT 1'
        );
        $statement->execute(['id_pilot' => $pilotId]);

        return (bool) $statement->fetchColumn();
    }
}
