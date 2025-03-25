<?php
ob_start();
include("db_connect.php");
$obj = new DB_Connect();
error_reporting(0);
session_start();

if ($_REQUEST["action"] == "get_notification") {

    $html = '';
    $ids = "";
    if ($_SESSION["user_type"] == "advocate") {
        $noti_qry = "SELECT n1.*,a1.name FROM `notification` n1,staff a1 where n1.sender_id=a1.id and n1.status='1' order by n1.id desc ";
    } else {
        $noti_qry = "SELECT n1.*,i1.name FROM `notification` n1,staff i1 where n1.sender_id=i1.id and n1.status='1' and n1.receiver_id='" . $_SESSION["intern_id"] . "' order by n1.id desc; ";
    }

    //echo $noti_qry;
    $res_noti = $obj->select($noti_qry);
    $num = mysqli_num_rows($res_noti);



    if ($num > 0) {


        $i = 0;
        $html .= '<li class="dropdown-header">
              You have <span id="count"></span> new notifications
              <a href="javascript:read_all()"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>';

        while ($notification = mysqli_fetch_array($res_noti)) {
            $ids .= $notification["id"] . ",";

            if ($notification["type"] == "task_created") {
                $msg = "Task Created";
                $icon = "bi bi-file-earmark-plus text-primary";
            } else if ($notification["type"] == "task_reassigned") {
                $msg = "Task Re-assigned";
                $icon = "bi bi-file-earmark-plus text-primary";
            } else {
                $msg = "Task Completed";
                $icon = "bi bi-check-square text-success";
            }
            if ($notification["sender_type"] == "advocate") {
                $by = "advocate";
            } else {
                $by = $notification["name"];
            }
            $html .= '<li>
              <hr class="dropdown-divider">
            </li>

            <a href="javascript:show_task(\'' . $_SESSION['user_type'] . '\',\'' . $notification["id"] . '\')"><li class="notification-item">
              <i class="' . $icon . '"></i>
              <div>';

            $html .= '<h4>' . $notification["msg"] . '</h4>
            
            <p>' . $by . '</p><p>' . date("d/m/Y h:i a", strtotime($notification["datetime"])) . '</p>
              </div>
            </li></a>
            </div>';

            if ($i != ($num - 1)) {
                $html .= '<div tabindex="-1" class="dropdown-divider"></div>';
            }

            $i++;
        }
    }
    if ($num == 0) {
        $html .= '<li class="notification-item">No new notification</li>';
    }

    echo $html . "@@@@" . $num . "@@@@" . rtrim($ids, ",");
}
if ($_REQUEST["action"] == "get_Playnotification") {

    if ($_SESSION["user_type"] == "advocate") {
        $noti_qry = "SELECT * FROM `notification` where playstatus='1' order by id desc ";
    } else {
        $noti_qry = "SELECT * FROM `notification` where playstatus='1' and receiver_id='" . $_SESSION["id"] . "' order by id desc ";
    }
    // echo $noti_qry;
    $res_noti = $obj->select($noti_qry);
    $num = mysqli_num_rows($res_noti);
    $ids = "";
    while ($notification = mysqli_fetch_array($res_noti)) {
        $ids .= $notification["id"] . ",";
    }
    echo $num . "@@@@" . rtrim($ids, ",");
}
if ($_REQUEST["action"] == "removeplaysound") {

    $ids = explode(',', $_REQUEST["id"]);


    for ($i = 0; $i < sizeof($ids); $i++) {
        $update_noti = "UPDATE `notification` SET `playstatus`=0 WHERE id='" . $ids[$i] . "'";
        $res_update = $obj->update($update_noti);
    }
}
if ($_REQUEST["action"] == "removenotification") {


    $id = $_REQUEST["id"];

    $update_noti = "UPDATE `notification` SET `status`=0,playstatus=0 where id='" . $id . "'";

    $res_update = $obj->update($update_noti);
}
if ($_REQUEST["action"] == "read_all") {


    if ($_SESSION["user_type"] == "advocate") {
        $update_noti = "UPDATE `notification` SET `status`=0 ,`playstatus`=0 where sender_type='intern'";
    } else {
        $update_noti = "UPDATE `notification` SET `service_status`=0 ,`service_playstatus`=0 where sender_type='advocate'";
    }

    $res_update = $obj->update($update_noti);
}
