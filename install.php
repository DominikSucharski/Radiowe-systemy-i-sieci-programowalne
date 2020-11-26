<?php

require_once 'db.php';

$db = new DB();
$mysqli = $db->GetInstance();

$sql1_1 = "CREATE TABLE `users` (
    `user_id` smallint(6) NOT NULL,
    `user_coords_x` double NOT NULL DEFAULT 0,
    `user_coords_y` double NOT NULL DEFAULT 0,
    `user_channel` tinyint(4) NOT NULL DEFAULT 0,
    `user_ptx` int(11) NOT NULL DEFAULT 0,
    `user_points` text NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci";

var_dump($mysqli->query($sql1_1));

$sql1_2 = "ALTER TABLE `users` ADD PRIMARY KEY (`user_id`)";

var_dump($mysqli->query($sql1_2));

$sql1_3 = "ALTER TABLE `users` MODIFY `user_id` smallint(1) NOT NULL AUTO_INCREMENT";

var_dump($mysqli->query($sql1_3));
