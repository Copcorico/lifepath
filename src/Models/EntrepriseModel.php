<?php
namespace App\Models;

class Entreprise{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function create($profil_id, $societe, $adresse) {

        $sql = "INSERT INTO ENTREPRISES(id_profil, nom, adresse) VALUES(?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$profil_id, $societe, $adresse]);
    }

    public function getByProfilId($profil_id) {
        $sql = "SELECT * FROM ENTREPRISES WHERE id_profil = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$profil_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}