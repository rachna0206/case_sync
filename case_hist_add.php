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
    $next_stage = $_REQUEST['next_stage'];

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

        $stmt_case = $obj->con1->prepare("UPDATE `case` SET `stage`=?,`next_date`=?,`next_stage`=? WHERE id=?");
        $stmt_case->bind_param("isii",  $stage,$nextdate,$next_stage,$Resp_case["id"]);
        $Resp_case_update = $stmt_case->execute();
        
        $stmt_case->close();

        //upload documents
        foreach ($_FILES["docs"]['name'] as $key => $value) {
            if ($_FILES["docs"]['name'][$key] != "") {
                $PicSubImage = $_FILES["docs"]["name"][$key];
                // Generate unique file name if file already exists
                $SubImageName = generateUniqueFileName("documents/case/", $PicSubImage);
                $SubImageTemp = $_FILES["docs"]["tmp_name"][$key];
                $SubImageName = str_replace(' ', '_', $SubImageName);

                // Move uploaded file
                move_uploaded_file($SubImageTemp, "documents/case/" . $SubImageName);
                $added_by=$_SESSION["intern_id"];
                $user_type="intern";
                
                $stmt_image = $obj->con1->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`,`added_by`,`user_type`) VALUES (?, ?,?,?)");
                $stmt_image->bind_param("isis", $Resp_case["id"], $SubImageName,$added_by,$user_type);
                $Resp_img = $stmt_image->execute();
                $stmt_image->close();

            }
        }

        
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

// Function to generate unique file name
function generateUniqueFileName($directory, $filename)
{
    if (file_exists($directory . $filename)) {
        $i = 0;
        $Arr = explode('.', $filename);
        $baseName = $Arr[0];
        $extension = end($Arr);
        do {
            $i++;
            $filename = $baseName . $i . '.' . $extension;
        } while (file_exists($directory . $filename));
    }
    return $filename;
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
                            <label for="stage" class="form-label">Current Stage</label>
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

                        <div class="col-md-12" <?php echo (isset($mode)) ? 'hidden' : '' ?>>
                            <label for="docs" class="form-label">Documents</label>
                            <input type="file" class="form-control mb-3" id="docs" name="docs[]"
                                onchange="readURL_multiple(this)" multiple <?php echo (isset($mode)) ? '' : 'required' ?>>
                            <div id="preview_file_div" style="color:blue"></div>
                        </div>

                        <div class="col-md-12">
                            <label for="stage" class="form-label">Next Stage</label>
                            <select class="form-control" id="next_stage" name="next_stage">
                                <option value="">Select a Stage</option>
                                <?php 
                                $stmt_case = $obj->con1->prepare("select * from `case` ");
                               
                                $stmt_case->execute();
                                $Resp_case = $stmt_case->get_result()->fetch_assoc();
                                $stmt_case->close();

                                $comp = "SELECT * FROM stage";
                                $result = $obj->select($comp);
                                while ($row = mysqli_fetch_array($result)) { ?>
                                <option value="<?= htmlspecialchars($row["id"]) ?>"  >
                                    <?= htmlspecialchars($row["stage"]) ?>
                                </option>
                                <?php } ?>
                            </select>
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
function readURL_multiple(input) {
        $('#preview_file_div').html(""); // Clear previous preview
        var filesAmount = input.files.length;
        for (let i = 0; i < filesAmount; i++) {
            if (input.files && input.files[i]) {
                var filename = input.files[i].name;
                var extn = filename.split(".").pop().toLowerCase();

                if (["pdf", "doc", "docx","xlsx","jpg","png","jpeg","bmp","txt"].includes(extn)) {
                    document.getElementById('save').disabled = false; // Enable save button if valid file

                    // Display file name with a delete "X" button
                    $('#preview_file_div').append('<p id="file_' + i + '">' + filename +
                        ' <button type="button" class="btn btn-danger btn-sm" onclick="deleteFile(' + i + ')">' +
                        '<i class="bi bi-x-circle"></i></button></p>');
                } else {
                    $('#preview_file_div').html("Please select a valid file (PDF, DOC, and DOCX)");
                    document.getElementById('save').disabled = true;
                    break; // Stop the loop for invalid file
                }
            }
        }
    }
    function deleteFile(index) {
        $('#file_' + index).remove(); // Remove the corresponding file preview
        // Optionally, you can manage the files array here to keep track of files to be uploaded
        // For example, you could store the remaining file names in a hidden input if needed
    }
</script>
<?php
include "footer_intern.php";
?>