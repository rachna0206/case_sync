<?php 
 include "header_intern.php";
 include "alert.php";

 if(isset($_REQUEST["btndelete"]))
{
  $id = $_REQUEST['delete_id'];
  try
  {
    $stmt_del = $obj->con1->prepare("DELETE FROM `task` WHERE id = ?");
    $stmt_del->bind_param("i",$id);
    $Resp=$stmt_del->execute();
    if(!$Resp)
    {
      throw new Exception("Problem in deleting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt_del->close();
  }
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "data_del",time()+3600,"/");
    header("location:case_intern.php");
  }
  else
  {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:case_intern.php");
 }
}
?>
<script type="text/javascript">
function add_data() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "case_intern_updt.php";
}

function editdata(id) {
    eraseCookie("view_id");
    createCookie("edit_id", id, 1);
    window.location = "case_intern_updt.php";
}

function viewdata(id) {
    eraseCookie("edit_id");
    createCookie("view_id", id, 1);
    window.location = "case_intern_updt.php";
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
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr no.</th>
                                <th scope="col">Case No</th>
                                <th scope="col">Alloted Date</th>
                                <th scope="col">Remarks</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // echo $_SESSION["id"];
                            // $stmt = $obj->con1->prepare("SELECT * FROM `case` t1, `task` t2 where t1.id = t2.case_id and `alloted_to` = {$_SESSION['id']} ORDER BY t2.id DESC");
                            $stmt = $obj->con1->prepare("SELECT t2* FROM `case` t1 JOIN `task` t2 ON t1.id = t2.case_id JOIN `stage` t3 ON t2.remark = t3.id WHERE t2.alloted_to = {$_SESSION['id']} ORDER BY t2.id DESC");
                            
                            // $stmt = $obj->con1->prepare("SELECT * FROM `case` t1, `task` t2 where t1.id = t2.case_id ORDER BY t2.id DESC");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                            <tr>

                                <th scope="row"><?php echo $i; ?></th>
                                <td ><?php echo $row["case_no"] ?></td>
                                <td><?php echo $row["alloted_date"] ?></td>
                                <td><?php echo $row["stage"] ?></td>
                                <td>
                                <h4><span
                                        class="badge rounded-pill bg-<?php echo ($row['status']=='enable')?'success':'danger'?>"><?php echo $row["status"]; ?></span>
                                </h4>
                                </td>

                                <td>
                                    <a href="javascript:viewdata('<?php echo $row["id"]?>')"><i
                                            class="bx bx-show-alt bx-sm me-2"></i> </a>
                                    <a href="javascript:editdata('<?php echo$row["id"]?>')"><i
                                            class="bx bx-edit-alt bx-sm me-2 text-success"></i> </a>
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