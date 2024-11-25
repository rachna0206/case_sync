<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `company` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $company_name = $_REQUEST['company_name'];
    $contact_person = $_REQUEST['company_person'];
    $contact_num = $_REQUEST['company_num'];
    $status = $_REQUEST['radio'];

    try {
       // echo "INSERT INTO `company`(`company_name`, `contact_person`,`contact_num`, `status`) VALUES (".$company_name.",".$contact_person.",".$contact_num.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `company`(`name`, `contact_person`,`contact_no`, `status`) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $company_name,$contact_person,$contact_num, $status);
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
        header("location:company.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:company.php");
    }

   
}

    

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $company_name = $_REQUEST['company_name'];
    $contact_person = $_REQUEST['company_person'];
    $contact_num = $_REQUEST['company_num'];
    $status = $_REQUEST['radio'];


    try {
        $stmt = $obj->con1->prepare("UPDATE `company` SET `name`=?, `contact_person`=?,`contact_no`=?,`status`=? WHERE `id`=?");
        $stmt->bind_param("ssssi",  $company_name,$contact_person,$contact_num, $status, $e_id);
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
    header("location:company.php");
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Company</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Company</li>
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
                                <label for="title" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name"
                                    value="<?php echo (isset($mode)) ? $data['name'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> required>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label">Person</label>
                                <input type="text" class="form-control" id="company_person" name="company_person"
                                    value="<?php echo (isset($mode)) ? $data['contact_person'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> required>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label">Contact No.</label>
                                <input type="text" class="form-control" id="company_num" name="company_num"
                                    value="<?php echo (isset($mode)) ? $data['contact_no'] : '' ?>" 
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> required>
                            </div>

                        <div class="col-md-6">
                            <label for="inputEmail5" class="form-label">Status</label> <br />
                            <div class="form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio1"
                                    <?php echo isset($mode) && $data['status'] == 'Enable' ? 'checked' :'' ?>
                                    class="form-radio text-primary" value="Enable" checked required
                                    <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                                <label class="form-check-label" for="radio1">Enable</label>
                            </div>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio2"
                                    <?php echo isset($mode) && $data['status'] == 'Disable' ? 'checked' : '' ?>
                                    class="form-radio text-danger" value="Disable" required
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
    window.location = "company.php";
}

</script>
<?php
include "footer.php";
?>