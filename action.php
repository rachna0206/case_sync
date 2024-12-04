<?php
session_start();
include "db_connect.php";
error_reporting(E_ALL);
$obj = new DB_Connect();


if (isset($_REQUEST['action'])) 
{

    if ($_REQUEST['action'] == "get_stage") 
    {

        $case_type=$_REQUEST["case_type"];
        
        $stmt_stage = $obj->con1->prepare("SELECT * FROM `stage` WHERE case_type_id=? and lower(`status`)='enable'");
        $stmt_stage->bind_param('i', $case_type);
        $stmt_stage->execute();
        $res_stage = $stmt_stage->get_result();
        $stmt_stage->close();
        $html_stage="<option>--Select Stage--</option>";
        while($stages=mysqli_fetch_array($res_stage))
        {

            $html_stage.='<option value="'.$stages['id'].'" >'.$stages["stage"].'</option>';

        }

        $stmt_court = $obj->con1->prepare("SELECT * FROM `court` WHERE case_type=? and lower(`status`)='enable'");
        $stmt_court->bind_param('i', $case_type);
        $stmt_court->execute();
        $res_court = $stmt_court->get_result();
        $stmt_court->close();
        $html_court="<option>--Select Court--</option>";
        while($Court=mysqli_fetch_array($res_court))
        {

            $html_court.='<option value="'.$Court['id'].'" >'.$Court["name"].'</option>';

        }


        echo $html_stage."@@@@@".$html_court;
    }
}

?>