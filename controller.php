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
        if (isset($_REQUEST['power']) && isset($_REQUEST['coord_x']) && isset($_REQUEST['coord_y']) && !empty($_REQUEST['channel'])) {
            $user_name = $_REQUEST['user_name'] ?? '';
            $power = floatval($_REQUEST['power']);
            $coordX = intval($_REQUEST['coord_x']);
            $coordY = intval($_REQUEST['coord_y']);
            $channel = floatval($_REQUEST['channel']);
            $aclr1 = intval($_REQUEST['aclr_1']);
            $aclr2 = intval($_REQUEST['aclr_2']);
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
                    $row['aclr_1'] = intval($row['aclr_1']);
                    $row['aclr_2'] = intval($row['aclr_2']);
                    $users[] = $row;
                }
                // zapisanie parametrow w tablicy
                $params = [];
                $paramsFromDb = $this->db->GetSystemParams();
                while ($row = $paramsFromDb->fetch_array(MYSQLI_ASSOC)) {
                    $params[] = $row;
                    $paramsByName[$row['name']] = $row['value'];
                }

                // maksymalna liczba prób
                $tryLimit = 10;
                $initialPower = $power;
                $initialChannel = $channel;
                // naprzemienne zwiększanie i zmniejszanie kanału np. 5 -> 6 -> 4 -> 7 -> 3
                $recentlyIncreasedChannel = false;
                // odległość od początkowego kanału
                $channelDifference = 0;
                // max i min kanał
                $maxChannel = 10;
                $minChannel = 1;

                do {
                    $tryLimit--;

                    $pythonResult = $this->callExternalPythonScript($coordX, $coordY, $power, $channel, $aclr1, $aclr2, $users, $params);
                    if ($pythonResult === false) {
                        $this->jsonResponse['response'] = 'python_error';
                        break;
                    } else if ($pythonResult == 'no_access') {
                        $this->jsonResponse['response'] = 'no_access';
                    } else {
                        $this->jsonResponse['response'] = $pythonResult;
                        $pythonResult = $this->db->GetInstance()->real_escape_string($pythonResult);
                        $this->db->AddUser($user_name, $power, $coordX, $coordY, $channel, $pythonResult, $aclr1, $aclr2);
                        break;
                    }

                    // najpierw zmniejszanie mocy
                    $power -= $paramsByName['power_reduction_step'];

                    // później próba zmiany kanału
                    if ($power < $paramsByName['min_power']) {
                        $power = $initialPower;
                        if ($recentlyIncreasedChannel) {
                            // zmniejszeie numeru kanału
                            $channel = $initialChannel - $channelDifference;
                            $recentlyIncreasedChannel = false;
                        } else {
                            // zwiększenie numeru kanału
                            $channel = ++$channelDifference + $initialChannel;
                            $recentlyIncreasedChannel = true;
                        }
                        if ($channel < $minChannel) {
                            $channel = ++$channelDifference + $initialChannel;
                        } elseif ($channel > $maxChannel) {
                            $channel = $initialChannel - $channelDifference;
                        }
                    }
                } while ($tryLimit > 0);
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

    protected function callExternalPythonScript($coordX, $coordY, $power, $channel, $aclr1, $aclr2, $users = [], $params = [])
    {
        $url = 'https://europe-west1-my-project-1567770564898.cloudfunctions.net/calculations';
        $pythonRequest = array(
            "coord_x" => $coordX,
            "coord_y" => $coordY,
            "power" => $power,
            "channel" => $channel,
            "aclr_1" => $aclr1,
            "aclr_2" => $aclr2,
            "users" => $users,
            "params" => $params,
        );
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
        $paramsFromDb = $this->db->GetSystemParams(true);
        while ($row = $paramsFromDb->fetch_array(MYSQLI_ASSOC)) {
            $params[] = $row;
        }
        echo json_encode($params);
    }
}
