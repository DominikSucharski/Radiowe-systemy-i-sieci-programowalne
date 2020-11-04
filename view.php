<?php

class View
{
    public function DisplayHeader()
    {
        echo '<h3><a href="index.php">Strona główna</a></h3>';
    }

    public function MainView()
    {
        echo '<p>Strona główna - do zrobienia przez grupę interfejsu graficznego (PHP + HTML + CSS)</p>';
        echo '<p><a href="?action=ViewBsList">Lista BS</a></p>';
        echo '<p><a href="?action=ViewFreeSpaceLoss">Tłumienie w wolnej przestrzeni</a></p>';
    }

    public function DisplayBsList($bs_list)
    {
        echo 'Dodaj BS:';
        echo '<form action="?action=AddBs" method="POST">';
        echo 'Moc nadajnika: <input type="text" name="power" size="10" /> dBm<br>';
        echo 'Współrzędna x: <input type="text" name="coord_x" size="10" /><br>';
        echo 'Współrzędna y: <input type="text" name="coord_y" size="10" /><br>';
        echo 'Częstotliwość: <input type="text" name="frequency" size="10" /> MHz<br>';
        echo '<input type="submit" value="Dodaj BS" /></form></p>';
        if ($bs_list) {
            echo '<h3>Lista BS</h3>';
            echo '<table style="border-style: dashed;"><thead><tr><th>id</th><th>PTX</th><th>coords x</th><th>coords y</th><th>frequency</th></tr></thead><tbody>';
            while ($bs = mysqli_fetch_assoc($bs_list)) {
                echo '<tr><td style="padding: 0px 10px;">' . $bs['bs_id'] . '</td><td style="padding: 0px 10px;">' . $bs['bs_ptx'] . ' dBm</td><td style="padding: 0px 10px;">' . $bs['bs_coords_x'] . '</td><td>' . $bs['bs_coords_y'] . '</td><td>' . $bs['bs_frequency'] . '</td></tr>';
            }
            echo '</tbody></table>';
        } else {
            echo 'Brak BS';
        }
    }

    public function DisplayFreeSpaceLossForm() {
        echo '<h1>Tłumienie w wolnej przestrzeni</h1>';
        echo '<form action="?action=FreeSpaceLoss" method="POST">';
        echo '<p>Odległość: <input type="text" name="distance" /> m</p>';
        echo '<p>Częstotliwość: <input type="text" name="frequency" /> Hz</p>';
        echo '<input type="submit" value="Oblicz" /></form></p>';
    }
}
