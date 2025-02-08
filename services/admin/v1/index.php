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

$app->post('/stage_court_list', function () use ($app) {

    verifyRequiredParams(array('case_type_id'));
    $case_type_id = $app->request->post("case_type_id");
    $db = new DbOperation();
    $data = array();
    $data["stage_list"] = array();
    $data["court_list"] = array();

    //$result will be an array as the function returns result of two queries in an array
    $result = $db->stage_court_list($case_type_id);

    if (mysqli_num_rows($result[0]) > 0 || mysqli_num_rows($result[1]) > 0) {
        //first element of the array is of stage list
        if (mysqli_num_rows($result[0]) > 0) {
            while ($row = $result[0]->fetch_assoc()) {
                $temp = array();
                foreach ($row as $key => $value) {
                    $temp[$key] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['stage_list'], $temp);
            }
        }
        //second element of the array is of court list
        if (mysqli_num_rows($result[1]) > 0) {
            while ($row = $result[1]->fetch_assoc()) {
                $temp = array();
                foreach ($row as $key => $value) {
                    $temp[$key] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['court_list'], $temp);
            }
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
$app->post('/get_case_documents', function () use ($app) {

    verifyRequiredParams(array('case_no'));
    $case_no = $app->request->post("case_no");
    // echo $stage . "\n";
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->get_case_documents($case_no);

    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                if ($key == 'docs') {
                    $temp[$key] = "https://pragmanxt.com/case_sync_pro/documents/case/" . $value;
                } else {
                    $temp[$key] = $value;
                }
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


$app->post('/stage_list', function () use ($app) {

    verifyRequiredParams(array('case_id'));
    $case_id = $app->request->post('case_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->stage_list($case_id);
    // print(mysqli_num_rows($result));
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "Stage list found";
        $data['success'] = true;
    } else {
        $data['message'] = "Error in fetching stage list or no stage exist for this.";
        $data['success'] = false;
    }
    echoResponse(200, $data);
});


$app->post('/next_stage', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_json = json_decode($app->request->post('data'));
    $case_id = $data_json->case_id;
    $next_stage = $data_json->next_stage;
    $next_date = $data_json->next_date;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result = $db->next_stage($case_id, $next_stage, $next_date);
    if ($result) {
        $data['message'] = "Stage updated successfully";
        $data['success'] = true;
    } else {
        $data['message'] = "Error in updating stage and next date";
        $data['success'] = false;
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

$app->post('/notifications', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->notifications();
    $data['counters'] = array();
    $resp = ['unassigned_count', 'assigned_count', 'history_count', 'advocate_count', 'intern_count', 'company_count', 'task_count', 'todays_case_count', 'counters_count','new_case_counter'];

    if (mysqli_num_rows($result[0]) > 0) {
        while ($row = $result[0]->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
    }
    $temp = array();

    foreach ($resp as $i => $counter) {
        $temp[$counter] = $result[$i + 1];
        $temp = array_map('utf8_encode', $temp);
    }
    array_push($data['counters'], $temp);

    $data['message'] = mysqli_num_rows($result[0]) > 0 ? "Data found." : "No data found";
    //$data['success'] = mysqli_num_rows($result[0]) > 0;
    $data['success'] = true;

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


$app->post('/get_case_counter', function () use ($app) {

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_case_counter();

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
                if ($key == 'docs') {
                    $temp[$key] = "https://pragmanxt.com/case_sync_pro/documents/case/" . $value;
                } else {
                    $temp[$key] = $value;
                }
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
$app->post('/read_notification', function () use ($app) {

    // verifyRequiredParams($app->request->post('not_id'));
    $not_id = $app->request->post('not_id');

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->read_notification($not_id);

    if ($result) {
        $data['message'] = "Notification read";
        $data['success'] = true;
    } else {
        $data["message"] = "Error in reading notification";
        $data["success"] = false;
    }
    echoResponse(200, $data);
});
$app->post('/get_todays_case', function () use ($app) {
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $result = $db->get_todays_case();

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

// $app->post('/get_task_list', function () use ($app) {
//     verifyRequiredParams(array('data'));
//     $data_request = json_decode($app->request->post('data'));
//     $case_no = $data_request->case_no;

//     $db = new DbOperation();
//     $data = array();
//     $data["data"] = array();

//     $result = $db->get_task_list($case_no);

//     if (mysqli_num_rows($result) > 0) {
//         while ($row = $result->fetch_assoc()) {
//             $temp = array();
//             foreach ($row as $key => $value) {
//                 $temp[$key] = $value;
//             }
//             $temp = array_map('utf8_encode', $temp);
//             array_push($data['data'], $temp);
//         }
//         $data['message'] = "Data found.";
//         $data['success'] = true;
//     } else {
//         $data["message"] = "No data found";
//         $data["success"] = false;
//     }
//     echoResponse(200, $data);
// });


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
    $complainant_advocate = $data_request->complainant_advocate;
    $respondent_advocate = $data_request->respondent_advocate;
    $date_of_filing = $data_request->date_of_filing;
    $next_date = $data_request->next_date;
    $ImageFileName1 = "";
    if (isset($_FILES["case_image"]["name"])) {

        $case_img = $_FILES["case_image"]["name"];
        $case_img_path = $_FILES["case_image"]["tmp_name"];
        $case_img = preg_replace('/[^A-Za-z0-9.\-]/', '_', $case_img);

        if (file_exists("../../../documents/case/" . $case_img)) {
            $i = 0;
            $ImageFileName1 = $case_img;
            $Arr1 = explode('.', $ImageFileName1);

            $ImageFileName1 = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("../../../documents/case/" . $ImageFileName1)) {
                $i++;
                $ImageFileName1 = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $ImageFileName1 = $case_img;
        }

    }
    $ImageFileName2 = null;

    if (isset($_FILES["case_docs"]["name"])) {
        for ($i = 0; $i < sizeof($_FILES["case_docs"]["name"]); $i++) {

            $case_docs[$i] = $_FILES["case_docs"]["name"][$i];
            $case_docs_path[$i] = $_FILES["case_docs"]["tmp_name"][$i];

            $case_docs[$i] = preg_replace('/[^A-Za-z0-9.\-]/', '_', $case_docs[$i]);

            if (file_exists("../../../documents/case/" . $case_docs[$i])) {
                $i = 0;
                $ImageFileName2[$i] = $case_img[$i];
                $Arr1 = explode('.', $ImageFileName1);

                $ImageFileName2[$i] = $Arr1[0] . $i . "." . $Arr1[1];
                while (file_exists("../../../documents/case/" . $ImageFileName1)) {
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
    $result = $db->add_case($case_no, $year, $company_id, $ImageFileName1, $opp_name, $court_name, $city_id, $sr_date, $case_type, $handle_by, $applicant, $stage, $ImageFileName2, $added_by, $user_type, $complainant_advocate, $respondent_advocate, $date_of_filing, $next_date);
    if ($result) {
        if (isset($_FILES["case_image"]["name"])) {
            move_uploaded_file($case_img_path, "../../../documents/case/" . $ImageFileName1);
        }
        if (isset($_FILES["case_docs"]["name"])) {
            for ($i = 0; $i < sizeof($ImageFileName2); $i++) {
                move_uploaded_file($case_docs_path[$i], "../../../documents/case/" . $ImageFileName2[$i]);
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

$app->post('/add_task', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_json = json_decode($app->request->post('data'));
    $case_id = $data_json->case_id;
    $alloted_to = $data_json->alloted_to;
    $instructions = $data_json->instructions;
    $alloted_by = $data_json->alloted_by;
    $alloted_date = $data_json->alloted_date;
    $expected_end_date = $data_json->expected_end_date;
    $status = $data_json->status;
    $remark = $data_json->remark;

    $db = new DbOperation();
    $result = $db->add_task($case_id, $alloted_to, $instructions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark);
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


$app->post('/delete_task', function () use ($app) {

    verifyRequiredParams(array('task_id'));
    $task_id = $app->request->post('task_id');

    $db = new DbOperation();
    $result = $db->delete_task($task_id);
    $data = array();
    if ($result) {
        $data["response"] = "data deleted successfully";
        $data["success"] = true;
    } else {
        $data["response"] = "error in deleting data , try again.";
        $data["success"] = false;
    }
    echoResponse(200, $data);

});

$app->post('/delete_intern', function () use ($app) {

    verifyRequiredParams(array('intern_id'));
    $intern_id = $app->request->post('intern_id');

    $db = new DbOperation();
    $result = $db->delete_intern($intern_id);
    $data = array();
    if ($result) {
        $data["response"] = "intern deleted successfully";
        $data["success"] = true;
    } else {
        $data["response"] = "error in deleting data , check if intern has task assigned.";
        $data["success"] = false;
    }
    echoResponse(200, $data);

});
$app->post('/delete_advocate', function () use ($app) {

    verifyRequiredParams(array('advocate_id'));
    $advocate_id = $app->request->post('advocate_id');

    $db = new DbOperation();
    $result = $db->delete_advocate($advocate_id);
    $data = array();
    if ($result) {
        $data["response"] = "advocate deleted successfully";
        $data["success"] = true;
    } else {
        $data["response"] = "error in deleting data , check if advocate has task assigned.";
        $data["success"] = false;
    }
    echoResponse(200, $data);

});

$app->post('/delete_company', function () use ($app) {

    verifyRequiredParams(array('company_id'));
    $company_id = $app->request->post('company_id');

    $db = new DbOperation();
    $result = $db->delete_company($company_id);
    $data = array();
    if ($result) {
        $data["response"] = "company deleted successfully";
        $data["success"] = true;
    } else {
        $data["response"] = "error in deleting data , check if company has task assigned.";
        $data["success"] = false;
    }
    echoResponse(200, $data);

});

$app->post('/edit_intern', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_json = json_decode($app->request->post('data'));
    $intern_id = $data_json->intern_id;
    $name = $data_json->name;
    $contact = $data_json->contact;
    $email = $data_json->email;
    $password = $data_json->password;
    $status = $data_json->status;

    $db = new DbOperation();
    $result = $db->edit_intern($intern_id, $name, $contact, $email, $status, $password);
    $data = array();
    if ($result) {
        $data["response"] = "data edited successfully";
        $data["success"] = true;
    } else {
        $data["response"] = "error in editing data , try again.";
        $data["success"] = false;
    }
    echoResponse(200, $data);

});

$app->post('/edit_advocate', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_json = json_decode($app->request->post('data'));
    $advocate_id = $data_json->advocate_id;
    $name = $data_json->name;
    $contact = $data_json->contact;
    $email = $data_json->email;
    $password = $data_json->password;
    $status = $data_json->status;

    $db = new DbOperation();
    $result = $db->edit_advocate($advocate_id, $name, $contact, $email, $status, $password);
    $data = array();
    if ($result) {
        $data["response"] = "data edited successfully";
        $data["success"] = true;
    } else {
        $data["response"] = "error in editing data , try again.";
        $data["success"] = false;
    }
    echoResponse(200, $data);

});
$app->post('/edit_company', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_json = json_decode($app->request->post('data'));
    $company_id = $data_json->company_id;
    $name = $data_json->name;
    $contact_person = $data_json->contact_person;
    $contact_no = $data_json->contact_no;
    $status = $data_json->status;

    $db = new DbOperation();
    $result = $db->edit_company($company_id, $name, $contact_person, $contact_no, $status);
    $data = array();
    if ($result) {
        $data["response"] = "data edited successfully";
        $data["success"] = true;
    } else {
        $data["response"] = "error in editing data , try again.";
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