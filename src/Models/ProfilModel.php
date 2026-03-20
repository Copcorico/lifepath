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

    public function create($telephone, $email, $password, $type = 'particulier'){

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
}