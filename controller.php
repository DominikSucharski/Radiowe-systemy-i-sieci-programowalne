<?php
require_once 'db.php';
require_once 'view.php';

class MainController
{
    protected $db;
    protected $view;
    protected $jsonResponse;

    public function __construct()
    {
        $this->db = new DB();
        $this->view = new View();
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
        $this->view->MainView();
    }

    protected function actionGetUserList()
    {
        $users = [];
        $usersFromDb = $this->db->GetUserList();
        while ($user = $usersFromDb->fetch_array(MYSQLI_ASSOC)) {
            $users[] = $user;
        }
        if (empty($users)) {
            echo 'no_users';
        } else {
            echo json_encode($users);
        }
    }

    protected function actionViewUserList()
    {
        $result = $this->db->GetUserList();
        $this->view->DisplayUserList($result);
    }

    protected function actionAddUser()
    {
        header('Content-type: application/json');
        if (!empty($_REQUEST['power']) && !empty($_REQUEST['coord_x']) && !empty($_REQUEST['coord_y']) && !empty($_REQUEST['channel'])) {
            $user_name = $_REQUEST['user_name'] ?? '';
            $power = floatval($_REQUEST['power']);
            $coordX = intval($_REQUEST['coord_x']);
            $coordY = intval($_REQUEST['coord_y']);
            $channel = floatval($_REQUEST['channel']);
            $existingUser = $this->db->FindUserByParams($coordX, $coordY, $channel);
            if ($existingUser) {
                $this->jsonResponse['response'] = 'user_exist';
            } else {
                // zapisanie istniejacych uzytkowników w tablicy
                $users = [];
                $usersFromDb = $this->db->GetUsersForCalculation();
                while ($row = $usersFromDb->fetch_array(MYSQLI_ASSOC)) {
                    $row['coord_x'] = floatval($row['coord_x']);
                    $row['coord_y'] = floatval($row['coord_y']);
                    $row['power'] = intval($row['power']);
                    $row['channel'] = intval($row['channel']);
                    $users[] = $row;
                }
                // zapisanie parametrow w tablicy
                $params = [];
                $paramsFromDb = $this->db->GetSystemParams();
                while ($row = $paramsFromDb->fetch_array(MYSQLI_ASSOC)) {
                    $params[] = $row;
                }
                $pythonResult = $this->callExternalPythonScript($coordX, $coordY, $power, $channel, $users, $params);
                if ($pythonResult === false) {
                    $this->jsonResponse['response'] = 'python_error';
                } else if ($pythonResult == 'no_access') {
                    $this->jsonResponse['response'] = 'no_access';
                } else {
                    $this->jsonResponse['response'] = $pythonResult;
                    $pythonResult = $this->db->GetInstance()->real_escape_string($pythonResult);
                    $this->db->AddUser($user_name, $power, $coordX, $coordY, $channel, $pythonResult);
                }
            }
            $_POST = array();
            $_REQUEST = array();
        } else {
            $this->jsonResponse['response'] = 'set_all_params';
        }
        echo json_encode($this->jsonResponse);
    }

    protected function actionDeleteUser()
    {
        $uid = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
        if ($uid != 0) {
            echo $this->db->DeleteUser($uid);
        } else {
            echo 'incorrect_id';
        }
    }

    protected function callExternalPythonScript($coordX, $coordY, $power, $channel, $users = [], $params = [])
    {
        $url = 'https://europe-west1-my-project-1567770564898.cloudfunctions.net/calculations';
        $pythonRequest = array("coord_x" => $coordX, "coord_y" => $coordY, "power" => $power, "channel" => $channel, "users" => $users, "params" => $params);
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

    protected function actionGetSystemParams()
    {
        $params = [];
        $paramsFromDb = $this->db->GetSystemParams();
        while ($row = $paramsFromDb->fetch_array(MYSQLI_ASSOC)) {
            $params[] = $row;
        }
        echo json_encode($params);
    }
}
