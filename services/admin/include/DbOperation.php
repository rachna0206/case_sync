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


    public function loginAdvocate($user_id, $password)
    {
        $stmt_login = $this->con->prepare("SELECT * FROM `advocate` WHERE `email`=? AND BINARY `password`=? AND status = 'enable'");
        $stmt_login->bind_param("ss", $user_id, $password);
        $stmt_login->execute();
        $result = $stmt_login->get_result();
        $stmt_login->close();
        return $result;
    }

    public function addNewAdvocate($name, $contact, $email, $password)
    {

        $stmt = $this->con->prepare("SELECT contact from advocate where contact = ? ");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 2;
        }


        $stmt = $this->con->prepare("SELECT email from advocate where email = ? ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 3;
        }


        $status = "enable";

        $stmt = $this->con->prepare("INSERT INTO `advocate`(`name`, `contact`, `email`, `status`, `password`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $contact, $email, $status, $password);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }
    public function addNewIntern($name, $contact, $email, $password, $start_date)
    {
        $stmt = $this->con->prepare("SELECT contact from interns where contact = ? ");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 2;
        }


        $stmt = $this->con->prepare("SELECT email from interns where email = ? ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 3;
        }


        $status = "enable";

        $stmt = $this->con->prepare("INSERT INTO `interns`(`name`, `contact`, `email`,`status`, `password`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $contact, $email, $status, $password);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }
    public function get_case_type_list()
    {
        $stmt = $this->con->prepare("SELECT `id`,`case_type` FROM `case_type` where `status`='enable'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_court_list()
    {
        $stmt = $this->con->prepare("SELECT `id`,`name` FROM `court` where `status`='enable'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_city_list()
    {
        $stmt = $this->con->prepare("SELECT `id`,`name` FROM `city` where `status`='enable'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function add_case($case_no, $year, $company_id, $docs, $opp_name, $court_name, $city_id, $sr_date, $case_type, $handle_by, $applicant, $stage, $multiple_images, $added_by, $user_type)
    {
        $status = "enable";
        $stmt = $this->con->prepare("INSERT INTO `case` (`case_no`, `year`, `case_type`, `stage`, `company_id`, `handle_by`, `docs`, `applicant`, `opp_name`, `court_name`, `city_id`, `sr_date`, `status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("siiiiisssiiss", $case_no, $year, $case_type, $stage, $company_id, $handle_by, $docs, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status);
        $result = $stmt->execute();
        $stmt->close();

        $id = mysqli_insert_id($this->con);

        if ($multiple_images != null) {
            for ($i = 0; $i < sizeof($multiple_images); $i++) {
                $stmt = $this->con->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`, `added_by`, `user_type`) VALUES (?,?,?,?)");
                $stmt->bind_param("isis", $id, $multiple_images[$i], $added_by, $user_type);
                $result = $stmt->execute();
                $stmt->close();
            }
        }
        return $result;
    }
    public function get_case_history()
    {
        $stmt = $this->con->prepare("SELECT a.id,a.case_no , a.applicant , a.opp_name , a.sr_date , a.court_name ,b.name as court_name,c.case_type, d.name as city_name , e.name as 'handle_by' from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id join advocate as e on a.handle_by = e.id;");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_advocate_list()
    {
        $stmt = $this->con->prepare("select name , contact , email from advocate where status = 'enable'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_case_info($case_id)
    {
        $stmt = $this->con->prepare("SELECT c.id,c.case_no,c.year , t.case_type , st.stage , cmp.name , ad.name , c.docs , c.applicant , c.opp_name , crt.name , ct.name , c.next_date , c.next_stage , c.sr_date   from `case` as c join case_type as t on t.id = c.case_type join stage as st on st.id = c.stage join company as cmp on cmp.id = c.company_id join advocate as ad on ad.id = c.handle_by join court as crt on crt.id = c.court_name join city as ct on ct.id = c.city_id where c.id = ?;");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_case_stage_list($case_stage)
    {
        $stmt = $this->con->prepare("SELECT * from stage where case_type_id = ? AND `status`='enable'  order by stage");
        $stmt->bind_param("s", $case_stage);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_unassigned_case_list()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,a.handle_by from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id where a.id  not in (select DISTINCT(case_id) from task);");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_case_task($case_no)
    {
        $case_id = $this->getCaseId($case_no);
        $stmt = $this->con->prepare("SELECT * from task where case_id = ?");
        $stmt->bind_param("s", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_task_history($task_no)
    {
        $stmt = $this->con->prepare("SELECT * from case_hist where task_id = ?");
        $stmt->bind_param("s", $task_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_assigned_case_list()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,a.handle_by from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id where a.id in (select DISTINCT(case_id) from task);");
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
    public function task_assignment($case_id, $alloted_to, $alloted_by, $remark, $expected_end_date, $instruction)
    {
        $status = "allocated";
        $stmt = $this->con->prepare("INSERT into `task` (`case_id`,`alloted_to`,`alloted_by`,`remark`,`status`,`expected_end_date`,`instruction`) values (?,?,?,?,?,?,?)");
        $stmt->bind_param('iiissss', $case_id, $alloted_to, $alloted_by, $remark, $status, $expected_end_date, $instruction);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function add_company($name, $contact_person, $contact_no)
    {
        $status = 'enable';
        $stmt = $this->con->prepare("INSERT into `company` (`name`,`contact_person`,`contact_no`,`status`) values (?,?,?,?)");
        $stmt->bind_param('ssss', $name, $contact_person, $contact_no, $status);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_company_list()
    {
        $stmt = $this->con->prepare("SELECT * from `company` where status = 'enable '");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function getCaseId($case_no)
    {
        // echo "select id from `case` where case_no = $case_no";
        $stmt = $this->con->prepare("select id from `case` where case_no = ?");
        $stmt->bind_param('s', $case_no);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        // echo $result["id"];
        // print_r($result);
        return $result["id"];
    }

    public function get_task_list($case_no)
    {

        $case_id = $this->getCaseId($case_no);

        $stmt = $this->con->prepare("select * from task where case_id = ?");
        $stmt->bind_param('s', $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

}
?>