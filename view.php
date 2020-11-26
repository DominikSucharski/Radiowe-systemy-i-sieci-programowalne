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
        echo '<p><a href="?action=ViewUserList">Lista użytkowników</a></p>';
        echo '<p><a href="?action=ViewFreeSpaceLoss">Tłumienie w wolnej przestrzeni</a></p>';
        echo '<p><a href="?action=ClearDb">Wyczyszczenie bazy danych<a></p>';
    }

    public function DisplayUserList($userList)
    {
        ?>

        <!-- <script src="js grupy obliczeń"></script> -->

        <form action="?action=AddUser" method="POST">
        Moc nadajnika: <input type="text" name="power" size="10" /> dBm<br>
        Współrzędna x: <input type="text" name="coord_x" size="10" /><br>
        Współrzędna y: <input type="text" name="coord_y" size="10" /><br>
        Kanał: <input type="text" name="channel" size="10" /><br>
        <input type="submit" value="Dodaj użytkownika" /></form></p>

        <?php
        if ($userList) {
            echo '<h3>Lista użytkowników</h3>';
            echo '<table style="border-style: dashed;"><thead><tr><th>id</th><th>PTX</th><th>coords x</th><th>coords y</th><th>channel</th><th>result</th></tr></thead><tbody>';
            while ($bs = mysqli_fetch_assoc($userList)) {
                echo '<tr><td style="padding: 0px 10px;">' . $bs['user_id'] . '</td><td style="padding: 0px 10px;">' . $bs['user_ptx'] . ' dBm</td><td style="padding: 0px 10px;">' . $bs['user_coords_x'] . '</td><td>' . $bs['user_coords_y'] . '</td><td>' . $bs['user_channel'] . '</td><td>' . $bs['user_points'] . '</td></tr>';
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
