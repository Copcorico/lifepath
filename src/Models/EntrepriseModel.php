<?php
namespace App\Models;

class Entreprise{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function create($profil_id, $societe, $adresse, $description) {

        $sql = "INSERT INTO ENTREPRISES(id_profil, nom, adresse, description) VALUES(?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$profil_id, $societe, $adresse, $description]);
    }

    public function getByProfilId($profil_id) {
        $sql = "SELECT * FROM ENTREPRISES WHERE id_profil = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$profil_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateByProfilId($profil_id, $nom, $adresse, $description)
    {
        $sql = "UPDATE ENTREPRISES SET nom = ?, adresse = ?, description = ? WHERE id_profil = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nom, $adresse, $description, $profil_id]);
    }
}