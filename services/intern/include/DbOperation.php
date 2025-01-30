<?php
date_default_timezone_set("Asia/Kolkata");
class DbOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }

    public function loginIntern($user_id, $password)
    {
        $stmt_login = $this->con->prepare("SELECT * FROM `interns` WHERE `email`=? AND BINARY `password`=? AND status = 'enable' ");
        $stmt_login->bind_param("ss", $user_id, $password);
        $stmt_login->execute();
        $result = $stmt_login->get_result();
        $stmt_login->close();
        return $result;
    }
    public function intern_task_list($intern_id)
    {
        $stmt = $this->con->prepare("SELECT t.id as task_id,c.id as case_id,c.stage as stage_id,c.case_no,t.instruction,i.name as alloted_to,a.name as alloted_by,t.alloted_date,t.expected_end_date,t.status,st.stage  from task as t join `case` as c on t.case_id = c.id join interns as i on i.id = t.alloted_to join advocate as a on a.id = t.alloted_by join stage as st on st.id = c.stage where i.id = ?;");

        //  $stmt = $this->con->prepare("SELECT t.*,c.case_no,date_format(t.alloted_date,'%d-%m-%Y') as adt,CASE WHEN t.action_by = 'intern' THEN i.name WHEN t.action_by = 'advocate' THEN a.name ELSE 'Unknown' END AS alloted_by_name, it.name AS alloted_to_name FROM  task t LEFT JOIN  interns i ON t.alloted_by = i.id AND t.action_by = 'intern' LEFT JOIN  advocate a ON t.alloted_by = a.id AND t.action_by = 'advocate' LEFT JOIN `case` c ON t.case_id = c.id LEFT JOIN 
        // interns it ON t.alloted_to = it.id where  t.alloted_to =? ORDER BY t.id DESC"); 
        $stmt->bind_param("s", $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function add_task_remark($task_id, $remark, $remark_date, $stage_id, $ImageFileName1, $case_id, $intern_id, $status)
    {
        $stmt = $this->con->prepare("INSERT INTO case_hist(`task_id`, `stage`, `remarks`, `dos`, `status`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $task_id, $stage_id, $remark, $remark_date, $status);
        $result = $stmt->execute();
        $stmt->close();
        $Resp_img = true;
        if ($ImageFileName1 != "") {
            $user_type = "intern";
            $stmt_image = $this->con->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`,`added_by`,`user_type`) VALUES (?, ?,?,?)");
            $stmt_image->bind_param("isis", $case_id, $ImageFileName1, $intern_id, $user_type);
            $Resp_img = $stmt_image->execute();
            $stmt_image->close();
        }

        $stmt = $this->con->prepare("UPDATE `task` set `status`=? where id=?");
        $stmt->bind_param("si", $status, $task_id);
        $result = $stmt->execute();
        $stmt->close();


        return $result && $Resp_img;
        // return $result;
    }
    public function task_remark_list($task_id)
    {
        $stmt = $this->con->prepare("SELECT `case_hist`.*,st.stage from `task` inner join `case_hist` on task.id = case_hist.task_id  join stage as st on st.id = `case_hist`.`stage`WHERE task.id = ? ORDER BY task.id DESC");
        $stmt->bind_param("s", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function intern_case_history($intern_id) // added by jay 22-01-2025
    {
        $stmt = $this->con->prepare("SELECT `case`.`id`,`case`.`case_no`, DATE_FORMAT(`case`.sr_date, '%d-%m-%Y') AS summon_date, company.name AS company_name, case_type.case_type AS case_type_name, court.name AS court_name, city.name AS city_name,`case`.`status` FROM  `case` INNER JOIN `company` ON `case`.company_id = company.id INNER JOIN `case_type` ON `case`.case_type = case_type.id INNER JOIN `court` ON court.id = `case`.court_name INNER JOIN `city` ON city.id = `case`.city_id WHERE `case`.id IN (SELECT DISTINCT case_id FROM `task` WHERE alloted_to = ?) ORDER BY `case`.id DESC;");
        $stmt->bind_param("i", $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function case_history_view($case_id)
    {
        $stmt = $this->con->prepare("SELECT `case_hist`.*, DATE_FORMAT(`case_hist`.dos, '%d-%m-%Y') AS fdos, DATE_FORMAT(`case_hist`.date_time, '%d-%m-%Y') AS fdt, interns.name AS intern_name, stage.stage AS stage_name, `case`.case_no, advocate.name AS advocate_name FROM `case_hist` INNER JOIN `task` ON task.id = `case_hist`.task_id INNER JOIN `case` ON `case`.id = task.case_id INNER JOIN `stage` ON `case_hist`.stage = stage.id INNER JOIN `interns` ON task.alloted_to = interns.id INNER JOIN advocate ON advocate.id = task.alloted_by WHERE task.case_id=? ORDER BY `case_hist`.id DESC");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function case_history_documents($case_id)
    {
        $stmt = $this->con->prepare("SELECT c1.case_no,c2.case_type,c1.docs,c1.id as file_id,'main' as file_type,c1.sr_date as date_time,'admin' as handled_by,'admin' as user_type from `case` c1,case_type c2 WHERE c1.case_type=c2.id  and  c1.id=? and docs!='' union SELECT c1.case_no,c2.case_type,m.docs,m.id as file_id ,'sub' as file_type,m.date_time,m.added_by as handled_by,m.user_type from `case` c1,case_type c2,multiple_doc m WHERE c1.case_type=c2.id and   m.c_id=c1.id and c1.id=?");
        $stmt->bind_param("ii", $case_id, $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function task_reassign($task_id, $intern_id, $reassign_id, $remark, $remark_date)
    {
        $stmt = $this->con->prepare("SELECT * from task where id=?");
        $stmt->bind_param('i', $task_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $this->con->prepare("UPDATE task set `status`='re_alloted',`reassign_status` = 're_alloted' where id=?");
        $stmt->bind_param('i', $task_id);
        $result1 = $stmt->execute();
        $stmt->close();

        $case_id = $data["case_id"];
        $alloted_to = $reassign_id;
        $instruction = $data["instruction"];
        $alloted_by = $intern_id;
        $action_by = "intern";
        $alloted_date = $remark_date;
        $expected_end_date = $data["expected_end_date"];
        $status = "reassign";
        $reassign_status = $data["reassign_status"];
        $old_remark = $data["remark"];

        $stmt = $this->con->prepare("INSERT INTO `task`(`case_id`, `alloted_to`, `instruction`, `alloted_by`, `action_by`, `alloted_date`, `expected_end_date`, `status`, `reassign_status`, `remark`) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iisissssss', $case_id, $alloted_to, $instruction, $alloted_by, $action_by, $alloted_date, $expected_end_date, $status, $reassign_status, $old_remark);
        $result2 = $stmt->execute();
        $task_id = mysqli_insert_id($this->con);
        $stmt->close();

        $stmt = $this->con->prepare("SELECT * from `case` where id = ?");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $stage = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stage_id = $stage["stage"];
        $status = 'enable';

        $stmt = $this->con->prepare("INSERT INTO `case_hist`(`task_id`, `stage`, `remarks`, `dos`, `status`) VALUES (?,?,?,?,?)");
        $stmt->bind_param('iisss', $task_id, $stage_id, $remark, $remark_date, $status);
        $result2 = $stmt->execute();
        $stmt->close();

        return $result1 && $result2;

    }
    public function get_case_remarks($case_id) // added by jay 22-01-2025
    {
        $stmt = $this->con->prepare("SELECT c1.id,c1.case_no,case_hist.remarks,case_hist.status, date_format(`case_hist`.dos,'%d-%m-%Y') as fdos , date_format(`case_hist`.date_time,'%d-%m-%Y') as fdt , interns.name as intern_name ,stage.stage as stage_name , c1.case_no,advocate.name as advocate_name from `case_hist` inner join `task` on task.id = case_hist.task_id inner join `case` c1 on c1.id = task.case_id inner join `stage` on case_hist.stage = stage.id inner join `interns` on task.alloted_to = interns.id inner join advocate on advocate.id = task.alloted_by where task.case_id = ? order by case_hist.id DESC");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_interns_list()
    {
        $stmt = $this->con->prepare("SELECT id,name,contact,date_time , email FROM `interns` where `status` = 'enable'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function get_advocate_list()
    {
        $stmt = $this->con->prepare("select id,name , contact , email from advocate where status = 'enable'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function edit_task($task_id, $case_id, $alloted_to, $instructions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark)
    {

        $stmt = $this->con->prepare("UPDATE `task` SET `case_id`=?,`alloted_to`=?,`instruction`=?,`alloted_by`=?,`alloted_date`=?,`expected_end_date`=?,`status`=?,`remark`=? WHERE `id`=?");
        $stmt->bind_param('iisissssi', $case_id, $alloted_to, $instructions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark, $task_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }

    public function task_info($task_id)
    {
        $stmt = $this->con->prepare("SELECT ch.id,s.stage,ch.remarks as stage,ch.date_time as remark_date , ch.nextdate , ch.status FROM `case_hist` as ch join task as t on t.id = ch.task_id join stage as s on s.id = ch.stage where t.id = ?;");
        $stmt->bind_param("s", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

}
?>