<?php
require_once 'db.php';
require_once 'view.php';
require_once 'calculations.php';

class MainController
{
    protected $db;
    protected $view;
    protected $calculations;
    protected $jsonResponse;

    public function __construct()
    {
        $this->db = new DB();
        $this->view = new View();
        $this->calculations = new Calculations();
        $this->jsonResponse = [];
        $this->jsonResponse = ['response' => [], 'request' => []];
    }

    public function StartAction($action)
    {
        $funName = 'action' . $action;
        if (!empty($action) && method_exists($this, $funName)) {
            $this->$funName();
        } else {
            $this->actionMain();
        }

    }

    protected function actionMain()
    {
        $this->view->DisplayHeader();
        $this->view->MainView();
    }

    protected function actionViewUserList()
    {
        $this->view->DisplayHeader();
        $result = $this->db->GetUserList();
        $this->view->DisplayUserList($result);
    }

    protected function actionAddUser()
    {
        header('Content-type: application/json');     
        if (!empty($_POST['power']) && !empty($_POST['coord_x']) && !empty($_POST['coord_y'])) {
            $power = floatval($_POST['power']);
            $coordX = intval($_POST['coord_x']);
            $coordY = intval($_POST['coord_y']);
            $channel = floatval($_POST['channel']);
            $users = [];
            $usersFromDb = $this->db->GetUsersForCalculation();
            while($row = $usersFromDb->fetch_array(MYSQLI_ASSOC)) {
                $row['coord_x'] = floatval($row['coord_x']);
                $row['coord_y'] = floatval($row['coord_y']);
                $row['power'] = intval($row['power']);
                $row['channel'] = intval($row['channel']);
                $users[] = $row;
            }
            $pythonResult = $this->callExternalPythonScript($coordX, $coordY, $power, $channel, $users);
            // var_dump($pythonResult);  // TODO: delete after tests
            if($pythonResult === false) {
                $this->jsonResponse['response'] = 'Błąd podczas wykonywania obliczeń';
            }
            else if($pythonResult == 'false') {
                $this->jsonResponse['response'] = 'Brak dostępu dla użytkownika';
            }
            else {
                $this->jsonResponse['response'] = $pythonResult;
                $pythonResult = $this->db->GetInstance()->real_escape_string($pythonResult);
                $this->db->AddOrUpdateUser($power, $coordX, $coordY, $channel, $pythonResult);
            } 
            // unset($_POST);
        }
        else {
            $this->jsonResponse['response'] = 'Podaj wszystkie parametry';
        }
        echo json_encode($this->jsonResponse);
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

    public function callExternalPythonScript($coordX, $coordY, $power, $channel, $users = [])
    {
        $url = 'https://europe-west1-my-project-1567770564898.cloudfunctions.net/calculations';
        $pythonRequest = array("coord_x" => $coordX, "coord_y" => $coordY, "power" => $power, "channel" => $channel, "users" => $users);
        $pythonRequest = json_encode($pythonRequest, true);  
        $this->jsonResponse['request'] = $pythonRequest;
        $options = array(
            'http' => array(
                'header' => "Content-type: application/json",
                'method' => 'POST',
                'content' => $pythonRequest,
            ),
        );
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }


    protected function actionClearDb() {
        $this->db->GetInstance()->query("TRUNCATE TABLE users");
    }
}
