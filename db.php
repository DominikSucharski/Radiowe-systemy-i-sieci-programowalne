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

    public function GetUserList()
    {
        return $this->instance->query("SELECT * FROM users");
    }

    public function GetUsersForCalculation() {
        return $this->instance->query("SELECT user_coords_x coord_x, user_coords_y coord_y, user_channel channel, user_ptx power, user_points points FROM users");
    }

    public function AddOrUpdateUser($power, $coord_x, $coord_y, $channel, $points)
    {
        // TODO: select by $coord_x, $coord_y, $channel; update $power, $points if exist
        $sql = "INSERT INTO users (user_ptx, user_coords_x, user_coords_y, user_channel, user_points) VALUES ({$power}, {$coord_x}, {$coord_y}, {$channel}, '{$points}')";
        $this->instance->query($sql);
    }
}
