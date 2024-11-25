<?php 
 include "header.php";
 include "alert.php";

 if (isset($_REQUEST["btndelete"])) {
    $c_id = $_REQUEST['delete_id'];
 
     try {
         $stmt_subimg = $obj->con1->prepare("SELECT * FROM `case` WHERE id=?");
         $stmt_subimg->bind_param("i",$c_id);
         $stmt_subimg->execute();
         $Resp_subimg = $stmt_subimg->get_result()->fetch_assoc();
         $stmt_subimg->close();
 
         if (file_exists("documents/case" . $Resp_subimg["docs"])) {
             unlink("documents/case" . $Resp_subimg["docs"]);
         }
 
         $stmt_del = $obj->con1->prepare("DELETE FROM `case` WHERE id=?");
         $stmt_del->bind_param("i", $c_id);
         $Resp = $stmt_del->execute();
         if (!$Resp) {
             throw new Exception("Problem in deleting! " . strtok($obj->con1->error,  '('));
         }
         $stmt_del->close();
     } catch (\Exception $e) {
         setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
     }
 
     if ($Resp) {
         setcookie("msg", "data_del", time() + 3600, "/");
     }
     header("location:case.php");
  }
?>
<script type="text/javascript">
function add_data() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "case_add.php";
}

function editdata(id) {
    eraseCookie("view_id");
    createCookie("edit_id", id, 1);
    window.location = "case_add.php";
}

function viewdata(id) {
    eraseCookie("edit_id");
    createCookie("view_id", id, 1);
    window.location = "case_add.php";
}

function deletedata(id,case_no) {
    $('#deleteModal').modal('toggle');
    $('#delete_id').val(id);
    $('#delete_record').html(case_no);
}

function addmuldocs(id) {
    eraseCookie("view_id");
    eraseCookie("edit_muldocs_id");
    eraseCookie("view_muldocs_id");
    createCookie("edit_id", id, 1);
    window.location = "case_mul_doc.php";
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
                    Are you sure you really want to delete Case No: "<span id="delete_record"></span>" ?
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
    <h1>Case</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case</li>
            <li class="breadcrumb-item active">Data</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <a href="javascript:add_data()"><button type="button" class="btn btn-success"><i class="bi bi-plus me-1"></i> Add</button></a>
                    </div>
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr no.</th>
                                <th scope="col">Case No</th>
                                <th scope="col">Case Year</th>
                                <th scope="col">Case Type</th>
                                <th scope="col">Company</th>
                                <th scope="col">Handle By</th>
                                <th scope="col">Applicant</th>
                                <th scope="col">Opponent</th>
                                <th scope="col">Court</th>
                                <th scope="col">City</th>
                                <th scope="col">Summon Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $obj->con1->prepare("SELECT *, case.id as case_id, company.name as company_name, case_type.case_type as case_type_name, court.name as cname, city.name as city_name, advocate.name as adv_name FROM `case` inner join `company` on case.company_id = company.id inner join `case_type` on case.case_type = case_type.id inner join `court` on court.id = case.court_name inner join `city` on city.id = case.city_id inner join `advocate` on case.handle_by = advocate.id ORDER BY case.id DESC");
                            // $stmt = $obj->con1->prepare("SELECT t1.name, t2.* FROM `company` t1, `case` t2 where t1.id = t2.company_id ORDER BY t2.id DESC");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                            <tr>

                                <th scope="row"><?php echo $i; ?></th>
                                <td ><?php echo $row["case_no"] ?></td>

                                <td ><?php echo $row["year"] ?></td>
                                <td ><?php echo $row["case_type_name"] ?></td>
                                <td ><?php echo $row["company_name"] ?></td>
                                <td><?php echo $row["adv_name"] ?></td>
                                <td><?php echo $row["applicant"] ?></td>
                                <td><?php echo $row["opp_name"] ?></td>
                                <td><?php echo $row["cname"] ?></td>
                                <td><?php echo $row["city_name"] ?></td>
                                <td><?php echo $row["sr_date"] ?></td>
                                <td>
                                <h4><span
                                        class="badge rounded-pill bg-<?php echo ($row['status']=='Enable')?'success':'danger'?>"><?php echo $row["status"]; ?></span>
                                </h4>
                                </td>

                                <td>
                                <a href="javascript:addmuldocs('<?php echo $row["case_id"]?>');"><i
                                class="bx bx-add-to-queue bx-sm me-2"></i></a>
                                    <a href="javascript:viewdata('<?php echo $row["case_id"]?>')"><i
                                            class="bx bx-show-alt bx-sm me-2"></i> </a>
                                    <a href="javascript:editdata('<?php echo$row["case_id"]?>')"><i
                                            class="bx bx-edit-alt bx-sm me-2 text-success"></i> </a>
                                    <a href="javascript:deletedata('<?php echo $row["case_id"]?>','<?php echo $row["case_no"] ?>')"><i
                                            class="bx bx-trash bx-sm me-2 text-danger"></i> </a>
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
include "footer.php";
?>