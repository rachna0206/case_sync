<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `stage` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $case_type = $_REQUEST["case_type_id"];
    $stg = $_REQUEST['stage'];
    $status = $_REQUEST['radio'];

    try {
       // echo "INSERT INTO `company`(`company_name`, `contact_person`,`contact_num`, `status`) VALUES (".$company_name.",".$contact_person.",".$contact_num.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `stage`(`case_type_id`,`stage`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss",$case_type, $stg, $status);
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
        header("location:stage.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:stage.php");
    }

   
    }

    

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $case_type = $_REQUEST["case_type_id"];
    $stg = $_REQUEST['stage'];
    $status = $_REQUEST['radio'];


    try {
        $stmt = $obj->con1->prepare("UPDATE `stage` SET `case_type_id` = ?, `stage`=?,`status`=? WHERE `id`=?");
        $stmt->bind_param("issi",$case_type, $stg, $status, $e_id);
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
        header("location:stage.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:stage.php");
    }
}



if (isset($_REQUEST["btn_case_type"])) {

    $case_type_m = $_REQUEST['c_type'];
    $status='enable';
    try {
        // echo "INSERT INTO `city`(`case_type`, `status`) VALUES (". $case_type_m.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `case_type`(`case_type`, `status`) VALUES (?,?)");
        $stmt->bind_param("ss",$case_type_m, $status);
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
        
        
        header("location:stage_add.php");
    } else {
        
        header("location:stage_add.php"); 
    }
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Stage</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Stage</li>
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

                    <!-- Multi Columns Form -->
                    <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">

                            
                            <div class="col-md-12">
                                <label for="case_type_id" class="form-label">Case Type</label>
                                <div class="d-flex">
                                <select class="form-control" id="case_type_id" name="case_type_id"
                                    <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                    <option value="">Select a Case Type</option>
                                    <?php 
                                    $comp = "SELECT * FROM `case_type` where status='enable' and id!=0";
                                    $result = $obj->select($comp);
                                    $selectedCompanyId = isset($data['case_type_id']) ? $data['case_type_id'] : ''; // Adjust this if necessary

                                    while ($row = mysqli_fetch_array($result)) { 
                                        $selected = ($row["id"] == $selectedCompanyId) ? 'selected' : '';
                                    ?>
                                        <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($row["case_type"]) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addcasetypemodal">
                                        <i class="bi bi-plus"></i>
                                    </button>
                            </div>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label">Stage</label>
                                <input type="text" class="form-control" id="stage" name="stage"
                                    value="<?php echo (isset($mode)) ? $data['stage'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> required>
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

<div class="modal fade" id="addcasetypemodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Case Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">

                    <div class="col-md-12">
                        <label for="title" class="form-label">Case Type</label>
                        <input type="text" class="form-control" id="c_type" name="c_type" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="add_casetype()">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add case type Modal-->

<script>
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "stage.php";
}


function add_casetype(){
    var c_type = document.getElementById("c_type").value;
    $("#addcasetypemodal").modal("toggle");

    $.ajax({
        async: true,
        type: "POST",
        url: "action.php?action=add_casetype",
        data: "c_type=" + c_type,
        cache: false,
        success: function(result) {
            $("#case_type_id").append(result);
          

        }
    });

}








</script>
<?php
include "footer.php";
?>