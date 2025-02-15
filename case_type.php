<?php 
 include "header.php";
 include "alert.php";

 if(isset($_REQUEST["btndelete"]))
{
  $id = $_REQUEST['delete_id'];
  try
  {
    $stmt_del = $obj->con1->prepare("DELETE FROM `case_type` WHERE id = ?");
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
    header("location:case_type.php");
  }
  else
  {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:case_type.php");
 }
}
?>
<script type="text/javascript">
function add_data() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "case_type_add.php";
}

function editdata(id) {
    eraseCookie("view_id");
    createCookie("edit_id", id, 1);
    window.location = "case_type_add.php";
}

function viewdata(id) {
    eraseCookie("edit_id");
    createCookie("view_id", id, 1);
    window.location = "case_type_add.php";
}

function deletedata(id,case_type) {
    $('#deleteModal').modal('toggle');
    $('#delete_id').val(id);
    $('#delete_record').html(case_type);
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
            <form method="post" action="case_type.php">
                <input type="hidden" name="delete_id" id="delete_id">
                <div class="modal-body">
                    Are you sure you want to delete "<span id="delete_record"></span>" Case Type record?
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
    <h1>Case Type</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case Type</li>
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
                                <th scope="col">Case Type</th>
                                <th scope="col">status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `case_type` where id!=0");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                            <tr>

                                <th scope="row"><?php echo $i; ?></th>
                                <td ><?php echo $row["case_type"] ?></td>
                                <td>
                                    <h4>
                                        <span
                                            class="badge rounded-pill bg-<?php echo ($row["status"] === "enable") ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($row["status"]); ?>
                                        </span>
                                    </h4>
                                </td>


                                <td>
                                    <a href="javascript:viewdata('<?php echo $row["id"]?>')"><i
                                            class="bx bx-show-alt bx-sm me-2"></i> </a>
                                    <a href="javascript:editdata('<?php echo$row["id"]?>')"><i
                                            class="bx bx-edit-alt bx-sm me-2 text-success"></i> </a>
                                    <a href="javascript:deletedata('<?php echo $row["id"]?>','<?php echo $row["case_type"]?>');"><i
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