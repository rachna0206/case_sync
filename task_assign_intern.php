<?php
include "header_intern.php";

$cno = $_COOKIE['case_id'];

if (isset($_REQUEST["save"])) {

    $task_id = $_COOKIE["assign_id"];  // Task to reassign
    $reassign_id = $_REQUEST['intern'];  // New intern
    $remark = $_REQUEST['remark'];
    $intern_id = $_SESSION["intern_id"];  // Current user
    $remark_date = $_REQUEST["rmk_date"];

    try {
        // 1. Fetch existing task details
        $stmt = $obj->con1->prepare("SELECT * FROM task WHERE id=?");
        $stmt->bind_param('i', $task_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$data) {
            throw new Exception("Task not found.");
        }

        // Extract task details
        $case_id = $data["case_id"];
        $old_alloted_to = $data["alloted_to"];
        $old_alloted_by = $data["alloted_by"];
        $instruction = $data["instruction"];
        $expected_end_date = $data["expected_end_date"];
        $old_remark = $data["remark"];

        $alloted_to = $reassign_id;
        $alloted_by = $intern_id;
        $status = "re_alloted";

        // 2. Update original task status and assign to new intern
        $stmt = $obj->con1->prepare("UPDATE task SET `status`='re_alloted', alloted_to=? , alloted_by=? WHERE id=?");
        $stmt->bind_param('iii', $alloted_to, $alloted_by, $task_id);
        $result1 = $stmt->execute();
        $stmt->close();

        // 3. Get case stage
        $stmt = $obj->con1->prepare("SELECT stage FROM `case` WHERE id = ?");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $stage_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$stage_info) {
            throw new Exception("Case stage not found.");
        }

        $stage_id = $stage_info["stage"];
        $status = 'pending';

        // 4. Insert into case history
        $stmt = $obj->con1->prepare("INSERT INTO `case_hist`(`task_id`, `stage`, `remarks`, `dos`, `added_by`, `status`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('iissis', $task_id, $stage_id, $remark, $remark_date, $intern_id, $status);
        $result3 = $stmt->execute();
        $case_hist_id = mysqli_insert_id($obj->con1);
        $stmt->close();

        // 5. Insert into reassign_table
        $stmt = $obj->con1->prepare("INSERT INTO `reassign_table`(`case_hist_id`, `alloted_to`, `alloted_by`, `current_alloted`) VALUES (?,?,?,?)");
        $stmt->bind_param('iiii', $case_hist_id, $old_alloted_to, $old_alloted_by, $reassign_id);
        $result2 = $stmt->execute();
        $stmt->close();

        // 6. Insert into notifications
        $type = "task_reassigned";
        $msg = "Task has been reassigned";
        $status = 1;
        $playstatus = 1;

        $stmt = $obj->con1->prepare("INSERT INTO notification (`task_id`, `type`, `sender_id`, `receiver_id`, `msg`, `status`, `playstatus`, `datetime`) VALUES (?,?,?,?,?,?,?,NOW())");
        $stmt->bind_param('isiisii', $task_id, $type, $intern_id, $reassign_id, $msg, $status, $playstatus);
        $result4 = $stmt->execute();
        $stmt->close();

        if ($result1 && $result2 && $result3 && $result4) {
            setcookie("msg", "data", time() + 3600, "/");
        } else {
            throw new Exception("One or more DB operations failed.");
        }

    } catch (Exception $e) {
        setcookie("sql_error", urlencode("Error during reassignment: " . $e->getMessage()), time() + 3600, "/");
    }

    header("location:task_alloted_to_me_intern.php");

}


    

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $tid = $_REQUEST['taskid'];
    $stage = $_REQUEST['stage'];
    $remark = $_REQUEST['remark'];
    $date = $_REQUEST['dos'];
    $new_status = "reassign";
    $old_status="re_alloted";
    $action_by="intern";


    try {
        $stmt = $obj->con1->prepare("UPDATE case_hist SET task_id=?, stage=?,remarks=?,dos=?,status=? WHERE id=?");
        $stmt->bind_param("issssi",  $tid,$stage,$remark,$date, $status, $e_id);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in updating! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("edit_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        
        
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
       
       
    }
    header("location:case_hist.php");
    header("location:case_hist.php");
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Task Assign</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Task Assign</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>-
                <strong><?= $cno ?></strong>
            </li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    <!-- Multi Columns Form -->
                    <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">
                        <div class="col-md-12">
                            <label for="intern" class="form-label">Interns</label>
                            <select class="form-control" id="intern" name="intern"
                                <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>required>
                                <option value="">Select Intern</option>
                                <?php
                                $task = "SELECT * FROM `staff` where `status`='enable'";
                                $result = $obj->select($task);
                                $selectedCaseId = isset($data['alloted_to']) ? $data['alloted_to'] : '';

                                while ($row = mysqli_fetch_array($result)) {
                                    $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                                    ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                        <?php echo htmlspecialchars($row["name"] . ' - (' . $row["type"] . ')') ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="title" class="form-label">Remark</label>
                            <input type="text" class="form-control" id="remark" name="remark"
                                value="<?php echo (isset($mode) && isset($data['remarks'])) ? $data['remarks'] : ''; ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>

                        <div class="col-md-12">
                            <label for="title" class="form-label">Remark Date</label>
                            <input type="date" class="form-control" id="rmk_date" name="rmk_date"
                                value="<?php echo (isset($mode) && isset($data['remark'])) ? $data['remark'] : date('Y-m-d'); ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>

                        
                        <div class="text-left mt-4">
                            <button type="submit"
                                name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'd-none' : '' ?>"><?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="javascript: go_back();">
                                Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    eraseCookie("add_id");
    eraseCookie("case_no");
    window.location = "task_alloted_to_me_intern.php";
}
</script>
<?php
include "footer_intern.php";
?>