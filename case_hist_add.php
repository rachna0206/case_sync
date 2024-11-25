<?php
include "header_intern.php";
error_reporting(E_ALL);

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    echo $stmt = $obj->con1->prepare("SELECT * FROM `case_hist` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
else
{
    $cno = $_COOKIE['case_no'];
}

if (isset($_REQUEST["save"])) {
    $tid = $_COOKIE["add_id"];  
    $stage = $_REQUEST['stage'];
    $remark = $_REQUEST['remark'];
    $date = $_REQUEST['dos'];
    $nextdate = $_REQUEST['ndt'];
    $status = $_REQUEST['radio'];

    //get case data
    
    $stmt_case = $obj->con1->prepare("select * from `case` where case_no=?");
    $stmt_case->bind_param("s", $_COOKIE['case_no']);
    $stmt_case->execute();
    $Resp_case = $stmt_case->get_result()->fetch_assoc();

    try {
        // Insert the new record into the case_hist table
        $stmt = $obj->con1->prepare("INSERT INTO case_hist(`task_id`, `stage`, `remarks`, `dos`, `nextdate`, `status`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("isssss", $tid, $stage, $remark, $date, $nextdate, $status);
        $Resp = $stmt->execute();

        //update case stage

        $stmt_case = $obj->con1->prepare("UPDATE `case` SET stage=? WHERE id=?");
        $stmt_case->bind_param("ii",  $stage,$Resp_case["id"]);
        $Resp_case_update = $stmt_case->execute();
        
        $stmt_case->close();

        
        if (!$Resp) {
            throw new Exception("Problem in adding! " . strtok($obj->con1->error, "("));
        }
        $stmt->close();

        // Update the status of the associated task in the task table
        $updateStmt = $obj->con1->prepare("UPDATE `task` SET `status` = ? WHERE `id` = ?");
        $updateStmt->bind_param("si", $status, $tid);
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
    $status = $_REQUEST['radio'];


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
    <h1>Task</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Task History</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Task for -
                <strong><?= $cno ?></strong></li>
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
                            <label for="stage" class="form-label">Stage</label>
                            <select class="form-control" id="stage" name="stage"
                                <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                <option value="">Select a Stage</option>
                                <?php 
                                $stmt_case = $obj->con1->prepare("select * from `case` where case_no=?");
                                $stmt_case->bind_param("s", $_COOKIE['case_no']);
                                $stmt_case->execute();
                                $Resp_case = $stmt_case->get_result()->fetch_assoc();
                                $stmt_case->close();

                                $comp = "SELECT * FROM stage";
                                $result = $obj->select($comp);
                                while ($row = mysqli_fetch_array($result)) { ?>
                                <option value="<?= htmlspecialchars($row["id"]) ?>"  <?=($Resp_case["stage"]==$row["id"])?"selected":""?>>
                                    <?= htmlspecialchars($row["stage"]) ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <input type="hidden" name="case_id" id="case_id" value=""/>

                        <div class="col-md-12">
                            <label for="title" class="form-label">Remark</label>
                            <input type="text" class="form-control" id="remark" name="remark"
                                value="<?php echo (isset($mode) && isset($data['remarks'])) ? $data['remarks'] : ''; ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>

                        <div class="col-md-12">
                            <label for="dos" class="form-label">Remark Date</label>
                            <input type="date" class="form-control" id="dos" name="dos"
                                value="<?php echo (isset($mode) && isset($data['dos']) && !empty($data['dos'])) ? date('Y-m-d', strtotime($data['dos'])) : date('Y-m-d'); ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>


                        <div class="col-md-12">
                            <label for="dos" class="form-label">Next Date</label>
                            <input type="date" class="form-control" id="ndt" name="ndt" value=""
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>



                        <div class="col-md-6">
                            <label for="inputEmail5" class="form-label">Status</label> <br />

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio1" value="pending" <?php 
                                echo (isset($data) && isset($data['status']) && $data['status'] == 'pending') ? 'checked' : 'checked'; 
                                echo (isset($mode) && $mode == 'view') ? ' disabled' : ''; 
                                ?> required />
                                <label class="form-check-label" for="radio1">Pending</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio2" value="completed" <?php 
                                    echo (isset($data) && isset($data['status']) && $data['status'] == 'completed') ? 'checked' : ''; 
                                    echo (isset($mode) && $mode == 'view') ? ' disabled' : ''; 
                                ?> required />
                                <label class="form-check-label" for="radio2">Completed</label>
                            </div>
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