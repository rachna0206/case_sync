<?php 
  include "header_intern.php";
  include "alert.php";

  $id=isset($_COOKIE["case_id"])?$_COOKIE["case_id"]:"";

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
     header("location:case_files_intern.php");
  }
?>
<script type="text/javascript">
function deletedata(id, case_no) {
    $('#deleteModal').modal('toggle');
    $('#delete_id').val(id);
    $('#delete_record').html(case_no);
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
    <h1>Case Files</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case Files</li>
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
                                <th scope="col">Case Type</th>
                                <th scope="col">Case Files</th>
                                <!-- <th scope="col">Action</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $stmt = $obj->con1->prepare("SELECT c1.case_no,c2.case_type,c1.docs from `case` c1,case_type c2,task t WHERE c1.case_type=c2.id  and t.case_id= c1.id and t.alloted_to=?
                                union
                                SELECT c1.case_no,c2.case_type,m.docs from `case` c1,case_type c2,task t,multiple_doc m WHERE c1.case_type=c2.id  and t.case_id= c1.id and t.alloted_to=? and m.c_id=c1.id");
                                $stmt->bind_param("ii",$_SESSION["intern_id"],$_SESSION["intern_id"]);
                                $stmt->execute();
                                $Resp = $stmt->get_result();
                                $i = 1;
                                while ($row = mysqli_fetch_array($Resp)) { ?>
                            <tr>
                                <th scope="row"><?php echo $i; ?></th>
                                <td><?php echo $row["case_no"] ?></td>
                                <td><?php echo $row["case_type"] ?></td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <!-- Main Document -->
                                        <?php if (!empty($row["docs"])) { ?>
                                        <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                            <!-- Adds 4px space between each file -->
                                            <a href="documents/case/<?php echo $row["docs"] ?>"
                                                class="btn btn-primary me-2" download style="margin-right: 4px;">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <span><?php echo $row["docs"] ?></span>
                                        </div>
                                        <?php } ?>

                                        
                                    </div>
                                </td>
                            </tr>
                            <?php $i++;
                                }
                                ?>
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
    window.location = "case_hist_intern.php";
}

</script>

<?php
include "footer_intern.php";
?>