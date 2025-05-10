<?php
include "header_intern.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * from `task` inner join `case_hist` on task.id = case_hist.task_id  WHERE task.id = ? ORDER BY task.id DESC");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];

    $stmt1 = $obj->con1->prepare("SELECT * from `case_hist` WHERE task_id = ?");
    $stmt1->bind_param('i', $Id);
    $stmt1->execute();
    $data1 = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();

    $task_id = $data1['task_id'];
    $remarks = $data1['remarks'];
    $dos = $data1['dos'];
    $status = $data1['status'];
    $stage = $_REQUEST['stage'];

    try {
        // $stmt = $obj->con1->prepare("UPDATE `case_hist` SET `stage`=? WHERE `task_id`=?");
        $stmt = $obj->con1->prepare("INSERT INTO case_hist(task_id, stage,remarks,dos, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $task_id, $stage, $remarks, $dos, $status);
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
        header("location:task_alloted_to_me_intern.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:task_alloted_to_me_intern.php");
    }
}
?>
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
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Task</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index_intern.php">Home</a></li>
            <li class="breadcrumb-item">Task</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>-Data
            </li>
        </ol>
    </nav>
</div><!-- End Page Title -->
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id = $_COOKIE["view_id"];
                            // $stmt = $obj->con1->prepare("SELECT * FROM `case_hist` inner join `task` on case_hist.task_id = task.id where task_id = '$id' order by case_hist.id DESC");
                            $stmt = $obj->con1->prepare("SELECT case_hist.*, staff.name, stage.stage as stage_name , date_format(dos,'%d-%m-%Y') as rd FROM `case_hist` inner join staff on case_hist.added_by = staff.id inner join `task` on case_hist.task_id = task.id inner join `stage`on case_hist.stage = stage.id  where case_hist.task_id = '$id' and alloted_to = {$_SESSION['intern_id']} order by case_hist.id DESC");
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
                                    <?php $i++;
                            } ?>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-left mt-4">
                        <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
                            id="save"
                            class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'd-none' : '' ?>"><?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                        </button>
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
        window.location = "task_alloted_to_me_intern.php";
    }

</script>
<?php
include "footer_intern.php";
?>