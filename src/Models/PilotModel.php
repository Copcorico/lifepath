<?php
namespace App\Models;

class PilotModel{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($id_particulier) {

        $sql = "INSERT INTO PILOTS(id_particulier) VALUES(?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_particulier]);
    }

    public function getPilots()
    {
        $sql = "SELECT PILOTS.id_pilot as id_pilot, CONCAT(PARTICULIER.prenom, ' ', PARTICULIER.nom) as label FROM PILOTS INNER JOIN PARTICULIER ON PILOTS.id_particulier = PARTICULIER.id_particulier";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function exists($id_pilot)
    {
        $sql = "SELECT 1 FROM PILOTS WHERE id_pilot = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_pilot]);

        return (bool) $stmt->fetchColumn();
    }

    public function getPilotIdByParticulierId($id_particulier)
    {
        $sql = "SELECT id_pilot FROM PILOTS WHERE id_particulier = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_particulier]);

        $pilotId = $stmt->fetchColumn();

        return $pilotId !== false ? (int) $pilotId : null;
    }

}