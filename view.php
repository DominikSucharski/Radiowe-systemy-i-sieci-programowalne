<?php

class View
{
    public function MainView()
    {
        readfile('view/index.html');
        // echo '<p><a href="?action=ViewUserList">Lista użytkowników</a></p>';
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
}
