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

    public function GetInstance()
    {
        return $this->instance;
    }

    public function GetUserList()
    {
        return $this->instance->query("SELECT * FROM users");
    }

    public function GetUsersForCalculation()
    {
        return $this->instance->query("SELECT user_coords_x coord_x, user_coords_y coord_y, user_channel channel, user_ptx power, aclr_1, aclr_2, user_points points FROM users");
    }

    public function FindUserByParams($coord_x, $coord_y, $channel)
    {
        $userFromDb = $this->instance->query("SELECT user_id, user_ptx power, user_points points FROM users WHERE user_coords_x = {$coord_x} AND user_coords_y = {$coord_y} AND user_channel = {$channel} LIMIT 1");
        if ($userFromDb) {
            return $userFromDb->fetch_array(MYSQLI_ASSOC);
        }
        return false;
    }

    public function AddUser($user_name, $power, $coord_x, $coord_y, $channel, $points, $aclr1, $aclr2)
    {
        $sql = "INSERT INTO users
                (user_name, user_ptx, user_coords_x, user_coords_y, user_channel, user_points, aclr_1, aclr_2)
                VALUES ('{$user_name}', {$power}, {$coord_x}, {$coord_y}, {$channel}, '{$points}', {$aclr1}, {$aclr2})";
        $this->instance->query($sql);
    }

    public function DeleteUser($id)
    {
        $sql = "DELETE FROM users WHERE user_id = {$id}";
        $this->instance->query($sql);
        return $this->instance->affected_rows;
    }

    public function GetSystemParams($withDescription = false)
    {
        if ($withDescription) {
            $sql = "SELECT name, value, description FROM params";
        } else {
            $sql = "SELECT name, value FROM params";
        }
        $paramsFromDb = $this->instance->query($sql);
        if ($paramsFromDb) {
            return $paramsFromDb;
        }
        return false;
    }
}
