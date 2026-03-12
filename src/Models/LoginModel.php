<?php
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;
use RuntimeException;

class LoginModel
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

    /**
     * @return array{id_profil:int,email:string}
     */
    public function authenticate(string $email, string $plainPassword): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id_profil, adresse_mail, mot_de_passe
             FROM PROFIL
             WHERE adresse_mail = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        if ($user === false) {
            throw new RuntimeException('Aucun compte trouve avec cet email.');
        }

        $storedPassword = (string) $user['mot_de_passe'];
        $isPasswordValid = password_verify($plainPassword, $storedPassword)
            || hash_equals($storedPassword, $plainPassword);

        if (!$isPasswordValid) {
            throw new RuntimeException('Mot de passe incorrect.');
        }

        return [
            'id_profil' => (int) $user['id_profil'],
            'email' => (string) $user['adresse_mail'],
        ];
    }
}
