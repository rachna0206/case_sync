<?php
include "header.php";
include "alert.php";

if (isset($_REQUEST["btndelete"])) {
    $id = $_REQUEST['delete_id'];
    try {
        $stmt_del = $obj->con1->prepare("DELETE FROM `task` WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            throw new Exception("Problem in deleting! " . strtok($obj->con1->error, '('));
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data_del", time() + 3600, "/");
        header("location:task.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:task.php");
    }
}
?>
<script type="text/javascript">
    function add_data() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "task_add.php";
    }

    function editdata(id) {
        eraseCookie("view_id");
        createCookie("edit_id", id, 1);
        window.location = "task_add.php";
    }

    function viewdata(id) {
        eraseCookie("edit_id");
        createCookie("view_id", id, 1);
        window.location = "task_add.php";
    }

    function deletedata(id) {
        $('#deleteModal').modal('toggle');
        $('#delete_id').val(id);

    }
</script>
<!-- Basic Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="task.php">
                <input type="hidden" name="delete_id" id="delete_id">
                <div class="modal-body">
                    Are you sure you really want to delete this record?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="btndelete" id="btndelete">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="pagetitle">
    <h1>Task</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Task</li>
            <li class="breadcrumb-item active">Data</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">



            <div class="card">
                <div class="card-body">
                    <div class="card-title row">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success" onclick="javascript:add_data()"><i
                                        class="bi bi-plus me-1"></i> Add</button>
                        </div>
                        <div class="col-md-3">
                            <label for="title" class="col-form-label">Date</label>
                            <input type="date" name="dtxt" id="dtxt" onchange="get_data(this.value)"
                                value="<?php echo isset($_COOKIE["filter_task_date"]) ? $_COOKIE["filter_task_date"] : "" ?>">
                        </div>

                    </div>
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr no.</th>
                                <th scope="col">Case No</th>
                                <th scope="col">Alloted By</th>
                                <th scope="col">Alloted To</th>
                                <th scope="col">Alloted Date</th>
                                <th scope="col">Expected End Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            if (isset($_COOKIE["filter_task_date"]) && $_COOKIE["filter_task_date"] != "") {
                                $stmt = $obj->con1->prepare("SELECT t.*, c.case_no, date_format(t.alloted_date, '%d-%m-%Y') as adt, s.name AS alloted_by_name, it.name AS alloted_to_name FROM task t LEFT JOIN staff s ON t.alloted_by = s.id LEFT JOIN `case` c ON t.case_id = c.id LEFT JOIN staff it ON t.alloted_to = it.id WHERE t.alloted_date = ? ORDER BY t.id DESC;");
                                $stmt->bind_param("s", $_COOKIE["filter_task_date"]);
                                setcookie("filter_task_date", "", time() - 3600, "/");
                            } else {
                                $stmt = $obj->con1->prepare("SELECT t.*, c.case_no, date_format(t.alloted_date, '%d-%m-%Y') as adt, s.name AS alloted_by_name, it.name AS alloted_to_name FROM task t LEFT JOIN staff s ON t.alloted_by = s.id LEFT JOIN `case` c ON t.case_id = c.id LEFT JOIN staff it ON t.alloted_to = it.id ORDER BY t.id DESC;");
                            }
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;

                            while ($row = mysqli_fetch_array($Resp)) { ?>
                                <tr>

                                    <th scope="row"><?php echo $i; ?></th>
                                    <td scope="row"><?php echo $row["case_no"] ?></td>
                                    <td scope="row"><?php echo $row["alloted_by_name"] ?></td>
                                    <td scope="row"><?php echo $row["alloted_to_name"] ?></td>
                                    <td scope="row"><?php echo $row["adt"] ?></td>
                                    <td scope="row"><?php echo $row["expected_end_date"] ?></td>
                                    <td>
                                        <h4>
                                            <span class="badge rounded-pill bg-<?php
                                            echo ($row['status'] == 'pending') ? 'warning' :
                                                (($row['status'] == 'completed') ? 'success' :
                                                    (($row['status'] == 'allotted') ? 'primary' :
                                                        (($row['status'] == 'reassign') ? 'info' : 'danger')));
                                            ?>">
                                                <?php echo ucfirst(str_replace("_", "-", $row["status"])); ?>
                                            </span>
                                        </h4>
                                    </td>



                                    <td>
                                        <a href="javascript:viewdata('<?php echo $row["id"] ?>')"><i
                                                class="bx bx-show-alt bx-sm me-2"></i> </a>
                                        <a href="javascript:editdata('<?php echo $row["id"] ?>')"><i
                                                class="bx bx-edit-alt bx-sm me-2 text-success"></i> </a>
                                        <a href="javascript:deletedata('<?php echo $row["id"] ?>');"><i
                                                class="bx bx-trash bx-sm me-2 text-danger"></i> </a>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">

    function get_data(date) {
        console.log("date=" + date);
        createCookie("filter_task_date", date, 1);
        window.location = window.location.href;
    }
</script>

<?php
include "footer.php";
?>