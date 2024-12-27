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
        $stmt = $this->con->prepare("SELECT * from task where alloted_to = ?");
        $stmt->bind_param("s", $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }



}
?>