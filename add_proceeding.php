<?php
include "header.php";
error_reporting(E_ALL);

if (isset($_REQUEST["save"])) {
    $remark = $_REQUEST['remark'];
    $next_date = $_REQUEST['next_date'];
    $next_stage = $_REQUEST['next_stage'];

    // Fetch the case details based on the case number from the cookie
    $stmt_case = $obj->con1->prepare("SELECT * FROM `case` WHERE case_no=?");
    $stmt_case->bind_param("s", $_COOKIE['case_no']);
    $stmt_case->execute();
    $Resp_case = $stmt_case->get_result()->fetch_assoc();
    $stmt_case->close();

    try {
        // Date formatting and validation (same as the original code)
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

        // If date is valid, format it for database insertion
        if ($date_obj) {
            $formatted_date = $date_obj->format('Y-m-d');
        } else {
            $formatted_date = null; // Handle invalid date input
        }

        // Get the user ID (inserted_by)
        $inserted_by = $_SESSION["id"];

        // Insert the proceeding record into `case_procedings` table
        $stmt = $obj->con1->prepare("INSERT INTO `case_procedings` (`case_id`, `next_stage`, `next_date`, `remarks`, `inserted_by`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $_COOKIE['case_id'], $next_stage, $formatted_date, $remark, $inserted_by);
        $result = $stmt->execute();
        $stmt->close();

        if (!$result) {
            throw new Exception("Problem in adding proceeding record!");
        }

        // Update the `case` table with the next stage
        $stmt_case = $obj->con1->prepare("UPDATE `case` SET stage=? WHERE id=?");
        $stmt_case->bind_param("ii", $next_stage, $_COOKIE['case_id']);
        $stmt_case->execute();
        $stmt_case->close();

        // Send notifications to all staff except the current user
        $stmt = $obj->con1->prepare("SELECT * FROM `staff` WHERE status='enable' AND id != ?");
        $stmt->bind_param("i", $inserted_by);
        $stmt->execute();
        $result_staff = $stmt->get_result();
        $stmt->close();

        while ($data = $result_staff->fetch_assoc()) {
            $type = "case_proceed";
            $alloted_by = $_SESSION["id"];
            $alloted_to = $data["id"];
            $msg = "Case has been Proceeded";
            $status = 1;  // Notification status
            $playstatus = 1; // Play notification sound or not

            // Insert a notification for each staff member
            $stmt = $obj->con1->prepare("INSERT INTO `notification` (`task_id`, `type`, `sender_id`, `receiver_id`, `msg`, `status`, `playstatus`, `datetime`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('isiisii', $_COOKIE['case_id'], $type, $alloted_by, $alloted_to, $msg, $status, $playstatus);
            $stmt->execute();
            $stmt->close();
        }

        // If all successful, set a success message and redirect
        setcookie("msg", "data", time() + 3600, "/");
        header("Location: stage.php");
        exit;

    } catch (\Exception $e) {
        // Handle errors, set the error message in a cookie, and redirect
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
        header("Location: stage.php");
        exit;
    }

}


/*if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $tid = $_REQUEST['taskid'];
    $stage = $_REQUEST['stage'];
    $remark = $_REQUEST['remark'];
    $date = $_REQUEST['dos'];
    $status = $_REQUEST['radio'];


    try {
        $stmt = $obj->con1->prepare("UPDATE case_hist SET task_id=?, stage=?,remarks=?,dos=?,status=? WHERE id=?");
        $stmt->bind_param("issssi", $tid, $stage, $remark, $date, $status, $e_id);
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
*/

?>

<div class="pagetitle">
    <h1>Case</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case History</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Proceeding
            </li>
        </ol>
    </nav>
</div>
<!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <!-- Multi Columns Form -->
                    <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">
                        <div class="col-md-12">
                            <label for="stage" class="form-label">Current Stage</label>

                            <select class="form-control" id="stage" name="stage" disabled>
                                <option value="">Select a Stage</option>
                                <?php


                                $comp = "SELECT * FROM stage";
                                $result = $obj->select($comp);
                                while ($row = mysqli_fetch_array($result)) { ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>">
                                        <?= htmlspecialchars($row["stage"]) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <input type="hidden" name="case_id" id="case_id" value="" />

                        <div class="col-md-12">
                            <label for="title" class="form-label">Remark</label>
                            <input type="text" class="form-control" id="remark" name="remark"
                                value="<?php echo (isset($mode) && isset($data['remarks'])) ? $data['remarks'] : ''; ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?> required>
                        </div>



                        <div class="col-md-12">
                            <label for="stage" class="form-label">Next Stage</label>
                            <select class="form-control" id="next_stage" name="next_stage" required>
                                <option value="">Select a Stage</option>
                                <?php

                                $stmt = $obj->con1->prepare("SELECT * FROM `stage` WHERE status = 'enable' AND `case_type_id` = (select case_type from `case` where id = ?) ;");
                                $stmt->bind_param("i", $_COOKIE['case_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = mysqli_fetch_array($result)) { ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>">
                                        <?= htmlspecialchars($row["stage"]) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>



                        <div class="col-md-12">
                            <label for="dos" class="form-label">Next Date</label>
                            <input type="date" class="form-control" id="next_date" name="next_date" value="" <?php
                            echo isset($mode) && $mode == 'view' ? 'readonly' : '';
                            ?> required>
                        </div>



                        <!-- <div class="col-md-6">
                            <label for="inputEmail5" class="form-label">Status</label> <br />

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio1" value="pending" <?php
                                // echo (isset($data) && isset($data['status']) && $data['status'] == 'pending') ? 'checked' : 'checked';
                                // echo (isset($mode) && $mode == 'view') ? ' disabled' : '';
                                ?>    required />
                                <label class="form-check-label" for="radio1">Pending</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio2" value="completed" <?php
                                // echo (isset($data) && isset($data['status']) && $data['status'] == 'completed') ? 'checked' : '';
                                // echo (isset($mode) && $mode == 'view') ? ' disabled' : '';
                                ?> required />
                                <label class="form-check-label" for="radio2">Completed</label>
                            </div>
                        </div>
                                -->

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
        eraseCookie("case_id");
        window.location = "case_hist.php";
    }

</script>
<?php
include "footer.php";
?>