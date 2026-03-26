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

    public function getByPilotId($id_pilot)
    {
        $sql = "SELECT E.id_etudiant, E.classe, P.id_particulier as id, P.nom, P.prenom, PR.adresse_mail as email
                FROM ETUDIANTS E
                INNER JOIN PARTICULIER P ON E.id_particulier = P.id_particulier
                INNER JOIN PROFIL PR ON P.id_profil = PR.id_profil
                WHERE E.id_pilot = ?
                ORDER BY P.prenom, P.nom";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_pilot]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}