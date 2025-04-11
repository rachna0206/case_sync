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
        $stmt_login = $this->con->prepare("SELECT * FROM `staff` WHERE `email`=? AND BINARY `password`=? AND `status`='enable' AND `type`='admin'");
        $stmt_login->bind_param("ss", $user_id, $password);
        $stmt_login->execute();
        $result = $stmt_login->get_result();
        $stmt_login->close();
        return $result;
    }

    public function get_case_remarks($case_id) // modified for new database
    {
        $stmt = $this->con->prepare("SELECT 
        c1.id, 
        c1.case_no, 
        case_hist.remarks, 
        case_hist.status,  
        DATE_FORMAT(case_hist.dos, '%d-%m-%Y') AS fdos,  
        DATE_FORMAT(case_hist.date_time, '%d-%m-%Y') AS fdt,  
        intern.name AS intern_name,  
        stage.stage AS stage_name,  
        c1.case_no,  
        advocate.name AS advocate_name  
    FROM `case_hist`  
    INNER JOIN `task` ON task.id = case_hist.task_id  
    INNER JOIN `case` c1 ON c1.id = task.case_id  
    INNER JOIN `stage` ON case_hist.stage = stage.id  
    INNER JOIN `staff` AS intern ON task.alloted_to = intern.id  
    INNER JOIN `staff` AS advocate ON advocate.id = task.alloted_by 
    WHERE task.case_id = ?  
    ORDER BY case_hist.id DESC");

        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function addNewAdvocate($name, $contact, $email, $password)
    {
        // Check if the contact already exists
        $stmt = $this->con->prepare("SELECT contact FROM staff WHERE contact = ? AND type = 'admin'");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 2; // Contact already exists
        }

        // Check if the email already exists
        $stmt = $this->con->prepare("SELECT email FROM staff WHERE email = ? AND type = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 3; // Email already exists
        }

        $status = "enable";
        $type = "admin"; // Set advocate type

        // Insert new advocate into staff table
        $stmt = $this->con->prepare("INSERT INTO `staff`(`name`, `contact`, `email`, `status`, `password`, `type`) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $contact, $email, $status, $password, $type);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    public function get_case_counter()
    {
        $stmt = $this->con->prepare("SELECT 
        a.id, 
        a.case_no, 
        a.year, 
        a.sr_date, 
        b.name AS court_name, 
        a.applicant, 
        a.opp_name, 
        c.case_type, 
        d.name AS city_name, 
        ad.name AS advocate_name, 
        a.complainant_advocate, 
        a.respondent_advocate, 
        a.date_of_filing, 
        a.next_date, 
        45-DATEDIFF(CURRENT_DATE , a.sr_date) AS case_counter 
    FROM `case` AS a 
    JOIN `court` AS b ON a.court_name = b.id 
    JOIN `case_type` AS c ON a.case_type = c.id 
    JOIN `city` AS d ON a.city_id = d.id 
    JOIN `staff` AS ad ON ad.id = a.handle_by AND ad.type = 'admin' 
    WHERE DATEDIFF(DATE_ADD(a.sr_date, INTERVAL 45 DAY), CURRENT_DATE) 
    ORDER BY a.id DESC;");

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

    public function get_todays_case()
    {
        $stmt = $this->con->prepare("SELECT
        a.id AS case_id,
        a.case_no,
        a.applicant,
        a.opp_name,
        a.sr_date,
        b.name AS court_name,
        c.case_type,
        d.name AS city_name,
        e.name AS handle_by,
        a.complainant_advocate,
        a.respondent_advocate,
        a.date_of_filing,
        cp.next_date,
        45-DATEDIFF(CURRENT_DATE, a.sr_date) AS case_counter
    FROM `case` AS a
    LEFT JOIN `court` AS b ON a.court_name = b.id
    LEFT JOIN `case_type` AS c ON a.case_type = c.id
    LEFT JOIN `city` AS d ON a.city_id = d.id
    LEFT JOIN `staff` AS e ON a.handle_by = e.id AND e.type = 'admin'
    LEFT JOIN `case_procedings` AS cp
        ON a.id = cp.case_id
        AND cp.next_date = CURRENT_DATE
    WHERE cp.date_of_creation = (
        SELECT MAX(cp2.date_of_creation)
        FROM `case_procedings` AS cp2
        WHERE cp2.case_id = a.id
        AND cp2.next_date = CURRENT_DATE
    )
    ORDER BY a.id DESC;");

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }


    public function notifications()
    {
        // Fetch notifications
        $stmt = $this->con->prepare("SELECT n1.*, s1.name FROM `notification` n1 JOIN `staff` s1 ON n1.sender_id = s1.id WHERE n1.status = '1' AND s1.type = 'admin' ORDER BY n1.id DESC");
        $stmt->execute();
        $notification = $stmt->get_result();
        $stmt->close();

        // Count unassigned cases
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case` a WHERE a.id NOT IN (SELECT DISTINCT(case_id) FROM task)");
        $stmt->execute();
        $unassigned_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count assigned cases
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case` a WHERE a.id IN (SELECT DISTINCT(case_id) FROM task)");
        $stmt->execute();
        $assigned_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count total cases
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case`");
        $stmt->execute();
        $history_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count advocates
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM staff WHERE type = 'admin'");
        $stmt->execute();
        $advocate_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count interns
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM staff WHERE type = 'intern'");
        $stmt->execute();
        $intern_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count companies
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM company");
        $stmt->execute();
        $company_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count total tasks
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM task t JOIN `case` c ON c.id = t.case_id");
        $stmt->execute();
        $task_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count today's cases
        $stmt = $this->con->prepare("SELECT COUNT(*) AS count 
        FROM `case` AS a 
        LEFT JOIN `case_procedings` AS cp 
            ON a.id = cp.case_id 
            AND cp.next_date = CURRENT_DATE 
        WHERE cp.date_of_creation = (
            SELECT MAX(cp2.date_of_creation) 
            FROM `case_procedings` AS cp2 
            WHERE cp2.case_id = a.id 
            AND cp2.next_date = CURRENT_DATE
        )");
        $stmt->execute();
        $todays_case_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count case counters
        $stmt = $this->con->prepare("SELECT COUNT(*) as count 
        FROM `case` a 
        JOIN `court` b ON a.court_name = b.id 
        JOIN `case_type` c ON a.case_type = c.id 
        JOIN `city` d ON a.city_id = d.id 
        JOIN `staff` e ON a.handle_by = e.id AND e.type = 'admin' 
        WHERE DATEDIFF(DATE_ADD(a.sr_date, INTERVAL 45 DAY), CURRENT_DATE)");
        $stmt->execute();
        $counters_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Count new cases created today
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case` WHERE date_of_creation = CURRENT_DATE()");
        $stmt->execute();
        $new_case_counter = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        return [$notification, $unassigned_count, $assigned_count, $history_count, $advocate_count, $intern_count, $company_count, $task_count, $todays_case_count, $counters_count, $new_case_counter];
    }



    public function addNewIntern($name, $contact, $email, $password, $start_date)
    {
        // Check if the contact already exists
        $stmt = $this->con->prepare("SELECT contact FROM staff WHERE contact = ? AND type = 'intern'");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 2; // Contact already exists
        }

        // Check if the email already exists
        $stmt = $this->con->prepare("SELECT email FROM staff WHERE email = ? AND type = 'intern'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 3; // Email already exists
        }

        $status = "enable";
        $type = "intern"; // Set intern type

        // Insert new intern into staff table
        $stmt = $this->con->prepare("INSERT INTO `staff`(`name`, `contact`, `email`, `status`, `password`, `date/time`, `type`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $contact, $email, $status, $password, $start_date, $type);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function get_case_type_list()
    {
        $stmt = $this->con->prepare("SELECT `id`,`case_type` FROM `case_type` where `status`='enable' order by id desc");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_city_list()
    {
        $stmt = $this->con->prepare("SELECT `id`,`name` FROM `city` where `status`='enable' order by id desc");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function add_case($case_no, $year, $company_id, $docs, $opp_name, $court_name, $city_id, $sr_date, $case_type, $handle_by, $applicant, $stage, $multiple_images, $added_by, $complainant_advocate, $respondent_advocate, $date_of_filing, $next_date, $remarks)
    {
        $status = "pending";

        $stmt = $this->con->prepare("INSERT INTO `case` (`case_no`, `year`, `case_type`, `stage`, `company_id`, `handle_by`, `docs`, `applicant`, `opp_name`, `court_name`, `city_id`, `sr_date`, `status`, `complainant_advocate`, `respondent_advocate`, `date_of_filing`, `next_date`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("siiiiisssiissssss", $case_no, $year, $case_type, $stage, $company_id, $handle_by, $docs, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status, $complainant_advocate, $respondent_advocate, $date_of_filing, $next_date);
        $result = $stmt->execute();
        $stmt->close();

        $id = mysqli_insert_id($this->con);

        $stmt = $this->con->prepare("INSERT INTO `case_procedings`(`case_id`, `next_stage`, `next_date`, `remarks`, `inserted_by`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("iissi", $id, $stage, $next_date, $remarks, $added_by);
        $result = $stmt->execute();
        $stmt->close();

        if ($multiple_images != null) {
            for ($i = 0; $i < sizeof($multiple_images); $i++) {
                $stmt = $this->con->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`, `added_by`) VALUES (?,?,?)");
                $stmt->bind_param("iss", $id, $multiple_images[$i], $added_by);
                $result = $stmt->execute();
                $stmt->close();
            }
        }
        return $result;
    }


    // Added by Jay 11-04-2025
    public function edit_case($case_id, $case_no, $year, $company_id, $opponent, $court_id, $city_id, $sr_date, $case_type, $handle_by, $applicant, $stage, $complainant_advocate, $respondent_advocate, $date_of_filing)
    {


        $stmt = $this->con->prepare("update `case` set case_no=?, `year` = ? , case_type=? , court_name=?, city_id=?, sr_date=?, handle_by=? , applicant=?, opp_name=? , stage=?, complainant_advocate = ?, respondent_advocate=?, date_of_filing=?, company_id=? where id=?");
        $stmt->bind_param("siiiisississsii", $case_no, $year, $case_type, $court_id, $city_id, $sr_date, $handle_by, $applicant, $opponent, $stage, $complainant_advocate, $respondent_advocate, $date_of_filing, $company_id, $case_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    public function edit_documents($file_type, $file_id, $added_by, $document)
    {
        if ($file_type == 'main') {
            $stmt = $this->con->prepare("UPDATE `case` SET docs = ? WHERE id = ?");
            $stmt->bind_param("si", $document, $file_id);
            $result = $stmt->execute();
            $stmt->close();
            $qr = "SELECT * FROM `case` WHERE id = ?";
        } else if ($file_type == 'sub') {
            $stmt = $this->con->prepare("UPDATE multiple_doc SET docs = ?, added_by = ? WHERE id = ?");
            $stmt->bind_param("ssi", $document, $added_by, $file_id);
            $result = $stmt->execute();
            $stmt->close();
            $qr = "SELECT * FROM `multiple_doc` WHERE id = ?";
        }

        if ($result) {
            $stmt = $this->con->prepare($qr);
            $stmt->bind_param('i', $file_id);
            $stmt->execute(); // Added missing execution step
            $doc = $stmt->get_result()->fetch_assoc()["docs"];
            $stmt->close();
            return $doc;
        }

        return null;
    }
    public function delete_documents($file_type, $file_id)
    {
        if ($file_type == 'main') {
            $qr = "SELECT * from `case` where id = ?";
        } else if ($file_type == 'sub') {
            $qr = "SELECT * from `multiple_doc` where id = ?";
        }
        $stmt = $this->con->prepare($qr);
        $stmt->bind_param('i', $file_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc()["docs"];
        $stmt->close();
        return $result;
    }
    public function stage_list($case_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `stage` WHERE status = 'enable' AND `case_type_id` = (select case_type from `case` where id = ?) ;");
        $stmt->bind_param("i", $case_id);
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
            // Handle invalid date input
            $formatted_date = null;
        }

        // Insert into `case_procedings` with `inserted_by` as the user ID
        $stmt = $this->con->prepare("INSERT INTO `case_procedings` (`case_id`, `next_stage`, `next_date`, `remarks`, `inserted_by`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $case_id, $next_stage, $formatted_date, $remark, $inserted_by);
        $result = $stmt->execute();
        $stmt->close();

        //update case master

        $stmt_case = $this->con->prepare("update `case` set stage=? where id=?");
        $stmt_case->bind_param("ii", $next_stage, $case_id);
        $stmt_case->execute();
        $stmt_case->close();


        return $result;
    }

    public function proceed_case_edit($case_id, $next_stage, $input_date, $remark, $inserted_by, $proceed_id)
    {
        // Create a DateTime object from the input date
        $date_obj = DateTime::createFromFormat('Y-m-d', $input_date);

        if (!$date_obj) {
            // Try to parse other common date formats if initial format fails
            $date_formats = ['d/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d', 'Y-m-d'];
            foreach ($date_formats as $format) {
                $date_obj = DateTime::createFromFormat($format, $input_date);
                if ($date_obj) {
                    break;
                }
            }
        }

        if ($date_obj) {
            // Format the date as Y-m-d for database insertion
            $formatted_date = $date_obj->format('Y-m-d');
        } else {
            // Handle invalid date input
            $formatted_date = null;
        }

        // Update `case_procedings` with `inserted_by` as the user ID
        $stmt = $this->con->prepare("UPDATE `case_procedings` SET `case_id`=?, `next_stage`=?, `next_date`=?, `remarks`=?, `inserted_by`=? WHERE id=?");
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

    public function get_case_history()
    {
        $stmt = $this->con->prepare("SELECT 
        a.id, 
        a.case_no, 
        a.applicant, 
        a.opp_name, 
        a.sr_date, 
        a.court_name, 
        b.name AS court_name, 
        c.case_type, 
        d.name AS city_name, 
        e.name AS handle_by, 
        a.complainant_advocate, 
        a.respondent_advocate, 
        a.date_of_filing, 
        a.next_date, 
        45-DATEDIFF(CURRENT_DATE, a.sr_date) AS case_counter 
    FROM `case` AS a 
    JOIN `court` AS b ON a.court_name = b.id 
    JOIN `case_type` AS c ON a.case_type = c.id 
    JOIN `city` AS d ON a.city_id = d.id 
    JOIN `staff` AS e ON a.handle_by = e.id AND e.type = 'admin' 
    ORDER BY a.id DESC;");

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function get_advocate_list()
    {
        $stmt = $this->con->prepare("SELECT * FROM staff WHERE type = 'admin' ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function delete_task($task_id)
    {
        $stmt = $this->con->prepare("DELETE from `task` where id = ?");
        $stmt->bind_param('i', $task_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $stmt = $this->con->prepare("DELETE from `case_hist` where task_id = ?");
            $stmt->bind_param('i', $task_id);
            $result2 = $stmt->execute();
            $stmt->close();
        }

        return $result2;
    }
    public function delete_intern($intern_id)
    {
        // Check if the intern is assigned to any task
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `task` WHERE alloted_to = ?");
        $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result["count"] > 0) {
            return false; // Cannot delete intern if assigned to a task
        }

        // Delete the intern from staff table
        $stmt = $this->con->prepare("DELETE FROM `staff` WHERE id = ? AND type = 'intern'");
        $stmt->bind_param('i', $intern_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
    public function delete_advocate($advocate_id)
    {
        // Check if the advocate is assigned to any task
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `task` WHERE alloted_by = ?");
        $stmt->bind_param('i', $advocate_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result['count'] > 0) {
            return false; // Cannot delete advocate if assigned to a task
        }

        // Delete the advocate from staff table
        $stmt = $this->con->prepare("DELETE FROM `staff` WHERE id = ? AND type = 'admin'");
        $stmt->bind_param('i', $advocate_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
    public function delete_company($company_id)
    {
        $stmt = $this->con->prepare("SELECT count(*) as count from `case` where company_id  = ?");
        $stmt->bind_param('i', $company_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result['count'] > 0) {
            return false;
        }

        $stmt = $this->con->prepare("DELETE from `company` where id = ?");
        $stmt->bind_param('i', $company_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_case_info($case_id)
    {
        $stmt = $this->con->prepare("SELECT
        c.id,
        c.case_no,
        c.year,
        t.case_type,
        st.stage AS stage_name,
        cmp.name AS company_name,
        ad.name AS advocate_name,
        c.docs,
        c.applicant,
        c.opp_name,
        crt.name AS court_name,
        ct.name AS city_name,
        st2.stage AS next_stage,
        c.sr_date,
        c.complainant_advocate,
        c.respondent_advocate,
        c.date_of_filing,
        45-DATEDIFF(CURRENT_DATE, c.sr_date) AS case_counter,
        cps.stage,
        cp.next_stage,
        cp.next_date,
        cp.remarks,
        cp.inserted_by,
        cp.date_of_creation
    FROM
        `case` AS c
    JOIN case_type AS t ON t.id = c.case_type
    JOIN stage AS st ON st.id = c.stage
    LEFT JOIN stage AS st2 ON st2.id = c.next_stage
    JOIN company AS cmp ON cmp.id = c.company_id
    JOIN staff AS ad ON ad.id = c.handle_by AND ad.type = 'admin'
    JOIN court AS crt ON crt.id = c.court_name
    JOIN city AS ct ON ct.id = c.city_id
    LEFT JOIN (
        SELECT
            case_id,
            MAX(id) AS max_id
        FROM
            case_procedings
        GROUP BY
            case_id
    ) AS max_cp ON max_cp.case_id = c.id
    LEFT JOIN case_procedings AS cp ON cp.id = max_cp.max_id
    LEFT JOIN stage AS cps ON cps.id = cp.next_stage
    WHERE
        c.id = ?;");

        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function add_task($case_id, $alloted_to, $instrctions, $alloted_by, $alloted_date, $expected_end_date, $remark)
    {
        $status = "allotted";
        $stmt = $this->con->prepare("INSERT INTO `task`(`case_id`, `alloted_to`, `instruction`, `alloted_by`, `alloted_date`, `expected_end_date`, `status`, `remark`) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iissssss', $case_id, $alloted_to, $instrctions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_unassigned_case_list()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,a.handle_by,a.complainant_advocate,a.respondent_advocate,a.date_of_filing,a.next_date, 45-DATEDIFF(CURRENT_DATE , a.sr_date) as case_counter from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id where a.id  not in (select DISTINCT(case_id) from task) order by a.id desc;");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function edit_intern($intern_id, $name, $contact, $email, $status, $password) // updated for new database
    {

        $stmt = $this->con->prepare("UPDATE `staff` SET `name`=?, `contact`=?, `email`=?, `status`=?, `password`=? WHERE `id`=? AND `type`='intern'");
        $stmt->bind_param('sssssi', $name, $contact, $email, $status, $password, $intern_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function edit_advocate($advocate_id, $name, $contact, $email, $status, $password) // updated for new database
    {
        $stmt = $this->con->prepare("UPDATE `staff` SET `name`=?, `contact`=?, `email`=?, `status`=?, `password`=? WHERE `id`=? AND `type`='admin'");
        $stmt->bind_param('sssssi', $name, $contact, $email, $status, $password, $advocate_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function edit_company($company_id, $name, $contact_person, $contact_no, $status)
    {
        $stmt = $this->con->prepare("UPDATE `company` set `name`=?,`contact_person`=?,`contact_no`=?,`status`=? where `id`=?");
        $stmt->bind_param('ssssi', $name, $contact_person, $contact_no, $status, $company_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_case_documents($case_no)
    {
        $stmt = $this->con->prepare("
        SELECT 
            c1.case_no, 
            c2.case_type, 
            c1.docs, 
            c1.id AS file_id, 
            'main' AS file_type, 
            c1.sr_date AS date_time, 
            'admin' AS handled_by 
        FROM `case` c1 
        JOIN case_type c2 ON c1.case_type = c2.id 
        WHERE c1.id = ? AND c1.docs != '' 
        
        UNION 
        
        SELECT 
            c1.case_no, 
            c2.case_type, 
            m.docs, 
            m.id AS file_id, 
            'sub' AS file_type, 
            m.date_time, 
            s.name AS handled_by 
        FROM `case` c1 
        JOIN case_type c2 ON c1.case_type = c2.id 
        JOIN multiple_doc m ON m.c_id = c1.id 
        LEFT JOIN staff s ON m.added_by = s.id 
        WHERE c1.id = ?;
    ");

        $stmt->bind_param('ii', $case_no, $case_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }
    public function add_task_remark($task_id, $remark, $remark_date, $stage_id, $ImageFileName1, $case_id, $intern_id, $status)
    {
        // Insert into case_hist
        $stmt = $this->con->prepare("INSERT INTO case_hist(`task_id`, `stage`, `remarks`, `dos`, `status`,`added_by`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issssi", $task_id, $stage_id, $remark, $remark_date, $status, $intern_id);
        $result = $stmt->execute();
        $stmt->close();

        $Resp_img = true;
        if ($ImageFileName1 != "") {
            // Insert image into multiple_doc (removed user_type)
            $stmt_image = $this->con->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`, `added_by`) VALUES (?, ?, ?)");
            $stmt_image->bind_param("isi", $case_id, $ImageFileName1, $intern_id);
            $Resp_img = $stmt_image->execute();
            $stmt_image->close();
        }

        // Update task status
        $stmt = $this->con->prepare("UPDATE `task` SET `status`=? WHERE id=?");
        $stmt->bind_param("si", $status, $task_id);
        $result = $stmt->execute();
        $stmt->close();

        return $result && $Resp_img;
    }
    public function get_task_info($task_id)
    {
        $stmt = $this->con->prepare("SELECT t.*, c.case_no, at_staff.name AS alloted_to_name, ab_staff.name AS alloted_by_name , at_staff.type as action_by, c.stage, st.stage as stage_name FROM `task` AS t JOIN `case` AS c ON t.case_id = c.id JOIN `staff` AS at_staff ON at_staff.id = t.alloted_to JOIN `staff` AS ab_staff ON ab_staff.id = t.alloted_by JOIN `stage` AS st on st.id = c.stage WHERE t.id = ?;");
        $stmt->bind_param('i', $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function task_reassign($task_id, $intern_id, $reassign_id, $remark, $remark_date)
    {
        // Fetch existing task details
        $stmt = $this->con->prepare("SELECT * FROM task WHERE id=?");
        $stmt->bind_param('i', $task_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Update original task status to re-alloted
        $stmt = $this->con->prepare("UPDATE task SET `status`='re_alloted', `reassign_status`='re_alloted' WHERE id=?");
        $stmt->bind_param('i', $task_id);
        $result1 = $stmt->execute();
        $stmt->close();

        // Extract task details for reassignment
        $case_id = $data["case_id"];
        $alloted_to = $reassign_id;
        $instruction = $data["instruction"];
        $alloted_by = $intern_id;  // Intern ID now comes from `staff`
        $alloted_date = $remark_date;
        $expected_end_date = $data["expected_end_date"];
        $status = "reassign";
        $reassign_status = $data["reassign_status"];
        $old_remark = $data["remark"];

        // Insert new reassigned task (Removed action_by column)
        $stmt = $this->con->prepare("INSERT INTO `task`(`case_id`, `alloted_to`, `instruction`, `alloted_by`, `alloted_date`, `expected_end_date`, `status`, `reassign_status`, `remark`) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iisisssss', $case_id, $alloted_to, $instruction, $alloted_by, $alloted_date, $expected_end_date, $status, $reassign_status, $old_remark);
        $result2 = $stmt->execute();
        $new_task_id = mysqli_insert_id($this->con);
        $stmt->close();

        // Fetch case stage
        $stmt = $this->con->prepare("SELECT stage FROM `case` WHERE id = ?");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $stage = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stage_id = $stage["stage"];
        $status = 'enable';

        // Insert into case history
        $stmt = $this->con->prepare("INSERT INTO `case_hist`(`task_id`, `stage`, `remarks`, `dos`, `status`) VALUES (?,?,?,?,?)");
        $stmt->bind_param('iisss', $new_task_id, $stage_id, $remark, $remark_date, $status);
        $result3 = $stmt->execute();
        $stmt->close();

        return $result1 && $result2 && $result3;
    }
    public function get_case_task($case_no)
    {
        $stmt = $this->con->prepare("
        SELECT 
            t.id,
            t.case_id,
            t.alloted_to AS alloted_to_id,
            t.instruction,
            t.alloted_by AS alloted_by_id,
            t.alloted_date,
            t.expected_end_date,
            t.status,
            t.reassign_status,
            t.remark,
            c.case_no AS case_num,
            at_staff.name AS alloted_to,
            ab_staff.name AS alloted_by
        FROM task AS t
        JOIN `case` AS c ON c.id = t.case_id
        JOIN `staff` AS at_staff ON at_staff.id = t.alloted_to
        JOIN `staff` AS ab_staff ON ab_staff.id = t.alloted_by
        WHERE t.case_id = ?
        ORDER BY t.id DESC
    ");

        $stmt->bind_param("s", $case_no);
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

    public function stage_court_list($case_id)
    {
        //getting list of stage based on the case type id
        $stmt = $this->con->prepare("SELECT * from stage where case_type_id = ? and status = 'enable' order by id desc");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $result1 = $stmt->get_result();
        $stmt->close();

        //getting list of court based on the case type id
        $stmt = $this->con->prepare("SELECT * from court where case_type = ? and status = 'enable' order by id desc");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();

        //returning both the query response in an array
        return [$result1, $result2];
    }

    public function get_task_history($task_no)
    {
        $stmt = $this->con->prepare("SELECT c1.id, c1.case_no, case_hist.remarks, case_hist.status, case_hist.dos AS fdos, case_hist.date_time AS fdt,  stage.stage AS stage_name, advocate.name AS added_by FROM `case_hist` INNER JOIN `task` ON task.id = case_hist.task_id INNER JOIN `case` c1 ON c1.id = task.case_id INNER JOIN `stage` ON case_hist.stage = stage.id  INNER JOIN `staff` AS advocate ON advocate.id = case_hist.added_by WHERE task.id = ? ORDER BY case_hist.id DESC");
        $stmt->bind_param("s", $task_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_assigned_case_list()
    {
        $stmt = $this->con->prepare("
        SELECT 
            a.id, 
            a.case_no, 
            a.year, 
            a.sr_date, 
            b.name AS court_name, 
            a.applicant, 
            a.opp_name, 
            c.case_type, 
            d.name AS city_name, 
            ad.name AS advocate_name, 
            a.complainant_advocate, 
            a.respondent_advocate, 
            a.date_of_filing, 
            a.next_date, 
            45-DATEDIFF(CURRENT_DATE, a.sr_date) AS case_counter 
        FROM `case` AS a 
        JOIN `court` AS b ON a.court_name = b.id 
        JOIN `case_type` AS c ON a.case_type = c.id 
        JOIN `city` AS d ON a.city_id = d.id 
        JOIN `staff` AS ad ON ad.id = a.handle_by AND ad.type = 'admin' 
        WHERE a.id IN (SELECT DISTINCT(case_id) FROM task) 
        ORDER BY a.id DESC;
    ");

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_interns_list()
    {
        $stmt = $this->con->prepare("SELECT id, name, contact, DATE_FORMAT(`date/time`, '%Y-%m-%d') AS date_time, email ,password,status FROM `staff` WHERE `type` = 'intern' ORDER BY id DESC;");

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
        $stmt = $this->con->prepare("SELECT * from `company` order by id desc");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function proceed_history($case_id)
    {
        $stmt = $this->con->prepare("SELECT cp.*,s.stage,st.name as inserted_by_name from case_procedings as cp join stage as s on s.id = cp.next_stage join staff as st on st.id = cp.inserted_by where cp.case_id = ? order by cp.id desc");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function upcoming_cases($date)
    {
        $result = [];
        $dateObj = new DateTime($date);

        for ($i = 0; $i < 3; $i++) {
            $stmt = $this->con->prepare("SELECT  
    CONVERT(a.id,CHAR) AS case_id, 
    a.case_no, 
    a.applicant, 
    a.opp_name, 
    a.sr_date,  
    b.name AS court_name, 
    c.case_type, 
    d.name AS city_name, 
    e.name AS handle_by,  
    a.complainant_advocate, 
    a.respondent_advocate, 
    a.date_of_filing, 
    cp.next_date, 
    ns.stage AS next_stage_name,  
    CONVERT(45-DATEDIFF(CURRENT_DATE, a.sr_date),char) AS case_counter, 
    ts.sequence, 
    ts.remark,
    CONVERT(ts.id,char) AS sequence_id 
FROM `case` AS a  
LEFT JOIN `court` AS b ON a.court_name = b.id  
LEFT JOIN `case_type` AS c ON a.case_type = c.id  
LEFT JOIN `city` AS d ON a.city_id = d.id  
LEFT JOIN `staff` AS e ON a.handle_by = e.id AND e.type = 'admin'  
LEFT JOIN `temp_sequence` AS ts ON a.id = ts.case_id 
LEFT JOIN `case_procedings` AS cp 
    ON a.id = cp.case_id 
    AND cp.date_of_creation = (
        SELECT MAX(cp2.date_of_creation) 
        FROM `case_procedings` AS cp2 
        WHERE cp2.case_id = a.id
        AND cp2.next_date = ?
    ) 
LEFT JOIN `stage` AS ns ON cp.next_stage = ns.id 
WHERE cp.next_date = ? 
ORDER BY ts.sequence ASC, a.id DESC;
        ");

            $formattedDate = $dateObj->format('Y-m-d');
            $stmt->bind_param('ss', $formattedDate, $formattedDate);
            $stmt->execute();
            $temp = $stmt->get_result();
            $stmt->close();

            $result[] = $temp;

            $dateObj->modify('+1 day');
        }

        return $result;
    }

    public function add_sequence($case_id, $sequence, $added_by, $remark)
    {

        $stmt = $this->con->prepare("INSERT INTO `temp_sequence`(`case_id`, `sequence`, `added_by`,`remark`) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis", $case_id, $sequence, $added_by, $remark);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function edit_sequence($id, $case_id, $sequence, $added_by, $remark)
    {
        $stmt = $this->con->prepare("UPDATE `temp_sequence` SET `case_id`=?,`sequence`=?,`added_by`=?,`remark`=? WHERE `id`=?");
        $stmt->bind_param("iissi", $case_id, $sequence, $added_by, $remark, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function delete_sequence($id)
    {
        $stmt = $this->con->prepare("DELETE from `temp_sequence` where `id` = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


}

?>