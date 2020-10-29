<?php
require_once 'db.php';
require_once 'view.php';

class MainController
{
    protected $db;
    protected $view;

    public function __construct()
    {
        $this->db = new DB();
        $this->view = new View();
    }

    public function StartAction($action)
    {
        $this->view->DisplayHeader();
        $funName = 'action' . $action;
        if (!empty($action) && method_exists($this, $funName)) {
            $this->$funName();
        } else {
            $this->actionMain();
        }

    }

    protected function actionMain()
    {
        echo '<p>Strona główna - do zrobienia przez grupę interfejsu graficznego (PHP + HTML + CSS)</p>';
        echo '<p><a href="?action=ViewBsList">Lista BS</a></p>';
        echo '<p><a href="?action=ViewObliczenia1">Przykładowa akcja - formularz obliczenia1</a> - przycisk / link (grupa interfejsu)</p>';
    }

    protected function actionViewBsList()
    {
        echo 'Dodaj BS:';
        echo '<form action="?action=AddBs" method="POST">';
        echo 'Moc nadajnika: <input type="text" name="power" /> dBm<br>';
        echo 'Współrzędna x: <input type="text" name="coord_x" /><br>';
        echo 'Współrzędna y: <input type="text" name="coord_y" /><br>';
        echo '<input type="submit" value="Dodaj BS" /></form></p>';
        $result = $this->db->GetBsList();
        if ($result) {
            echo '<h3>Lista BS</h3>';
            echo '<table style="border-style: dashed;"><thead><tr><th>id</th><th>PTX</th><th>coords x</th><th>coords y</th></tr></thead><tbody>';
            while ( $bs = mysqli_fetch_assoc ( $result ) ) {
                echo '<tr><td style="padding: 0px 10px;">' . $bs['bs_id'] . '</td><td style="padding: 0px 10px;">' . $bs['bs_ptx'] . ' dBm</td><td style="padding: 0px 10px;">' . $bs['bs_coords_x'] . '</td><td>' . $bs['bs_coords_y'] . '</td></tr>';
            }
            echo '</tbody></table>';
        } else {
            echo 'Brak BS';
        }
    }

    protected function actionAddBs() {
        if(!empty($_POST['power']) && !empty($_POST['coord_x']) && !empty($_POST['coord_y'])) {
            $power = floatval($_POST['power']);
            $coord_x = intval($_POST['coord_x']);
            $coord_y = intval($_POST['coord_y']);
            $this->db->AddBs($power, $coord_x, $coord_y);
            unset($_POST);
        }
        $this->actionViewBsList();
    }

    protected function actionViewObliczenia1()
    {
        echo '<h1>Przykładowe obliczenia</h1>';
        echo '<form action="?action=obliczenia1" method="POST">';
        echo '<p>Moc nadajnika: <input type="text" name="power" /></p>';
        echo '<p>Parametr z bazy danych: <input type="text" name="parametr1" /></p>';
        echo '<input type="submit" value="Oblicz" /></form></p>';
    }

    protected function actionObliczenia1()
    {
        echo '<h1>Przykładowe obliczenia</h1>';
        echo 'Uruchomienie funkcji obliczenia1 napisanej przez grupę obliczeń, przekazanie danych z interfejsu i bazy danych<br>';
        echo 'Parametry obliczeń z interfejsu<br>';
        echo 'Moc nadajnika: ' . ($_POST['power'] ?? 'nie podano');
        echo '<br>Wynik obliczeń: ???';
    }
}
