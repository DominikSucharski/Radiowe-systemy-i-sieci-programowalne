<?php
require_once 'db_config.php';
class DB
{
    private $instance = null;

    public function __construct()
    {
        $this->instance = new mysqli(constant('DB_HOST'), constant('DB_USER'), constant('DB_PASS'), constant('DB_NAME'));
        if ($this->instance->connect_errno != 0) {
            exit('Błąd połączenia z bazą danych');
        }
        $this->instance->set_charset("utf8");
    }

    public function GetInstance() {
        return $this->instance;
    }

    public function GetBsList()
    {
        return $this->instance->query("SELECT * FROM bs_list");
    }

    public function AddBs($power, $coord_x, $coord_y, $frequency)
    {
        $sql = "INSERT INTO bs_list (bs_ptx, bs_coords_x, bs_coords_y, bs_frequency) VALUES ({$power}, {$coord_x}, {$coord_y}, {$frequency})";
        $this->instance->query($sql);
    }
}
