<?php
namespace App\Models;

class Etudiant {

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($id_particulier, $classe, $id_pilot) {

        $sql = "INSERT INTO ETUDIANTS(id_particulier, classe, id_pilot) VALUES(?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_particulier, $classe, $id_pilot]);
    }

}