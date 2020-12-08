<?php

require_once 'db.php';

$db = new DB();
$mysqli = $db->GetInstance();

// tabela users
$sql1_1 = "CREATE TABLE `users` (
    `user_id` smallint(6) NOT NULL,
    `user_name` varchar(32) COLLATE utf8_polish_ci NOT NULL,
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

// tabela params
$sql2_1 = "CREATE TABLE `params` (
    `name` varchar(32) COLLATE utf8_polish_ci NOT NULL,
    `value` double DEFAULT 0,
    `description` varchar(255) COLLATE utf8_polish_ci NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci";

var_dump($mysqli->query($sql2_1));

$sql2_2 = "INSERT INTO `params` (`name`, `value`, `description`) VALUES
        ('bandwidth', 10000000, 'Szerokość pasma'),
        ('carrier_frequency', 2.5, 'Częstotliwość nośna'),
        ('matrix_length', 200, 'Długość siatki'),
        ('points_spacing', 1, 'Odstęp między punktami')";

var_dump($mysqli->query($sql2_2));

$sql2_3 = "ALTER TABLE `params` ADD PRIMARY KEY (`name`)";

var_dump($mysqli->query($sql2_3));