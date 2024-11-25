<?php
include "header_intern.php";
include "alert.php";
?>
<script type="text/javascript">
function add_data(id, cno) {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    createCookie("add_id", id, 1);
    createCookie("case_no", cno, 1);
    window.location = "case_hist_add.php";
}

function assign_task(id, cno) {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    createCookie("assign_id", id, 1);
    createCookie("case_id", cno, 1);
    window.location = "task_assign.php";
}

function editdata(id) {
    eraseCookie("view_id");
    createCookie("edit_id", id, 1);
    window.location = "case_hist_add.php";
}

function viewdata(id, cno) {
    eraseCookie("edit_id");
    createCookie("view_id", id, 1);
    createCookie("case_no", cno, 1);
    window.location = "task_intern_view.php";
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
            <form method="post" action="case.php">
                <input type="hidden" name="delete_id" id="delete_id">
                <div class="modal-body">
                    Are you sure you want to delete this record?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="btndelete" id="btndelete">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Basic Modal-->

<div class="pagetitle">
    <h1>New Task</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index_intern.php">Home</a></li>
            <li class="breadcrumb-item">New Task</li>
            <li class="breadcrumb-item active">Data</li>
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
                                <th scope="col">Case No</th>
                                <th scope="col">Instruction</th>
                                <th scope="col">Alloted by</th>
                                <th scope="col">Alloted Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $intern_id = $_SESSION['intern_id'];
                        $stmt = $obj->con1->prepare("SELECT t.*,c.case_no,date_format(t.alloted_date,'%d-%m-%Y') as adt,CASE WHEN t.action_by = 'intern' THEN i.name WHEN t.action_by = 'advocate' THEN a.name ELSE 'Unknown' END AS alloted_by_name, it.name AS alloted_to_name FROM  task t LEFT JOIN  interns i ON t.alloted_by = i.id AND t.action_by = 'intern' LEFT JOIN  advocate a ON t.alloted_by = a.id AND t.action_by = 'advocate' LEFT JOIN `case` c ON t.case_id = c.id LEFT JOIN 
    interns it ON t.alloted_to = it.id where  t.alloted_to =? ORDER BY t.id DESC;");                            
                            $stmt->bind_param("i",$intern_id);            
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                            <tr>

                                <th scope="row"><?php echo $i; ?></th>
                                <td><?php echo $row["case_no"] ?></td>
                                <td><?php echo $row["instruction"] ?></td>
                                <td><?php echo $row["alloted_by_name"] ?></td>
                                <td><?php echo $row["adt"] ?></td>
                                <td>
                                    <h4>
                                        <span class="badge rounded-pill bg-<?php 
                                echo ($row['status'] == 'pending') ? 'warning' : 
                                    (($row['status'] == 'completed') ? 'success' :     
                                    (($row['status'] == 'allotted') ? 'primary' : 
                                    (($row['status'] == 'reassign') ? 'info' : 'danger'))); 
                            ?>">
                                            <?php echo ucfirst(str_replace("_","-",$row["status"])); ?>
                                        </span>
                                    </h4>
                                </td>




                                <td>
                                    <a href="javascript:viewdata('<?php echo $row["id"] ?>','<?php echo $row["case_no"] ?>')"><i
                                            class="bx bx-show-alt bx-sm me-2"></i> </a>
                                    <?php
                                        if($row["status"]!="re_alloted" && $row["status"]!="completed")
                                        {
                                            ?>
                                            <a href="javascript:add_data('<?php echo $row["id"] ?>','<?php echo $row["case_no"] ?>')"><i
                                            class="bi bi-plus-circle me-1  bx-sm me-2 text-success"></i> </a>
                                            <a href="javascript:assign_task('<?php echo $row["id"] ?>','<?php echo $row["case_id"] ?>')"><i
                                            class="bi bi-arrow-right-circle me-1  bx-sm me-2 text-danger"></i> </a>

                                            <?php

                                       }
                                    ?>        
                                    

                                    

                                </td>
                            </tr>
                            <?php $i++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
include "footer_intern.php";
?>