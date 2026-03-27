<?php
namespace App\Models;

class Profil {

    protected $db;
    protected $id;
    protected $email;
    protected $password;
    
    public function __construct($db){
        $this->db = $db;
    }

    public function create($telephone, $email, $password, $type){

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO PROFIL(telephone, adresse_mail, mot_de_passe, type) VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$telephone, $email, $hash, $type]);

        return $this->db->lastInsertId();
    }

    public function getProfilFromEmail($email){

        $sql = "SELECT * FROM PROFIL WHERE adresse_mail = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateParticulier($idProfil, $nom, $prenom)
    {
        $sql = "UPDATE PARTICULIER SET nom = ?, prenom = ? WHERE id_profil = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nom, $prenom, $idProfil]);
    }

    public function updateEntreprise($idProfil, $societe, $adresse, $description)
    {
        $sql = "UPDATE ENTREPRISES SET nom = ?, adresse = ?, description = ? WHERE id_profil = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$societe, $adresse, $description, $idProfil]);
    }

    public function updateEmail($idProfil, $email)
    {
        $sql = "UPDATE PROFIL SET adresse_mail = ? WHERE id_profil = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$email, $idProfil]);
    }

    public function getPhoto($idProfil)
    {
        $sql = "SELECT photo FROM PROFIL WHERE id_profil = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idProfil]);

        return $stmt->fetchColumn();
    }

    public function getAllProfiles($limit = 50)
    {
        $limit = (int) $limit;
        if ($limit <= 0) {
            $limit = 50;
        }

        $sql = "SELECT id_profil, photo FROM PROFIL ORDER BY id_profil DESC LIMIT " . $limit;
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updatePhoto($idProfil, $filename)
    {
        $sql = "UPDATE PROFIL SET photo = ? WHERE id_profil = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$filename, $idProfil]);
    }
}