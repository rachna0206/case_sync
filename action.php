<?php
session_start();
include "db_connect.php";
error_reporting(E_ALL);
$obj = new DB_Connect();


if (isset($_REQUEST['action'])) {

    if ($_REQUEST['action'] == "get_stage") {

        $case_type = $_REQUEST["case_type"];

        $stmt_stage = $obj->con1->prepare("SELECT * FROM `stage` WHERE case_type_id=? and lower(`status`)='enable'");
        $stmt_stage->bind_param('i', $case_type);
        $stmt_stage->execute();
        $res_stage = $stmt_stage->get_result();
        $stmt_stage->close();
        $html_stage = "<option>--Select Stage--</option>";
        while ($stages = mysqli_fetch_array($res_stage)) {

            $html_stage .= '<option value="' . $stages['id'] . '" >' . $stages["stage"] . '</option>';
        }

        $stmt_court = $obj->con1->prepare("SELECT * FROM `court` WHERE case_type=? and lower(`status`)='enable'");
        $stmt_court->bind_param('i', $case_type);
        $stmt_court->execute();
        $res_court = $stmt_court->get_result();
        $stmt_court->close();
        $html_court = "<option>--Select Court--</option>";
        while ($Court = mysqli_fetch_array($res_court)) {

            $html_court .= '<option value="' . $Court['id'] . '" >' . $Court["name"] . '</option>';
        }


        echo $html_stage . "@@@@@" . $html_court;
    }

    if ($_REQUEST['action'] == "add_city") {
        $state = $_REQUEST['state'];
        $city_name = $_REQUEST['city'];
        $status = 'enable';


        $stmt = $obj->con1->prepare("INSERT INTO `city`(`state_id`,`name`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss", $state, $city_name, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_city .= '<option value="' . $last_id . '" selected>' . $city_name . '</option>';
        }
        echo $html_city;
    }


    if ($_REQUEST['action'] == "add_state") {
        $state = $_REQUEST['state'];
        $status = 'enable';


        $stmt = $obj->con1->prepare("INSERT INTO `state`(`state_name`,`status`) VALUES (?,?)");
        $stmt->bind_param("ss", $state, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_state .= '<option value="' . $last_id . '" selected>' . $state . '</option>';
        }
        echo $html_state;
    }


    if ($_REQUEST['action'] == "add_casetype") {
        $c_type = $_REQUEST['c_type'];
        $status = 'enable';


        $stmt = $obj->con1->prepare("INSERT INTO `case_type`(`case_type`,`status`) VALUES (?,?)");
        $stmt->bind_param("ss", $c_type, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_state .= '<option value="' . $last_id . '" selected>' . $c_type . '</option>';
        }
        echo $html_state;
    }

    if ($_REQUEST['action'] == "add_citys") {
        $state_id = $_REQUEST['state_id'];
        $name = $_REQUEST['name'];
        $status = 'enable';


        $stmt = $obj->con1->prepare("INSERT INTO `city`(`state_id`,`name`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss", $state_id, $name, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_citys .= '<option value="' . $last_id . '" selected>' . $name . '</option>';
        }
        echo $html_citys;
    }

    if ($_REQUEST['action'] == "add_alloted_to") {
        $int_name = $_REQUEST['int_name'];
        $contact = $_REQUEST['contact'];
        $email = $_REQUEST['email'];
        $password = $_REQUEST['password'];
        $date = $_REQUEST['date'];
        $status = 'enable';
        $type = 'intern';

        $stmt = $obj->con1->prepare("INSERT INTO `staff`(`name`,`contact`,`email`,`password`,`type`, `date/time`,`status`) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss", $int_name, $contact, $email, $password, $type, $date, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_intern .= '<option value="' . $last_id . '" selected>' . $int_name . '</option>';
        }
        echo $html_intern;
    }

    if ($_REQUEST['action'] == "add_stage") {
        $case_type_id = $_REQUEST['case_type_id'];
        $stage = $_REQUEST['stage'];
        $status = 'enable';

        $stmt = $obj->con1->prepare("INSERT INTO `stage`(`case_type_id`,`stage`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss", $case_type_id, $stage, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_stage .= '<option value="' . $last_id . '" selected>' . $stage . '</option>';
        }
        echo $html_stage;
    }



    if ($_REQUEST['action'] == "add_court") {
        $case_type = $_REQUEST['case_type'];
        $court = $_REQUEST['court'];
        $status = 'enable';

        $stmt = $obj->con1->prepare("INSERT INTO `court`(`case_type`,`name`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss", $case_type, $court, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_court .= '<option value="' . $last_id . '" selected>' . $court . '</option>';
        }
        echo $html_court;
    }

    if ($_REQUEST['action'] == "add_advocate") {
        $adv_name = $_REQUEST['adv_name'];
        $adv_contact = $_REQUEST['adv_contact'];
        $adv_email = $_REQUEST['adv_email'];
        $adv_password = $_REQUEST['adv_password'];
        $status = 'enable';
        $type = "admin";

        $stmt = $obj->con1->prepare("INSERT INTO `staff`(`name`,`contact`,`email`,`password`,`status`,`type`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("sssss", $adv_name, $adv_contact, $adv_email, $adv_password, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_advocate .= '<option value="' . $last_id . '" selected>' . $adv_name . '</option>';
        }
        echo $html_advocate;
    }


    if ($_REQUEST['action'] == "add_company") {
        $comp_name = $_REQUEST['comp_name'];
        $person = $_REQUEST['person'];
        $contact = $_REQUEST['contact'];

        $status = 'enable';


        $stmt = $obj->con1->prepare("INSERT INTO `company`(`name`,`contact_person`,`contact_no`,`status`) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $comp_name, $person, $contact, $status);
        $Resp = $stmt->execute();
        $last_id = mysqli_insert_id($obj->con1);
        $stmt->close();
        if ($Resp) {
            $html_company .= '<option value="' . $last_id . '" selected>' . $comp_name . '</option>';
        }
        echo $html_company;
    }

    if ($_REQUEST["action"] == "filter_case") {
        $html_case = "";
        $case_type_id = $_REQUEST["case_type_id"];
        $city_id = $_REQUEST["city_id"];

        $stmt = $obj->con1->prepare("SELECT c1.id, c1.case_no, a1.name FROM `case` c1, `staff` a1 WHERE c1.handle_by = a1.id AND `case_type` = ? AND `city_id` = ?");
        $stmt->bind_param("ii", $case_type_id, $city_id);
        $stmt->execute();
        $Resp = $stmt->get_result();
        $stmt->close();

        while ($row = mysqli_fetch_array($Resp)) {

            $html_case .= '<option value="' . $row['id'] . '" >' . $row["case_no"] . ' - (' . $row["name"] . ')' . '</option>';
        }
        echo $html_case;
    }
}
