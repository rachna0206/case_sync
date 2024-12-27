<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//including the required files
require_once '../include/DbOperation.php';
require '../libs/Slim/Slim.php';

date_default_timezone_set("Asia/Kolkata");
\Slim\Slim::registerAutoloader();

//require_once('../../PHPMailer_v5.1/class.phpmailer.php');

$app = new \Slim\Slim();


/*
 * login
 * Parameters: {"user_id":"","password":""}
 * Method: POST
 */
$app->post('/login_intern', function () use ($app) {

    verifyRequiredParams(array('data'));

    $data_request = json_decode($app->request->post('data'));
    $user_id = $data_request->user_id;
    $password = $data_request->password;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->loginIntern($user_id, $password);
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Logged in Successfully";
        $data['success'] = true;
    } else {
        $data['message'] = "Incorrect Id or Password";
        $data['success'] = false;
    }
    echoResponse(200, $data);
});
$app->post('/intern_task_list', function () use ($app) {


    // verifyRequiredParams('intern_id');

    $intern_id = $app->request->post('intern_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->intern_task_list($intern_id);
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Intern Task List Found";
        $data['success'] = true;
    } else {
        $data['message'] = "No Tasks Found";
        $data['success'] = false;
    }
    echoResponse(200, $data);

});

function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }

    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["error_code"] = 99;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}
function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}


$app->run();