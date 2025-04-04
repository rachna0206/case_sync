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
        $stmt_login = $this->con->prepare("SELECT id, name, contact, DATE_FORMAT(`date/time`, '%Y-%m-%d') AS date_time, email ,password,status FROM `staff` WHERE email = ? AND password = ? AND `status` = 'enable' AND `type` = 'intern' ORDER BY id DESC");
        $stmt_login->bind_param("ss", $user_id, $password);
        $stmt_login->execute();
        $result = $stmt_login->get_result();
        $stmt_login->close();
        return $result;
    }

    public function get_todays_case($intern_id)
    {
        $stmt = $this->con->prepare("
        SELECT
            case_id,
            case_no,
            applicant,
            opp_name,
            sr_date,
            court_name,
            case_type,
            city_name,
            handle_by,
            complainant_advocate,
            respondent_advocate,
            date_of_filing,
            next_date,
            case_counter
        FROM
        (
            SELECT
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
                a.next_date,
                45-DATEDIFF(CURRENT_DATE, a.sr_date) AS case_counter,
                cp.date_of_creation,
                @row_num := IF(@prev_case_id = a.id, @row_num + 1, 1) AS row_num,
                @prev_case_id := a.id
            FROM
                `case` AS a
                LEFT JOIN `court` AS b ON a.court_name = b.id
                LEFT JOIN `case_type` AS c ON a.case_type = c.id
                LEFT JOIN `city` AS d ON a.city_id = d.id
                LEFT JOIN `staff` AS e ON a.handle_by = e.id AND e.type = 'admin'
                LEFT JOIN `case_procedings` AS cp ON a.id = cp.case_id
                CROSS JOIN (SELECT @row_num := 0, @prev_case_id := NULL) AS vars
            WHERE
                cp.next_date = CURRENT_DATE
            ORDER BY
                a.id,
                cp.next_date ASC,
                cp.date_of_creation DESC
        ) AS CTE_CaseDetails
        WHERE
            row_num = 1
        ORDER BY
            case_id DESC;
    ");

        //  $stmt->bind_param('i', $intern_id); // Removed since intern_id is not used in the query
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_case_counter()
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
        WHERE DATEDIFF(DATE_ADD(a.sr_date, INTERVAL 45 DAY), CURRENT_DATE) 
        ORDER BY a.id DESC;
    ");

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

        $stmt = $this->con->prepare("
        SELECT 
            t.id AS task_id,
            c.id AS case_id,
            c.stage AS stage_id,
            c.case_no,
            t.instruction,
            i.name AS alloted_to,
            a.name AS alloted_by,
            t.alloted_date,
            t.expected_end_date,
            t.status,
            st.stage  
        FROM task AS t 
        JOIN `case` AS c ON t.case_id = c.id 
        JOIN `staff` AS i ON i.id = t.alloted_to 
        JOIN `staff` AS a ON a.id = t.alloted_by 
        JOIN `stage` AS st ON st.id = c.stage 
        WHERE i.id = ? " . $qr . " 
        ORDER BY t.id DESC;
    ");

        $stmt->bind_param("s", $intern_id);
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
    public function task_remark_list($task_id)
    {
        $stmt = $this->con->prepare("SELECT `case_hist`.*,st.stage from `task` inner join `case_hist` on task.id = case_hist.task_id  join stage as st on st.id = `case_hist`.`stage`WHERE task.id = ? ORDER BY case_hist.id DESC");
        $stmt->bind_param("s", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function intern_case_history() // updated for new database
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
            cmp.name as company_name,
            45-DATEDIFF(CURRENT_DATE, a.sr_date) AS case_counter 
        FROM `case` AS a 
        JOIN `court` AS b ON a.court_name = b.id 
        JOIN `case_type` AS c ON a.case_type = c.id 
        JOIN `city` AS d ON a.city_id = d.id 
        JOIN `staff` AS e ON a.handle_by = e.id AND e.type = 'admin' 
        join `company` as cmp on cmp.id = a.company_id
        ORDER BY a.id DESC
    ");

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function notification($intern_id)
    {
        // Fetch notifications
        $stmt = $this->con->prepare("
        SELECT n.*, s.name, c.case_no 
        FROM `notification` n 
        JOIN `staff` s ON n.sender_id = s.id AND s.type = 'intern' 
        JOIN `task` AS t ON t.id = n.task_id 
        JOIN `case` AS c ON c.id = t.case_id 
        WHERE n.status = '1' AND n.receiver_id = ? 
        ORDER BY n.id DESC
    ");
        $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        // Fetch case count
        $stmt = $this->con->prepare("SELECT COUNT(*) AS count FROM `case`");
        $stmt->execute();
        $case_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Fetch task count for the intern
        $stmt = $this->con->prepare("
        SELECT COUNT(*) AS count  
        FROM task AS t 
        JOIN `case` AS c ON t.case_id = c.id 
        JOIN `staff` AS i ON i.id = t.alloted_to
        JOIN stage AS st ON st.id = c.stage 
        WHERE i.id = ? 
        ORDER BY t.id DESC
    ");
        $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $task_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Fetch today's case count
        $stmt = $this->con->prepare("
        SELECT COUNT(*) AS count 
        FROM (
            SELECT 
                a.id AS case_id, 
                @row_num := IF(@prev_case_id = a.id, @row_num + 1, 1) AS row_num, 
                @prev_case_id := a.id 
            FROM `case` AS a 
            LEFT JOIN `case_procedings` AS cp ON a.id = cp.case_id 
            CROSS JOIN (SELECT @row_num := 0, @prev_case_id := NULL) AS vars 
            WHERE cp.next_date = CURRENT_DATE 
            ORDER BY a.id, cp.next_date ASC, cp.date_of_creation DESC
        ) AS CTE_CaseDetails 
        WHERE row_num = 1
    ");
        $stmt->execute();
        $todays_case_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        // Fetch case counter count
        $stmt = $this->con->prepare("
        SELECT COUNT(*) AS count 
        FROM `case` a 
        JOIN `court` b ON a.court_name = b.id 
        JOIN `case_type` c ON a.case_type = c.id 
        JOIN `city` d ON a.city_id = d.id 
        JOIN `staff` e ON a.handle_by = e.id AND e.type = 'admin' 
        WHERE DATEDIFF(DATE_ADD(a.sr_date, INTERVAL 45 DAY), CURRENT_DATE)
    ");
        $stmt->execute();
        $counters_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        return [$result, $case_count, $task_count, $todays_case_count, $counters_count];
    }

    public function get_case_info($case_id)
    {
        $stmt = $this->con->prepare("
        SELECT
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
        FROM `case` AS c
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
        WHERE c.id = ?;
    ");

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
        $stmt = $this->con->prepare("
        SELECT 
            `case_hist`.*, 
            `case_hist`.dos AS fdos, 
            `case_hist`.date_time AS fdt, 
            intern_staff.name AS intern_name, 
            stage.stage AS stage_name, 
            `case`.case_no, 
            advocate_staff.name AS advocate_name 
        FROM `case_hist` 
        INNER JOIN `task` ON task.id = `case_hist`.task_id 
        INNER JOIN `case` ON `case`.id = task.case_id 
        INNER JOIN `stage` ON `case_hist`.stage = stage.id 
        INNER JOIN `staff` AS intern_staff ON task.alloted_to = intern_staff.id 
        INNER JOIN `staff` AS advocate_staff ON advocate_staff.id = task.alloted_by 
        WHERE task.case_id = ? 
        ORDER BY `case_hist`.id DESC
    ");

        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function case_history_documents($case_id)
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

        $stmt->bind_param("ii", $case_id, $case_id);
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
    public function get_case_remarks($case_id) // updated for new database
    {
        $stmt = $this->con->prepare("
        SELECT 
            c1.id,
            c1.case_no,
            case_hist.remarks,
            case_hist.status,
            DATE_FORMAT(`case_hist`.dos, '%d-%m-%Y') AS fdos,
            DATE_FORMAT(`case_hist`.date_time, '%d-%m-%Y') AS fdt,
            intern_staff.name AS intern_name,
            stage.stage AS stage_name,
            c1.case_no,
            advocate_staff.name AS advocate_name 
        FROM `case_hist` 
        INNER JOIN `task` ON task.id = case_hist.task_id 
        INNER JOIN `case` c1 ON c1.id = task.case_id 
        INNER JOIN `stage` ON case_hist.stage = stage.id 
        INNER JOIN `staff` AS intern_staff ON task.alloted_to = intern_staff.id AND intern_staff.type = 'intern' 
        INNER JOIN `staff` AS advocate_staff ON advocate_staff.id = task.alloted_by AND advocate_staff.type = 'admin' 
        WHERE task.case_id = ? 
        ORDER BY case_hist.id DESC
    ");

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
        $stmt = $this->con->prepare("SELECT id, name, contact, DATE_FORMAT(`date/time`, '%Y-%m-%d') AS date_time, email ,password,status FROM `staff` WHERE `type` = 'intern' ORDER BY id DESC;");

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function get_advocate_list()
    {
        $stmt = $this->con->prepare("
        SELECT id, name, contact, email 
        FROM `staff` 
        WHERE `status` = 'enable' AND `type` = 'admin' 
        ORDER BY id DESC;
    ");

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
            $stmt = $this->con->prepare("
            SELECT  
                case_id, case_no, applicant, opp_name, sr_date, court_name,  
                case_type, city_name, handle_by, complainant_advocate,  
                respondent_advocate, date_of_filing, next_date, next_stage_name, case_counter, sequence 
            FROM ( 
                SELECT  
                    a.id AS case_id, a.case_no, a.applicant, a.opp_name, a.sr_date,  
                    b.name AS court_name, c.case_type, d.name AS city_name, e.name AS handle_by,  
                    a.complainant_advocate, a.respondent_advocate, a.date_of_filing, 
                    cp.next_date, ns.stage AS next_stage_name,  
                    45-DATEDIFF(CURRENT_DATE, a.sr_date) AS case_counter, 
                    cp.date_of_creation, ts.sequence, 
                    @row_num := IF(@prev_case_id = a.id, @row_num + 1, 1) AS row_num, 
                    @prev_case_id := a.id 
                FROM `case` AS a  
                LEFT JOIN `court` AS b ON a.court_name = b.id  
                LEFT JOIN `case_type` AS c ON a.case_type = c.id  
                LEFT JOIN `city` AS d ON a.city_id = d.id  
                LEFT JOIN `staff` AS e ON a.handle_by = e.id AND e.type = 'admin'  
                LEFT JOIN `case_procedings` AS cp ON a.id = cp.case_id  
                LEFT JOIN `temp_sequence` AS ts ON a.id = ts.case_id 
                LEFT JOIN `stage` AS ns ON cp.next_stage = ns.id 
                CROSS JOIN ( SELECT @row_num := 0, @prev_case_id := NULL ) AS vars  
                WHERE cp.next_date = ? 
                ORDER BY ts.sequence ASC, a.id, cp.next_date ASC, cp.date_of_creation DESC 
            ) AS CTE_CaseDetails  
            WHERE row_num = 1  
            ORDER BY sequence ASC, case_id DESC;
        ");

            $formattedDate = $dateObj->format('Y-m-d');
            $stmt->bind_param('s', $formattedDate);
            $stmt->execute();
            $temp = $stmt->get_result();
            $stmt->close();

            $result[] = $temp;

            $dateObj->modify('+1 day');
        }

        return $result;
    }

    public function add_sequence($case_id, $sequence, $added_by)
    {

        $stmt = $this->con->prepare("INSERT INTO `temp_sequence`(`case_id`, `sequence`, `added_by`) VALUES (?,?,?)");
        $stmt->bind_param("iii", $case_id, $sequence, $added_by);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function edit_sequence($id, $case_id, $sequence, $added_by)
    {
        $stmt = $this->con->prepare("UPDATE `temp_sequence` SET `case_id`=?,`sequence`=?,`added_by`=? WHERE `id`=?");
        $stmt->bind_param("iisi", $case_id, $sequence, $added_by, $id);
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