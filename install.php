<?php

require_once 'db.php';

$db = new DB();
$mysqli = $db->GetInstance();

$sql1_1 = "CREATE TABLE `bs_list` (
    `bs_id` smallint(6) NOT NULL,
    `bs_ptx` float NOT NULL,
    `bs_coords_x` int(11) NOT NULL DEFAULT 0,
    `bs_coords_y` int(11) NOT NULL DEFAULT 0,
    `bs_frequency` decimal(10,4) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci";

$mysqli->query($sql1_1);

$sql1_2 = "ALTER TABLE `bs_list` ADD PRIMARY KEY (`bs_id`)";

$mysqli->query($sql1_2);

$sql1_3 = "ALTER TABLE `bs_list` MODIFY `bs_id` smallint(1) NOT NULL AUTO_INCREMENT";

$mysqli->query($sql1_3);