<?php
require_once 'db.php';
require_once 'view.php';
require_once 'calculations.php';

class MainController
{
    protected $db;
    protected $view;
    protected $calculations;

    public function __construct()
    {
        $this->db = new DB();
        $this->view = new View();
        $this->calculations = new Calculations();
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
        $this->view->MainView();
    }

    protected function actionViewBsList()
    {
        $result = $this->db->GetBsList();
        $this->view->DisplayBsList($result);
    }

    protected function actionAddBs()
    {
        if (!empty($_POST['power']) && !empty($_POST['coord_x']) && !empty($_POST['coord_y'])) {
            $power = floatval($_POST['power']);
            $coord_x = intval($_POST['coord_x']);
            $coord_y = intval($_POST['coord_y']);
            $frequency = floatval($_POST['frequency']);
            $this->db->AddBs($power, $coord_x, $coord_y, $frequency);
            unset($_POST);
        }
        $this->actionViewBsList();
    }

    protected function actionViewFreeSpaceLoss()
    {
        $this->view->DisplayFreeSpaceLossForm();
    }

    protected function actionFreeSpaceLoss()
    {
        echo '<h1>Przykładowe obliczenia</h1>';
        echo 'Uruchomienie funkcji free_space_loss napisanej przez grupę obliczeń<br>';
        echo 'Parametry obliczeń z interfejsu:<br>';
        $distance = $_POST['distance'] ?? 0;
        $distance = floatval($distance);
        $frequency = $_POST['frequency'] ?? 0;
        $frequency = floatval($frequency);
        echo 'Odległość: ' . $distance . ' m<br>';
        echo 'Częstotliwość: ' . $frequency . ' Hz<br>';
        echo '<br>Tłumienie: ' . $this->calculations->free_space_loss($distance, $frequency) . ' dB';
    }
}
