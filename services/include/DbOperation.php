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

        $stmt = $this->con->prepare("INSERT INTO `interns`(`name`, `contact`, `email`,`status`, `password`,`date_time`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $name, $contact, $email, $status, $password, $start_date);
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
    public function add_case($case_no, $year, $company_id, $docs, $opp_name, $court_name, $city_id, $sr_date, $case_type, $handle_by, $applicant)
    {
        $status = "enable";
        $stmt = $this->con->prepare("INSERT into `case` (`case_no`,`year`,`case_type`,`company_id`,`handle_by`,`docs`,`applicant`,`opp_name`,`court_name`,`city_id`,`sr_date`,`status`) values (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssiiisssiiss", $case_no, $year, $case_type, $company_id, $handle_by, $docs, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_case_list()
    {
        // $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name, e.name from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id join `interns` as e on a.handle_by = e.id where `status` = 'enable';");
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,a.handle_by from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id where a.status = 'enable';");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }public function get_unassigned_case_list()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,a.handle_by from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id where a.status = 'enable' AND a.id  not in (select DISTINCT(case_id) from task);");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }public function get_assigned_case_list()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,a.handle_by from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id where a.status = 'enable' AND a.id in (select DISTINCT(case_id) from task);");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_interns_list()
    {
        $stmt = $this->con->prepare("SELECT `id` , `name` from `interns` where `status` = 'enable'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function task_assignment($case_id, $alloted_to, $alloted_by, $remark)
    {
        $status = "enable";
        $stmt = $this->con->prepare("INSERT into `task` (`case_id`,`alloted_to`,`alloted_by`,`remark`,`status`) values (?,?,?,?,?)");
        $stmt->bind_param('iiiss', $case_id, $alloted_to, $alloted_by, $remark, $status);
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
        $stmt = $this->con->prepare("SELECT * from `company`");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

}
?>