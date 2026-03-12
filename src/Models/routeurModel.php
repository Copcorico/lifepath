<?php
namespace App\Models;

class routeModel extends Model {

    /**
     * routeModel constructor.
     * 
     * @param mixed $connection The database connection. If null, a new FileDatabase connection will be created.
     */
    public function __construct($connection = null) {
        if(is_null($connection)) {
            $this->connection = new FileDatabase('tasks', ['task', 'status']);
        } else {
            $this->connection = $connection;
        }
    }
}