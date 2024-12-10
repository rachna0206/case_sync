<?php
include "header_intern.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `task`  WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $cid = $_REQUEST['case_id']; 
    $ato = $_REQUEST['alloted_to'];
    $adate = $_REQUEST['alloted_date'];
    $status = $_REQUEST['radio'];

    try {
       // echo "INSERT INTO `company`(`company_name`, `contact_person`,`contact_num`, `status`) VALUES (".$company_name.",".$contact_person.",".$contact_num.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `task`(`case_id`, `alloted_to`,`alloted_date`, `status`) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $cid,$ato,$adate, $status);
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
        header("location:case_intern.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case_intern.php");
    }

   
    }

    

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $remark = $_REQUEST["remark_task"]; 


    try {
        $stmt = $obj->con1->prepare("UPDATE `task` SET `remark`=? WHERE `id`=?");
        $stmt->bind_param("si",  $remark, $e_id);
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
        header("location:case_intern.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case_intern.php");
    }
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Case</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case</li>
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

                            <div class="col-md-12">
                                <label for="case_id" class="form-label">Case No</label>
                                <input type="text" class="form-control" id="case_id" name="case_id"
                                value="<?php echo isset($data['case_id']) ? $data['case_id'] : '' ?>" readonly>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="case_id" class="form-label">Alloted To</label>
                                <input type="text" class="form-control" id="alloted_to" name="alloted_to"
                                value="<?php echo isset($data['alloted_to']) ? $data['alloted_to'] : '' ?>" readonly>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label">Alloted Date</label>
                                <input type="date" class="form-control" id="alloted_date" name="alloted_date"
                                    value="<?php echo (isset($data['alloted_date'])) ? $data['alloted_date'] : '' ?>" readonly>
                            </div>

                            <div class="col-md-12">
                                <label for="city_id" class="form-label">Stage</label>
                                <select class="form-control" id="remark_task" name="remark_task"
                                    <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?> readonly>
                                    <option value="">Select Stage</option>
                                    <?php 
                                    $comp = "SELECT * FROM `stage`";
                                    $result = $obj->select($comp);
                                    $selectedCompanyId = isset($data['stage_case']) ? $data['stage_case'] : '';

                                    while ($row = mysqli_fetch_array($result)) { 
                                        $selected = ($row["id"] == $selectedCompanyId) ? 'selected' : '';
                                    ?>
                                        <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($row["stage"]) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                        <div class="col-md-6">
                            <label for="inputEmail5" class="form-label">Status</label> <br />
                            <div class="form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio1"
                                    <?php echo isset($mode) && $data['status'] == 'enable' ? 'checked' :'' ?>
                                    class="form-radio text-primary" value="enable" checked required
                                    <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                                <label class="form-check-label" for="radio1">Enable</label>
                            </div>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio2"
                                    <?php echo isset($mode) && $data['status'] == 'disable' ? 'checked' : '' ?>
                                    class="form-radio text-danger" value="disable" required
                                    <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                                <label class="form-check-label" for="radio2">Disable</label>
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
    window.location = "case_intern.php";
}

</script>
<?php
include "footer_intern.php";
?>