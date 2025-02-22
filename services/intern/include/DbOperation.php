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

    public function get_todays_case($intern_id)
    {
        $stmt = $this->con->prepare("SELECT a.id,a.case_no , a.applicant , a.opp_name , a.sr_date , a.court_name ,b.name as court_name,c.case_type, d.name as city_name , e.name as 'handle_by',a.complainant_advocate,a.respondent_advocate,a.date_of_filing,a.next_date,DATEDIFF(CURRENT_DATE , a.sr_date) as case_counter from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id join advocate as e on a.handle_by = e.id where a.next_date = CURRENT_DATE order by a.id desc;");
        //  $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_case_counter($intern_id)
    {

        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.applicant, a.opp_name, a.sr_date, b.name as court_name,c.case_type, d.name as city_name, e.name as handle_by,DATEDIFF(DATE_ADD(a.sr_date, INTERVAL 45 DAY),CURRENT_DATE) as case_counter FROM `case` a JOIN `court` b ON a.court_name = b.id JOIN `case_type` c ON a.case_type = c.id JOIN `city` d ON a.city_id = d.id JOIN `advocate` e ON a.handle_by = e.id WHERE DATEDIFF(DATE_ADD(a.sr_date, INTERVAL 45 DAY),CURRENT_DATE)");
        //  $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function intern_task_list($intern_id, $case_id)
    {
        $qr = "";
        if ($case_id != '') {
            $qr = "AND t.case_id = '" . $case_id . "' ";
        }
        $stmt = $this->con->prepare("SELECT t.id as task_id,c.id as case_id,c.stage as stage_id,c.case_no,t.instruction,i.name as alloted_to,a.name as alloted_by,t.alloted_date,t.expected_end_date,t.status,st.stage  from task as t join `case` as c on t.case_id = c.id join interns as i on i.id = t.alloted_to join advocate as a on a.id = t.alloted_by join stage as st on st.id = c.stage where i.id = ? " . $qr . " order by t.id desc;");
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
        $stmt = $this->con->prepare("SELECT `case_hist`.*,st.stage from `task` inner join `case_hist` on task.id = case_hist.task_id  join stage as st on st.id = `case_hist`.`stage`WHERE task.id = ? ORDER BY case_hist.id DESC");
        $stmt->bind_param("s", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function intern_case_history($intern_id) // added by jay 22-01-2025
    {
        //$stmt = $this->con->prepare("SELECT `case`.`id`,`case`.`case_no`, `case`.sr_date AS summon_date, company.name AS company_name, case_type.case_type AS case_type_name, court.name AS court_name, city.name AS city_name,`case`.`status` FROM  `case` INNER JOIN `company` ON `case`.company_id = company.id INNER JOIN `case_type` ON `case`.case_type = case_type.id INNER JOIN `court` ON court.id = `case`.court_name INNER JOIN `city` ON city.id = `case`.city_id WHERE `case`.id IN (SELECT DISTINCT case_id FROM `task` WHERE alloted_to = ?) ORDER BY `case`.id DESC;");
        // $stmt->bind_param("i", $intern_id);
        //  $stmt = $this->con->prepare("SELECT `case`.`id`,`case`.`case_no`, `case`.sr_date AS summon_date, company.name AS company_name, case_type.case_type AS case_type_name, court.name AS court_name, city.name AS city_name,`case`.`status` FROM  `case` INNER JOIN `company` ON `case`.company_id = company.id INNER JOIN `case_type` ON `case`.case_type = case_type.id INNER JOIN `court` ON court.id = `case`.court_name INNER JOIN `city` ON city.id = `case`.city_id WHERE `case`.id IN (SELECT DISTINCT case_id FROM `task` WHERE alloted_to = ?) ORDER BY `case`.id DESC;");
        $stmt = $this->con->prepare("SELECT `case`.`id`,`case`.`case_no`, `case`.sr_date AS summon_date, company.name AS company_name, case_type.case_type AS case_type_name, court.name AS court_name, city.name AS city_name, `case`.status FROM `case` INNER JOIN `company` ON `case`.company_id = company.id INNER JOIN `case_type` ON `case`.case_type = case_type.id INNER JOIN `court` ON court.id = `case`.court_name INNER JOIN `city` ON city.id = `case`.city_id ORDER BY `case`.id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function notification($intern_id)
    {
        $stmt = $this->con->prepare("SELECT n.*,i1.name , c.case_no FROM `notification` n join interns i1 join task as t on t.id = n.task_id join `case` as c on c.id = t.case_id where n.sender_id=i1.id and n.status='1' and n.receiver_type='intern' and n.receiver_id=? order by n.id desc;");
        $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case`");
        $stmt->execute();
        $case_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT count(*) as count  from task as t join `case` as c on t.case_id = c.id join interns as i on i.id = t.alloted_to join advocate as a on a.id = t.alloted_by join stage as st on st.id = c.stage where i.id = ? order by t.id desc;");
        $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $task_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case` as c where c.next_date = CURRENT_DATE;");
        $stmt->execute();
        $todays_case_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT count(*) as count FROM `case` a JOIN `court` b ON a.court_name = b.id JOIN `case_type` c ON a.case_type = c.id JOIN `city` d ON a.city_id = d.id JOIN `advocate` e ON a.handle_by = e.id WHERE DATEDIFF(DATE_ADD(a.sr_date, INTERVAL 45 DAY),CURRENT_DATE);");
        $stmt->execute();
        $counters_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();
        return [$result, $case_count, $task_count, $todays_case_count, $counters_count];
    }

    public function get_case_info($case_id)
    {
        $stmt = $this->con->prepare("SELECT
    c.id,
    c.case_no,
    c.year,
    t.case_type,
    st.stage as stage_name,
    cmp.name as company_name,
    ad.name as advocate_name,
    c.docs,
    c.applicant,
    c.opp_name,
    crt.name as court_name,
    ct.name as city_name,
    st2.stage as next_stage,
    c.sr_date,
    c.complainant_advocate,
    c.respondent_advocate,
    c.date_of_filing,
    DATEDIFF(CURRENT_DATE, c.sr_date) as case_counter,
    cps.stage,
    cp.next_stage,
    cp.next_date,
    cp.remarks,
    cp.inserted_by,
    cp.date_of_creation
from
    `case` as c
    join case_type as t on t.id = c.case_type
    join stage as st on st.id = c.stage
    left join stage as st2 on st2.id = c.next_stage
    join company as cmp on cmp.id = c.company_id
    join advocate as ad on ad.id = c.handle_by
    join court as crt on crt.id = c.court_name
    join city as ct on ct.id = c.city_id
    LEFT JOIN (
        SELECT
            case_id,
            MAX(id) as max_id
        FROM
            case_procedings
        GROUP BY
            case_id
    ) as max_cp ON max_cp.case_id = c.id
    LEFT JOIN case_procedings as cp ON cp.id = max_cp.max_id
    left join stage as cps on cps.id = cp.next_stage
where
    c.id = ?;");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function read_notification($not_id)
    {
        $stmt = $this->con->prepare("UPDATE `notification` SET `status`=0,playstatus=0 where id=?");
        $stmt->bind_param('i', $not_id);
        $result = $stmt->execute();

        $stmt->close();
        return $result;
    }

    public function case_history_view($case_id)
    {
        $stmt = $this->con->prepare("SELECT `case_hist`.*, `case_hist`.dos AS fdos, `case_hist`.date_time AS fdt, interns.name AS intern_name, stage.stage AS stage_name, `case`.case_no, advocate.name AS advocate_name FROM `case_hist` INNER JOIN `task` ON task.id = `case_hist`.task_id INNER JOIN `case` ON `case`.id = task.case_id INNER JOIN `stage` ON `case_hist`.stage = stage.id INNER JOIN `interns` ON task.alloted_to = interns.id INNER JOIN advocate ON advocate.id = task.alloted_by WHERE task.case_id=? ORDER BY `case_hist`.id DESC");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function case_history_documents($case_id)
    {
        $stmt = $this->con->prepare("SELECT c1.case_no, c2.case_type, c1.docs, c1.id AS file_id, 'main' AS file_type, c1.sr_date AS date_time, 'admin' AS handled_by, 'admin' AS user_type FROM `case` c1 JOIN case_type c2 ON c1.case_type = c2.id WHERE c1.id = ? AND docs != '' UNION SELECT c1.case_no, c2.case_type, m.docs, m.id AS file_id, 'sub' AS file_type, m.date_time, CASE WHEN m.user_type = 'intern' THEN i.name WHEN m.user_type = 'advocate' THEN a.name END AS handled_by, m.user_type FROM `case` c1 JOIN case_type c2 ON c1.case_type = c2.id JOIN multiple_doc m ON m.c_id = c1.id LEFT JOIN interns i ON m.added_by = i.id AND m.user_type = 'intern' LEFT JOIN advocate a ON m.added_by = a.id AND m.user_type = 'advocate' WHERE c1.id = ?;");
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
    public function stage_list($case_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `stage` WHERE status = 'enable' AND `case_type_id` = (select case_type from `case` where id = ?);");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function get_interns_list()
    {
        $stmt = $this->con->prepare("SELECT id,name,contact, date_format(date_time,'%Y-%m-%d') as date_time, email FROM `interns` where `status` = 'enable' order by id desc;");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function get_advocate_list()
    {
        $stmt = $this->con->prepare("SELECT id,name , contact , email from advocate where status = 'enable' order by id desc");
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
        $stmt = $this->con->prepare("SELECT ch.id,s.stage,ch.remarks as stage,ch.date_time as remark_date , ch.nextdate , ch.status FROM `case_hist` as ch join task as t on t.id = ch.task_id join stage as s on s.id = ch.stage where t.id = ? order by ch.id desc; ");
        $stmt->bind_param("s", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function proceed_case_add($case_id, $next_stage, $next_date, $remark, $inserted_by)
    {
        // Create a DateTime object from the input date
        $date_obj = DateTime::createFromFormat('Y-m-d', $next_date);

        if (!$date_obj) {
            // Try to parse other common date formats if initial format fails
            $date_formats = ['d/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d', 'Y-m-d'];
            foreach ($date_formats as $format) {
                $date_obj = DateTime::createFromFormat($format, $next_date);
                if ($date_obj) {
                    break;
                }
            }
        }

        if ($date_obj) {
            // Format the date as Y-m-d for database insertion
            $formatted_date = $date_obj->format('Y-m-d');
        } else {
            // Handle invalid date input (optional)
            $formatted_date = null; // or set to a default date value
        }

        // echo "UPDATE `case` set next_date = $next_date , stage = $next_stage where id = $case_id";
        $stmt = $this->con->prepare("INSERT into `case_procedings` (`case_id`, `next_stage`, `next_date`, `remarks`,`inserted_by`) values (?,?,?,?,?)");
        $stmt->bind_param("iisss", $case_id, $next_stage, $formatted_date, $remark, $inserted_by);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function proceed_case_edit($case_id, $next_stage, $input_date, $remark, $inserted_by, $proceed_id)
    {
        $date_obj = DateTime::createFromFormat('Y-m-d', $input_date);

        if (!$date_obj) {
            $date_formats = ['d/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d', 'Y-m-d'];
            foreach ($date_formats as $format) {
                $date_obj = DateTime::createFromFormat($format, $input_date);
                if ($date_obj) {
                    break;
                }
            }
        }

        if ($date_obj) {
            $formatted_date = $date_obj->format('Y-m-d');
        } else {
            $formatted_date = null;
        }

        $stmt = $this->con->prepare("UPDATE `case_procedings` SET `case_id`=?, `next_stage`=?, `next_date`=?, `remarks`=?,`inserted_by` = ? WHERE id = ?");
        $stmt->bind_param("iisssi", $case_id, $next_stage, $formatted_date, $remark, $inserted_by, $proceed_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function proceed_case_delete($proceed_id)
    {
        $stmt = $this->con->prepare("DELETE from `case_procedings` where `id` = ?");
        $stmt->bind_param("i", $proceed_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function proceed_history($case_id)
    {
        $stmt = $this->con->prepare("SELECT cp.*,s.stage from case_procedings as cp join stage as s on s.id = cp.next_stage where cp.case_id = ?;");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

}
?>