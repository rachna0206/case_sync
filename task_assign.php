<?php
include "header_intern.php";

$cno = $_COOKIE['case_id'];
// echo "$cno";

if (isset($_REQUEST["save"])) {
    $tid = $_COOKIE["assign_id"];  
    $intern = $_REQUEST['intern'];
    $remark = $_REQUEST['remark'];
    $alloted_by = $_SESSION["intern_id"];
    $alloted_date = $_REQUEST["rmk_date"];
    $new_status = "reassign";
    $old_status="re_alloted";
    $action_by="intern";

    // Fetching the instruction from the task table based on case_id
    $stmt = $obj->con1->prepare("SELECT * FROM `task` WHERE `case_id` = ?");
    $stmt->bind_param('i', $cno); // Changed $con to $cno
    $stmt->execute();
    $Resp = $stmt->get_result();

    if ($row = mysqli_fetch_array($Resp)) {
        $instruction = $row["instruction"];
    } else {
        // Handle case where no rows were returned
        setcookie("sql_error", "No task found for this case ID.", time() + 3600, "/");
        header("location:task_intern.php");
        exit();
    }

    try {
        // Prepare insert statement
        $stmt = $obj->con1->prepare("INSERT INTO task(`case_id`, `alloted_to`, `instruction`, `alloted_by`,`action_by`, `alloted_date`, `status`, `remark`) VALUES (?, ?, ?, ?, ?, ?, ?,?)");
        // Bind parameters
        $stmt->bind_param("iisissss", $cno, $intern, $instruction, $alloted_by,$action_by, $alloted_date, $new_status, $remark);
        
        // Execute and check response
        if (!$stmt->execute()) {
            throw new Exception("Problem in adding! " . strtok($obj->con1->error, "("));
        }
        $stmt->close();
        
       // Update the status of the associated task in the task table
       $updateStmt = $obj->con1->prepare("UPDATE `task` SET `status` = ? WHERE `id` = ?");
       $updateStmt->bind_param("si", $old_status, $tid);
       $updateResp = $updateStmt->execute();
       
       if (!$updateResp) {
           throw new Exception("Problem in updating task status! " . strtok($obj->con1->error, "("));
       }
       $updateStmt->close();

   } catch (\Exception $e) {
       setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
   }

   if ($Resp && $updateResp) {
       setcookie("msg", "data", time() + 3600, "/");
       header("location:task_intern.php");
   } else {
       setcookie("msg", "fail", time() + 3600, "/");
       header("location:task_intern.php");
   }
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
        $stmt = $obj->con1->prepare("UPDATE case_hist SET task_id=?, stage=?,remarks=?,dos=?,`status`=? WHERE id=?");
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
                                <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                <option value="">Select an Intern</option>
                                <?php 
                                    $signedInInternId = $_SESSION['intern_id'];

                                    // Update the query to exclude the signed-in intern
                                    $intern = "SELECT * FROM `interns` WHERE id != ?";
                                    $stmt = $obj->con1->prepare($intern);
                                    $stmt->bind_param("i", $signedInInternId);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($row = mysqli_fetch_array($result)) { ?>
                                <option value="<?= htmlspecialchars($row["id"]) ?>">
                                    <?= htmlspecialchars($row["name"]) ?>
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
    window.location = "task_intern.php";
}
</script>
<?php
include "footer_intern.php";
?>