<?php
include "header_intern.php";
error_reporting(E_ALL);

if (isset($_REQUEST["save"])) {
    $remark = $_REQUEST['remark'];
    $nextdate = $_REQUEST['next_date'];
    $next_stage = $_REQUEST['next_stage'];

    $stmt_case = $obj->con1->prepare("select * from `case` where case_no=?");
    $stmt_case->bind_param("s", $_COOKIE['case_no']);
    $stmt_case->execute();
    $Resp_case = $stmt_case->get_result()->fetch_assoc();

    try {
        $stmt = $obj->con1->prepare("INSERT ");
        $stmt->bind_param("isssss", $tid, $stage, $remark, $date, $nextdate, $status);
        $Resp = $stmt->execute();


        $stmt_case = $obj->con1->prepare("UPDATE `case` SET `stage`=?,`next_date`=?,`next_stage`=? WHERE id=?");
        $stmt_case->bind_param("isii", $stage, $nextdate, $next_stage, $Resp_case["id"]);
        $Resp_case_update = $stmt_case->execute();

        $stmt_case->close();

        foreach ($_FILES["docs"]['name'] as $key => $value) {
            if ($_FILES["docs"]['name'][$key] != "") {
                $PicSubImage = $_FILES["docs"]["name"][$key];
                $SubImageName = generateUniqueFileName("documents/case/", $PicSubImage);
                $SubImageTemp = $_FILES["docs"]["tmp_name"][$key];
                $SubImageName = str_replace(' ', '_', $SubImageName);

                move_uploaded_file($SubImageTemp, "documents/case/" . $SubImageName);
                $added_by = $_SESSION["intern_id"];

                $stmt_image = $obj->con1->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`,`added_by`) VALUES (?, ?,?)");
                $stmt_image->bind_param("isis", $Resp_case["id"], $SubImageName, $added_by);
                $Resp_img = $stmt_image->execute();
                $stmt_image->close();

            }
        }


        if (!$Resp) {
            throw new Exception("Problem in adding! " . strtok($obj->con1->error, "("));
        }
        $stmt->close();

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

        if ($status == "completed") {

            $stmt_task = $obj->con1->prepare("SELECT * from `task` where id=?");
            $stmt_task->bind_param("i", $tid);
            $stmt_task->execute();
            $Resp_task = $stmt_task->get_result()->fetch_assoc();
            $stmt_task->close();

            $stmt_noti = $obj->con1->prepare("INSERT INTO `notification` (`task_id`, `type`, `sender_id`,`receiver_id`, `msg`,  `status`, `playstatus`) VALUES (?, ?, ?, ?, ?, ?,  ?)");

            $stmt_noti->bind_param("isiisii", $tid, $noti_type, $_SESSION["intern_id"], $Resp_task["alloted_by"], $noti_msg, $noti_status, $play_status);
            $Resp_noti = $stmt_noti->execute();
            $stmt_noti->close();
        }
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
        $stmt->bind_param("issssi", $tid, $stage, $remark, $date, $status, $e_id);
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

<div class="pagetitle">
    <h1>Task</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Task History</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Task for -
                <strong><?= $cno ?></strong>
            </li>
        </ol>
    </nav>
</div>
<!-- End Page Title -->

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
                                $stmt_case = $obj->con1->prepare("select * from `case_procedings` where case_id=? order by id desc limit 1");
                                $stmt_case->bind_param("s", $_COOKIE['case_no']);
                                $stmt_case->execute();
                                $Resp_case = $stmt_case->get_result()->fetch_assoc();
                                $stmt_case->close();

                                $comp = "SELECT * FROM stage";
                                $result = $obj->select($comp);
                                while ($row = mysqli_fetch_array($result)) { ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>">
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
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?> required>
                        </div>



                        <div class="col-md-12">
                            <label for="stage" class="form-label">Next Stage</label>
                            <select class="form-control" id="next_stage" name="next_stage" required>
                                <option value="">Select a Stage</option>
                                <?php
                                $stmt_case = $obj->con1->prepare("select * from `case` ");

                                $stmt_case->execute();
                                $Resp_case = $stmt_case->get_result()->fetch_assoc();
                                $stmt_case->close();

                                $comp = "SELECT * FROM stage";
                                $result = $obj->select($comp);
                                while ($row = mysqli_fetch_array($result)) { ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>">
                                        <?= htmlspecialchars($row["stage"]) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>



                        <div class="col-md-12">
                            <label for="dos" class="form-label">Next Date</label>
                            <input type="date" class="form-control" id="next_date" name="next_date" value="" <?php
                            echo isset($mode) && $mode == 'view' ? 'readonly' : '';
                            ?> required>
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
        $('#preview_file_div').html("");
        var filesAmount = input.files.length;
        for (let i = 0; i < filesAmount; i++) {
            if (input.files && input.files[i]) {
                var filename = input.files[i].name;
                var extn = filename.split(".").pop().toLowerCase();

                if (["pdf", "doc", "docx", "xlsx", "jpg", "png", "jpeg", "bmp", "txt"].includes(extn)) {
                    document.getElementById('save').disabled = false;

                    $('#preview_file_div').append('<p id="file_' + i + '">' + filename +
                        ' <button type="button" class="btn btn-danger btn-sm" onclick="deleteFile(' + i + ')">' +
                        '<i class="bi bi-x-circle"></i></button></p>');
                } else {
                    $('#preview_file_div').html("Please select a valid file (PDF, DOC, and DOCX)");
                    document.getElementById('save').disabled = true;
                    break;
                }
            }
        }
    }
    function deleteFile(index) {
        $('#file_' + index).remove();
    }
</script>
<?php
include "footer_intern.php";
?>