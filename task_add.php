<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `task` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $cid = $_REQUEST['case_id'];
    $ato = $_REQUEST['alloted_to'];
    $adate = $_REQUEST['alloted_date'];
    $edate = $_REQUEST['exp_end_date'];
    $status = $_REQUEST['radio'];
    $instruction =  $_REQUEST['instruction'];
    $action_by = "advocate";

    try {
        // echo "INSERT INTO `task`(`case_id`, `alloted_to`,`alloted_date`, `status`) VALUES ($cid, $ato, $adate, $status)";
        $stmt = $obj->con1->prepare("INSERT INTO `task`(`case_id`, `alloted_to`,`instruction` , `alloted_by` ,`action_by`,`alloted_date`,`expected_end_date`, `status`) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ississss", $cid, $ato, $instruction, $_SESSION["id"], $action_by, $edate, $adate, $status);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {

        setcookie("msg", "data", time() + 3600, "/");
        header("location:task.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:task.php");
    }
}

if (isset($_REQUEST["btn_city"])) {

    $state = $_REQUEST['state_id'];
    $city_name = $_REQUEST['name'];
    $status='enable';
    try {
        // echo "INSERT INTO `city`(`name`, `status`) VALUES (". $city_name.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `city`(`state_id`,`name`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss",$state, $city_name, $status);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    if ($Resp) {
        
        
        header("location:task_add.php");
    } else {
        
        header("location:task_add.php"); 
    }
}



if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $cid = $_REQUEST['case_id'];
    $ato = $_REQUEST['alloted_to'];
    $adate = $_REQUEST['alloted_date'];
    $edate = $_REQUEST['exp_end_date'];
    $status = $_REQUEST['radio'];
    $instruction =  $_REQUEST['instruction'];


    try {
        $stmt = $obj->con1->prepare("UPDATE `task` SET `case_id`=?, `alloted_to`=?,`instruction`=?,`alloted_date`=?,`expected_end_date`=?,`status`=? WHERE `id`=?");
        $stmt->bind_param("isssssi",  $cid, $ato, $instruction, $adate, $edate, $status, $e_id);
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
        header("location:task.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:task.php");
    }
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Task</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Task</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>- Data</li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">
                        <div class="row pt-3">
                            <div class="col-md-6">
                                <label for="case_type" class="form-label">Case Type</label>
                                <select class="form-control" id="case_type" name="case_type"
                                    <?= isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                    <option value="">Select Case Type</option>
                                    <?php
                                    $comp = "SELECT * FROM `case_type` WHERE status='enable'";
                                    $result = $obj->select($comp);
                                    $selectedcourtId = isset($data['case_type']) ? $data['case_type'] : '';

                                    while ($row = mysqli_fetch_array($result)) {
                                        $selected = ($row["id"] == $selectedcourtId) ? 'selected' : '';
                                    ?>
                                        <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($row["case_type"]) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label for="city" class="form-label">City</label>
                                <select class="form-control" id="city" name="city"
                                    <?= isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                    <option value="">Select City</option>
                                    <?php
                                    $task = "SELECT * FROM `city` WHERE status='enable'";
                                    $result = $obj->select($task);
                                    $selectedCaseId = isset($data['city_id']) ? $data['city_id'] : '';

                                    while ($row = mysqli_fetch_array($result)) {
                                        $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                                    ?>
                                        <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($row["name"]) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                               
                            </div>
                            <div class="col-md-1 mt-4 pt-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addcitymodal">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <label for="case_id" class="form-label">Case Number</label>
                            <select class="form-control" id="case_id" name="case_id"
                                <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?> required>
                                <option value="">Select a Case</option>
                                <?php
                                $task = "SELECT * FROM `case`";
                                $result = $obj->select($task);
                                $selectedCaseId = isset($data['case_id']) ? $data['case_id'] : '';

                                while ($row = mysqli_fetch_array($result)) {
                                    $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row["case_no"]) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="case_id" class="form-label">Alloted To</label>
                            <select class="form-control" id="alloted_to" name="alloted_to"
                                <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                <option value="">Select Intern</option>
                                <?php
                                $task = "SELECT * FROM `interns`";
                                $result = $obj->select($task);
                                $selectedCaseId = isset($data['alloted_to']) ? $data['alloted_to'] : '';

                                while ($row = mysqli_fetch_array($result)) {
                                    $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row["name"]) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="col-md-12">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Task Instruction</label>
                            <textarea class="form-control" style="height: 100px" id="instruction" name="instruction"
                                required
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo (isset($mode)) ? $data['instruction'] : '' ?></textarea>
                        </div>
                        <div class="row pt-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Allotted Date</label>
                                <input type="date" class="form-control" id="alloted_date" name="alloted_date"
                                    value="<?php echo (isset($mode) && isset($data['alloted_date']) && !empty($data['alloted_date'])) ? date('Y-m-d', strtotime($data['alloted_date'])) : date('Y-m-d'); ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                            </div>

                            <div class="col-md-6">
                                <label for="title" class="form-label">Expected End Date</label>
                                <input type="date" class="form-control" id="exp_end_date" name="exp_end_date"
                                    value="<?php echo (isset($mode) && isset($data['expected_end_date']) && !empty($data['expected_end_date'])) ? date('Y-m-d', strtotime($data['alloted_date'])) : date('Y-m-d'); ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                        <input type="hidden" name="radio" value="allotted">
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

<div class="modal fade" id="addcitymodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add City</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                <div class="col-md-12 mb-3">
                    <label for="state_id" class="form-label">State</label>
                    <select class="form-control" id="state_id" name="state_id" required>
                        <option value="">Select State</option>
                        <?php
                        $task = "SELECT * FROM `state` where `status` = 'Enable'";
                        $result = $obj->select($task);
                        $selectedCaseId = isset($data['state_id']) ? $data['state_id'] : '';

                        while ($row = mysqli_fetch_array($result)) {
                           
                        ?>
                            <option value="<?= htmlspecialchars($row["id"]) ?>" >
                                <?= htmlspecialchars($row["state_name"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>


                <div class="col-md-12">
                    <label for="title" class="form-label">City Name</label>
                    <input type="text" class="form-control" id="name" name="name"                     
                         required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="btn_city" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add city Modal-->

<script>
    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "task.php";
    }
</script>
<?php
include "footer.php";
?>