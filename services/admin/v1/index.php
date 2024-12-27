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





$app->post('/login_advocate', function () use ($app) {

    verifyRequiredParams(array('data'));

    $data_request = json_decode($app->request->post('data'));
    $user_id = $data_request->user_id;
    $password = $data_request->password;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->loginAdvocate($user_id, $password);
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

$app->post('/advocate_registration', function () use ($app) {


    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $name = $data_request->name;
    $contact = $data_request->contact;
    $email = $data_request->email;
    $password = $data_request->password;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->addNewAdvocate($name, $contact, $email, $password);
    if ($result == 1) {
        $data['message'] = "Advocate Added";
        $data['success'] = true;
    } else {
        $data['message'] = "Error in adding Advocate";
        $data['success'] = false;
        $data['error'] = ($result == 2) ? 'phone number already exists' : 'email already exists';
    }
    echoResponse(200, $data);
});
$app->post('/add_company', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $name = $data_request->name;
    $contact_person = $data_request->contact_person;
    $contact_no = $data_request->contact_no;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->add_company($name, $contact_person, $contact_no);
    if ($result == 1) {
        $data['message'] = "Company Added";
        $data['success'] = true;
    } else {
        $data['message'] = "Error in adding Company";
        $data['success'] = false;
    }
    echoResponse(200, $data);
});

$app->post('/intern_registration', function () use ($app) {


    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $name = $data_request->name;
    $contact = $data_request->contact;
    $email = $data_request->email;
    $password = $data_request->password;
    $start_date = $data_request->start_date;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->addNewIntern($name, $contact, $email, $password, $start_date);
    // echo $result;
    if ($result == 1) {
        $data['message'] = "Intern Added";
        $data['success'] = true;
    } else {
        $data['message'] = "Error in adding Intern";
        $data['success'] = false;
        $data['error'] = ($result == 2) ? 'phone number already exists' : 'email already exists';
    }
    echoResponse(200, $data);
});

$app->get('/get_case_type_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_case_type_list();

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

$app->post('/get_case_stage_list', function () use ($app) {

    verifyRequiredParams(array('case_stage'));
    $case_stage = $app->request->post("case_stage");
    // echo $stage . "\n";
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_case_stage_list($case_stage);

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

$app->post('/get_case_task', function () use ($app) {

    verifyRequiredParams(array('case_no'));
    $case_no = $app->request->post("case_no");
    // echo $stage . "\n";
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_case_task($case_no);

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
$app->post('/get_task_history', function () use ($app) {

    verifyRequiredParams(array('task_id'));
    $task_id = $app->request->post("task_id");
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_task_history($task_id);

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
$app->post('/get_case_info', function () use ($app) {

    verifyRequiredParams(array('case_id'));
    $case_id = $app->request->post("case_id");
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_case_info($case_id);

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
$app->get('/get_company_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_company_list();

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


$app->get('/get_court_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_court_list();

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

$app->get('/get_city_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_city_list();

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

$app->get('/get_case_history', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_case_history();

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


$app->post('/get_task_list', function () use ($app) {
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $case_no = $data_request->case_no;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->get_task_list($case_no);

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


$app->get('/get_unassigned_case_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_unassigned_case_list();

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
$app->get('/get_assigned_case_list', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_assigned_case_list();

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


$app->post('/add_case', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $case_no = $data_request->case_no;
    $year = $data_request->year;
    $case_type = $data_request->case_type;
    $handle_by = $data_request->handle_by;
    $applicant = $data_request->applicant;
    $company_id = $data_request->company_id;
    $opp_name = $data_request->opp_name;
    $court_name = $data_request->court_name;
    $city_id = $data_request->city_id;
    $sr_date = $data_request->sr_date;
    $stage = $data_request->stage;
    $added_by = $data_request->added_by;
    $user_type = $data_request->user_type;


    $case_img = $_FILES["case_image"]["name"];
    $case_img_path = $_FILES["case_image"]["tmp_name"];
    $case_img = preg_replace('/[^A-Za-z0-9.\-]/', '_', $case_img);

    if (file_exists("../../case_image/" . $case_img)) {
        $i = 0;
        $ImageFileName1 = $case_img;
        $Arr1 = explode('.', $ImageFileName1);

        $ImageFileName1 = $Arr1[0] . $i . "." . $Arr1[1];
        while (file_exists("../../case_image/" . $ImageFileName1)) {
            $i++;
            $ImageFileName1 = $Arr1[0] . $i . "." . $Arr1[1];
        }
    } else {
        $ImageFileName1 = $case_img;
    }

    $ImageFileName2 = null;

    if (isset($_FILES["case_docs"]["name"])) {
        for ($i = 0; $i < sizeof($_FILES["case_docs"]["name"]); $i++) {

            $case_docs[$i] = $_FILES["case_docs"]["name"][$i];
            $case_docs_path[$i] = $_FILES["case_docs"]["tmp_name"][$i];

            $case_docs[$i] = preg_replace('/[^A-Za-z0-9.\-]/', '_', $case_docs[$i]);

            if (file_exists("../../case_image/" . $case_docs[$i])) {
                $i = 0;
                $ImageFileName2[$i] = $case_img[$i];
                $Arr1 = explode('.', $ImageFileName1);

                $ImageFileName2[$i] = $Arr1[0] . $i . "." . $Arr1[1];
                while (file_exists("../../case_image/" . $ImageFileName1)) {
                    $i++;
                    $ImageFileName2[$i] = $Arr1[0] . $i . "." . $Arr1[1];
                }
            } else {
                $ImageFileName2[$i] = $case_docs[$i];
            }
        }
    }


    $db = new DbOperation();
    $data = array();
    $result = $db->add_case(
        $case_no,
        $year,
        $company_id,
        $ImageFileName1,
        $opp_name,
        $court_name,
        $city_id,
        $sr_date,
        $case_type,
        $handle_by,
        $applicant,
        $stage,
        $ImageFileName2,
        $added_by,
        $user_type
    );
    if ($result) {
        move_uploaded_file($case_img_path, "../../case_image/" . $ImageFileName1);
        if (isset($_FILES["case_docs"]["name"])) {
            for ($i = 0; $i < sizeof($ImageFileName2); $i++) {
                move_uploaded_file($case_docs_path[$i], "../../case_image/" . $ImageFileName2[$i]);
            }
        }
        // for($i=0;$i<sizeof($))
        $data["message"] = "Case added successfully";
        $data["success"] = true;
    } else {
        $data["message"] = "Error in adding case , try again";
        $data["success"] = false;
    }
    echoResponse(200, $data);
});

$app->post('/task_assignment', function () use ($app) {


    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $case_id = $data_request->case_id;
    $alloted_to = $data_request->alloted_to;
    $alloted_by = $data_request->alloted_by;
    $remark = $data_request->remark;
    $expected_end_date = $data_request->expected_end_date;
    $instruction = $data_request->instruction;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->task_assignment($case_id, $alloted_to, $alloted_by, $remark, $expected_end_date, $instruction);
    // echo $result;
    if ($result) {
        $data['message'] = "Task Assigned Successfully.";
        $data['success'] = true;
    } else {
        $data['message'] = "Error in adding task";
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