<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    
    $stmt = $obj->con1->prepare("SELECT * FROM `city` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $state = $_REQUEST['state_id'];
    $city_name = $_REQUEST['name'];
    $status = $_REQUEST['radio'];

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
        
        setcookie("msg", "data", time() + 3600, "/");
        header("location:city.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:city.php"); 
    }

   
    }

    

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $state = $_REQUEST['state_id'];
    $city_name = $_REQUEST['name'];
    $status = $_REQUEST['radio'];



    try {
        $stmt = $obj->con1->prepare("UPDATE `city` SET `state_id`=?,`name`=?, `status`=? WHERE `id`=?");
        $stmt->bind_param("issi",  $state,$city_name, $status, $e_id);
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
        header("location:city.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:city.php");
    }
}

if (isset($_REQUEST["btn_case_type"])) {

    $case_type_m = $_REQUEST['c_type'];
    $status='enable';
    try {
        // echo "INSERT INTO `city`(`case_type`, `status`) VALUES (". $case_type_m.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `state`(`state_name`, `status`) VALUES (?,?)");
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
        
        
        header("location:city_add.php");
    } else {
        
        header("location:city_add.php"); 
    }
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>City</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">City</li>
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
                        
                    <!-- <div class="col-md-12">
                            <label for="menu" class="form-label">State</label>
                            <select id="inputMenu" class="form-select" name="state" id="state" required>
                                <option selected>Choose Menu</option>
                                <?php
                                        $stmt_list = $obj->con1->prepare("SELECT * FROM `state` WHERE `status`= 'Enable'");
                                        $stmt_list->execute();
                                        $result = $stmt_list->get_result();
                                        $stmt_list->close();
                                        $i=1;
                                        while($state=mysqli_fetch_array($result))
                                        {
                                    ?>
                                <option value="<?php echo $state["id"]?>"
                                    <?php echo isset($mode) && $data['state_id'] == $state["id"] ? 'selected' : '' ?>>
                                    <?php echo $state["state_name"]?></option>
                                <?php 
                                        }
                                    ?>
                            </select>
                        </div> -->
                            
                                <div class="col-md-12">
                                    <label for="state_id" class="form-label">State</label>
                                    <div class="d-flex">
                                    <select class="form-control" id="state_id" name="state_id"
                                        <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                        <option value="">Select State</option>
                                        <?php 
                                        $task = "SELECT * FROM `state` where `status` = 'Enable'";
                                        $result = $obj->select($task);
                                        $selectedCaseId = isset($data['state_id']) ? $data['state_id'] : ''; 

                                        while ($row = mysqli_fetch_array($result)) { 
                                            $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                                        ?>
                                            <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($row["state_name"]) ?>   
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
                                <label for="title" class="form-label">City Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo (isset($mode)) ? $data['name'] : '' ?>"
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
                            <button type="button" class="btn btn-danger" onclick="javascript: go_back() ;">
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
                <button type="submit" name="btn_case_type" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add case type Modal-->

<script>
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "city.php";
}

</script>
<?php
include "footer.php";
?>