<?php
include "header.php";
include "alert.php";

error_reporting(E_ALL);

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = isset($_COOKIE['edit_id']) ? 'edit' : 'view';
    $Id = isset($_COOKIE['edit_id']) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM case_hist WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    $cno = $_COOKIE['c_no'];
}



if (isset($_REQUEST["save"])) {
    $tid = $_COOKIE["add_id"];
    $stage = $_REQUEST['stage'];
    $remark = $_REQUEST['remark'];
    $date = $_REQUEST['dos'];
    $status = $_REQUEST['radio'];

    $noti_type = "task_completed";
    $noti_msg = "Task has been completed";
    $noti_status = 1;
    $play_status = 1;


    //get case data

    $stmt_case = $obj->con1->prepare("select * from `case` where case_no=?");
    $stmt_case->bind_param("s", $_COOKIE['c_no']);
    $stmt_case->execute();
    $Resp_case = $stmt_case->get_result()->fetch_assoc();

    try {
        // Insert the new record into the case_hist table
        $stmt = $obj->con1->prepare("INSERT INTO case_hist(`task_id`, `stage`, `remarks`, `dos`,`status`,`added_by`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issssi", $tid, $stage, $remark, $date, $status, $_SESSION['id']);
        $Resp = false;
        if ($stmt->execute()) {
            $Resp = true; // Set to true if the statement was executed successfully
        } else {
            // Handle the error
            throw new Exception("Problem in adding! " . strtok($obj->con1->error, "("));
        }

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
                $added_by = $_SESSION["id"];

                $stmt_image = $obj->con1->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`,`added_by`) VALUES (?, ?,?)");
                $stmt_image->bind_param("isi", $Resp_case["id"], $SubImageName, $added_by);
                $Resp_img = $stmt_image->execute();
                $stmt_image->close();

            }
        }


        if (!$Resp) {
            throw new Exception("Problem in adding! " . strtok($obj->con1->error, "("));
        }
        $stmt->close();

        // Update the status of the associated task in the task table
        $updateStmt = $obj->con1->prepare("UPDATE `task` SET `status` = ? WHERE id = ?");
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

        if ($status == "completed") {

            $stmt_task = $obj->con1->prepare("select * from `task` where id=?");
            $stmt_task->bind_param("i", $tid);
            $stmt_task->execute();
            $Resp_task = $stmt_task->get_result()->fetch_assoc();
            $stmt_task->close();

            //add into notification tbl

            // echo "INSERT INTO notification (`task_id`, `type`, `sender_id`,`receiver_id`, `msg`, `sender_type`,`receiver_type`, `status`, `playstatus`) VALUES ('$tid','$noti_type', '".$_SESSION["id"]."','".$Resp_task["alloted_by"]."', '$noti_msg', '$sender_type','$receiver_type', '$noti_status', '$play_status')";

            $stmt_noti = $obj->con1->prepare("INSERT INTO `notification` (`task_id`, `type`, `sender_id`,`receiver_id`, `msg`, `status`, `playstatus`) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt_noti->bind_param("isiisii", $tid, $noti_type, $_SESSION["id"], $Resp_task["alloted_by"], $noti_msg, $noti_status, $play_status);
            $Resp_noti = $stmt_noti->execute();
            $stmt_noti->close();
        }
        setcookie("msg", "data", time() + 3600, "/");
        header("location:case_hist_add.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case_hist_add.php");
    }
}

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $remark = $_REQUEST['remark'];
    $date = $_REQUEST['dos'];
    $status = $_REQUEST['radio'];
    $tid = $_COOKIE["add_id"];

    $noti_type = "task_completed";
    $noti_msg = "Task has been completed";
    $noti_status = 1;
    $play_status = 1;



    try {
        $stmt = $obj->con1->prepare("UPDATE `case_hist` SET `remarks`=?,`dos`=?,`status`=? WHERE `id`=?");
        $stmt->bind_param("sssi", $remark, $date, $status, $e_id);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in updating! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();

        // Update the status of the associated task in the task table
        $updateStmt = $obj->con1->prepare("UPDATE `task` SET `status` = ? WHERE id = ?");
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

        if ($status == "completed") {

            $stmt_task = $obj->con1->prepare("select * from `task` where id=?");
            $stmt_task->bind_param("i", $tid);
            $stmt_task->execute();
            $Resp_task = $stmt_task->get_result()->fetch_assoc();
            $stmt_task->close();

            //add into notification tbl

            // echo "INSERT INTO notification (`task_id`, `type`, `sender_id`,`receiver_id`, `msg`, `sender_type`,`receiver_type`, `status`, `playstatus`) VALUES ('$tid','$noti_type', '".$_SESSION["id"]."','".$Resp_task["alloted_by"]."', '$noti_msg', '$sender_type','$receiver_type', '$noti_status', '$play_status')";

            $stmt_noti = $obj->con1->prepare("INSERT INTO `notification` (`task_id`, `type`, `sender_id`,`receiver_id`, `msg`, `status`, `playstatus`) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt_noti->bind_param("isiisii", $tid, $noti_type, $_SESSION["id"], $Resp_task["alloted_by"], $noti_msg, $noti_status, $play_status);
            $Resp_noti = $stmt_noti->execute();
            $stmt_noti->close();
        }
        setcookie("edit_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");


    } else {
        setcookie("msg", "fail", time() + 3600, "/");


    }
    header("location:case_hist_add.php");
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
                <?php echo isset($mode) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Task for -
                <strong><?= $_COOKIE['c_no']; ?></strong>
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
                            <label for="stage" class="form-label">Current Stage</label>
                            <select class="form-control" id="stage" name="stage" disabled>
                                <option value="">Select a Stage</option>
                                <?php
                                $stmt_case = $obj->con1->prepare("select * from `case` where case_no=?");
                                $stmt_case->bind_param("s", $_COOKIE['c_no']);
                                $stmt_case->execute();
                                $Resp_case = $stmt_case->get_result()->fetch_assoc();
                                $stmt_case->close();

                                $comp = "SELECT * FROM stage";
                                $result = $obj->select($comp);
                                while ($row = mysqli_fetch_array($result)) { ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= ($Resp_case["stage"] == $row["id"]) ? "selected" : "" ?>>
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
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>required>
                        </div>

                        <div class="col-md-12">
                            <label for="dos" class="form-label">Remark Date</label>
                            <input type="date" class="form-control" id="dos" name="dos"
                                value="<?php echo (isset($mode) && isset($data['dos']) && !empty($data['dos'])) ? date('Y-m-d', strtotime($data['dos'])) : date('Y-m-d'); ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>

                        <div class="col-md-12" <?php echo isset($mode) ? 'hidden' : '' ?>>
                            <label for="docs" class="form-label">Documents</label>
                            <input type="file" class="form-control mb-3" id="docs" name="docs[]"
                                onchange="readURL_multiple(this)" multiple>
                            <div id="preview_file_div" style="color:blue"></div>
                        </div>


                        <div class="col-md-6">
                            <label for="inputEmail5" class="form-label">Status</label> <br />

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio1" value="pending" <?php
                                echo (isset($data) && isset($data['status']) && $data['status'] == 'pending') ? 'checked' : 'checked';
                                echo (isset($mode) && $mode == 'view') ? ' disabled' : '';
                                ?>    required />
                                <label class="form-check-label" for="radio1">Pending</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="radio" id="radio2" value="completed" <?php
                                echo (isset($data) && isset($data['status']) && $data['status'] == 'completed') ? 'checked' : '';
                                echo (isset($mode) && $mode == 'view') ? ' disabled' : '';
                                ?>    required />
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
                    <script>
                        // Before submitting the form, enable the disabled select element
                        document.querySelector('form').addEventListener('submit', function () {
                            document.getElementById('stage').removeAttribute('disabled');
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
    .status-label {
        display: inline-block;
        padding: 6px 14px;
        font-size: 18px;
        font-weight: 700;
        min-width: 120px;
        /* consistent width */
        text-align: center;
        border-radius: 20px;
        text-transform: capitalize;
    }

    .bg-light-green {
        background-color: rgb(70, 191, 33);
        color: white;
    }
</style>
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr no.</th>
                                <th scope="col">Stage</th>
                                <th scope="col">Remarks</th>
                                <th scope="col">Remark Date</th>
                                <th scope="col">Added by</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // $stmt = $obj->con1->prepare("SELECT * FROM case_hist inner join task on case_hist.task_id = task.id where task_id = '$id' order by case_hist.id DESC");
                            $stmt = $obj->con1->prepare("SELECT case_hist.*,staff.name, stage.stage as stage_name , date_format(dos,'%d-%m-%Y') as rd FROM case_hist inner join staff on case_hist.added_by = staff.id inner join task on case_hist.task_id = task.id inner join `stage`on case_hist.stage = stage.id  where case_hist.task_id = ? and {$_COOKIE['task']} = ? order by case_hist.id DESC");
                            $stmt->bind_param("ii", $_COOKIE["add_id"], $_SESSION['id']);
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                                <tr>

                                    <th scope="row"><?php echo $i; ?></th>
                                    <td><?php echo $row["stage_name"] ?></td>
                                    <td><?php echo $row["remarks"] ?></td>
                                    <td><?php echo $row["rd"] ?></td>
                                    <td><?php echo $row["name"] ?></td>
                                    <td>
                                        <h4><span
                                                class="status-label badge rounded-pill bg-<?php echo ($row['status'] == 'completed') ? 'light-green' : 'warning' ?>"><?php echo ucfirst($row["status"]); ?></span>
                                        </h4>
                                    </td>
                                    <td><?php
                                    if ($row['added_by'] == $_SESSION["id"]) { ?>
                                            <a href="javascript:editdata('<?php echo $row["id"] ?>')">
                                                <i class="bx bx-edit-alt bx-sm me-2 text-success"></i>
                                            </a>

                                        <?php } else {
                                        echo "-";
                                    }
                                    ?>
                                    </td>
                                    <?php $i++;
                            } ?>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-left mt-4">
                        <button type="button" class="btn btn-danger" onclick="javascript: go_back() ;">
                            Close</button>
                    </div>
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
        eraseCookie("c_no");
        eraseCookie("task");
        //window.history.back();
        window.location = "task_alloted_to_me.php";
    }
    function editdata(id) {
        eraseCookie("view_id");
        createCookie("edit_id", id, 1);
        window.location = "case_hist_add.php";

    }
    function readURL_multiple(input) {
        $('#preview_file_div').html(""); // Clear previous preview
        var filesAmount = input.files.length;
        for (let i = 0; i < filesAmount; i++) {
            if (input.files && input.files[i]) {
                var filename = input.files[i].name;
                var extn = filename.split(".").pop().toLowerCase();

                if (["pdf", "doc", "docx", "xlsx", "jpg", "png", "jpeg", "bmp", "txt"].includes(extn)) {
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
include "footer.php";
?>