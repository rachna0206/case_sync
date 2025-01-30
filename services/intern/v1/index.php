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
    verifyRequiredParams(array('intern_id'));


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


$app->post('/task_remark_list', function () use ($app) {

    verifyRequiredParams(array('task_id'));

    $task_id = $app->request->post('task_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->task_remark_list($task_id);
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

$app->post('/add_task_remark', function () use ($app) {


    // verifyRequiredParams('intern_id');
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $task_id = $data_request->task_id;
    $remark = $data_request->remark;
    $stage_id = $data_request->stage_id;
    $remark_date = $data_request->remark_date;
    $case_id = $data_request->case_id;
    $intern_id = $data_request->intern_id;
    $status = $data_request->status;
    $ImageFileName1 = "";

    if (isset($_FILES["task_image"]["name"]) && $_FILES["task_image"]["name"] != null) {
        $task_img = $_FILES["task_image"]["name"];
        $task_img_path = $_FILES["task_image"]["tmp_name"];
        $task_img = preg_replace('/[^A-Za-z0-9.\-]/', '_', $task_img);

        if (file_exists("../../../documents/case/" . $task_img)) {
            $i = 0;
            $ImageFileName1 = $task_img;
            $Arr1 = explode('.', $ImageFileName1);

            $ImageFileName1 = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("../../../documents/case/" . $ImageFileName1)) {
                $i++;
                $ImageFileName1 = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $ImageFileName1 = $task_img;
        }
    }

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->add_task_remark($task_id, $remark, $remark_date, $stage_id, $ImageFileName1, $case_id, $intern_id, $status);
    if ($result) {
        if (isset($_FILES["task_image"]["name"]) && $_FILES["task_image"]["name"] != null) {
            move_uploaded_file($task_img_path, "../../../documents/case/" . $ImageFileName1);
        }
        $data['message'] = "remark added";
        $data['success'] = true;
    } else {
        $data['message'] = "problem in adding remark";
        $data['success'] = false;
    }
    echoResponse(200, $data);

});


// added by Jay - 22-01-2025

$app->post('/intern_case_history', function () use ($app) {


    // verifyRequiredParams('intern_id');
    verifyRequiredParams(array('intern_id'));


    $intern_id = $app->request->post('intern_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->intern_case_history($intern_id);
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Intern Case History Found";
        $data['success'] = true;
    } else {
        $data['message'] = "No Case History Found";
        $data['success'] = false;
    }
    echoResponse(200, $data);

});


// added by Jay - 22-01-2025

$app->post('/get_case_remarks', function () use ($app) {


    // verifyRequiredParams('intern_id');
    verifyRequiredParams(array('case_id'));


    $case_id = $app->request->post('case_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->get_case_remarks($case_id);
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Case Remarks Found";
        $data['success'] = true;
    } else {
        $data['message'] = "No Case Remarks Found";
        $data['success'] = false;
    }
    echoResponse(200, $data);

});

$app->post('/task_reassign', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_json = json_decode($app->request->post('data'));
    $task_id = $data_json->task_id;
    $intern_id = $data_json->intern_id;
    $reassign_id = $data_json->reassign_id;
    $remark = $data_json->remark;
    $remark_date = $data_json->remark_date;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->task_reassign($task_id, $intern_id, $reassign_id, $remark, $remark_date);
    if ($result) {
        $data['message'] = "task reassigned successfully";
        $data['success'] = true;
    } else {
        $data['message'] = "error in reassigning task";
        $data['success'] = false;
    }
    echoResponse(200, $data);

});

$app->post('/case_history_view', function () use ($app) {

    verifyRequiredParams(array('case_id'));

    $case_id = $app->request->post('case_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->case_history_view($case_id);
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Intern Case History Found";
        $data['success'] = true;
    } else {
        $data['message'] = "No Case History Found";
        $data['success'] = false;
    }
    echoResponse(200, $data);
});

$app->post('/case_history_documents', function () use ($app) {

    verifyRequiredParams(array('case_id'));

    $case_id = $app->request->post('case_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->case_history_documents($case_id);
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                if ($key == 'docs') {
                    $temp[$key] = 'https://pragmanxt.com/case_sync_pro/documents/case' . $value;
                } else {
                    $temp[$key] = $value;
                }
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Intern Case History Found";
        $data['success'] = true;
    } else {
        $data['message'] = "No Case History Found";
        $data['success'] = false;
    }
    echoResponse(200, $data);
});

$app->get('/get_interns_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_interns_list();

    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Data found.";
        $data['success'] = true;
    } else {
        $data["message"] = "No data found";
        $data["success"] = false;
    }
    echoResponse(200, $data);
});

$app->get('/get_advocate_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_advocate_list();

    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Data found.";
        $data['success'] = true;
    } else {
        $data["message"] = "No data found";
        $data["success"] = false;
    }
    echoResponse(200, $data);
});
$app->post('/edit_task', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_json = json_decode($app->request->post('data'));
    $task_id = $data_json->task_id;
    $case_id = $data_json->case_id;
    $alloted_to = $data_json->alloted_to;
    $instructions = $data_json->instructions;
    $alloted_by = $data_json->alloted_by;
    $alloted_date = $data_json->alloted_date;
    $expected_end_date = $data_json->expected_end_date;
    $status = $data_json->status;
    $remark = $data_json->remark;

    $db = new DbOperation();
    $result = $db->edit_task($task_id, $case_id, $alloted_to, $instructions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark);
    $data = array();
    if ($result) {
        $data["response"] = "data added successfully.";
        $data["success"] = true;
    } else {
        $data["response"] = "error in inserting data , try again.";
        $data["success"] = false;
    }
    echoResponse(200, $data);
});

$app->post('/task_info', function () use ($app) {


    verifyRequiredParams(array('task_id'));

    $task_id = $app->request->post('task_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->task_info($task_id);
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