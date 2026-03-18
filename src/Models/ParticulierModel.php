<?php
namespace App\Models;

class Particulier{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($profil_id, $nom, $prenom){

        $sql = "INSERT INTO PARTICULIER(id_profil, nom, prenom) VALUES(?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$profil_id, $nom, $prenom]);

        return $this->db->lastInsertId();
    }

    public function getByProfilId($profil_id) {
        $sql = "SELECT * FROM PARTICULIER WHERE id_profil = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$profil_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}