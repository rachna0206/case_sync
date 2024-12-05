<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `court` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $name = $_REQUEST['name'];
    $case_type = $_REQUEST['case_type'];
    $status = $_REQUEST['radio'];

    try {
       // echo "INSERT INTO `company`(`company_name`, `contact_person`,`contact_num`, `status`) VALUES (".$company_name.",".$contact_person.",".$contact_num.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `court`(`name`, `case_type`,`status`) VALUES (?,?,?)");
        $stmt->bind_param("sis", $name, $case_type,$status);
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
        header("location:court.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:court.php");
    }

   
}

    

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $name = $_REQUEST['name'];
    $case_type = $_REQUEST['case_type'];
    $status = $_REQUEST['radio'];


    try {
        $stmt = $obj->con1->prepare("UPDATE `court` SET `name`=?,`case_type`=?,`status`=? WHERE `id`=?");
        $stmt->bind_param("sisi",  $name,$case_type, $status, $e_id);
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
    header("location:court.php");
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Court</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Court</li>
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
                            <label for="title" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo (isset($mode)) ? $data['name'] : '' ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                        </div>

                        <div class="col-md-12">
                                <label for="case_type" class="form-label">Case Type</label>
                                <select class="form-control" id="case_type" name="case_type"
                                    <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                    <option value="">Select Case Type</option>
                                    <?php 
                                        $comp = "SELECT * FROM `case_type` where status='enable'";
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
    window.location = "court.php";
}
</script>
<?php
include "footer.php";
?>