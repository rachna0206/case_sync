<?php 
 include "header_intern.php";
 include "alert.php";
?>
<script type="text/javascript">
function viewdata(id) {
    eraseCookie("edit_id");
    createCookie("view_id", id, 1);
    window.location = "case_intern_hist_view.php";
}

function file_data(id) {
    eraseCookie("edit_id");
    eraseCookie("view_id", id, 1);
    createCookie("case_id", id, 1);
    window.location = "case_files_intern.php";
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
            <form method="post" action="case_hist.php">
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
    <h1>Case History  <span></span></h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case Histroy</li>
            <li class="breadcrumb-item active">Data</li>
        </ol>
    </nav>
    <button type="button" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Next Date & Stage</button>
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
                                <th scope="col">Company</th>
                                <th scope="col">Court</th>
                                <th scope="col">City</th>
                                <th scope="col">Summon Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                           
                                $alloted_to_value = $_SESSION['intern_id']; // Get the ID of the logged-in intern

                                $stmt = $obj->con1->prepare("SELECT `case`.*, DATE_FORMAT(`case`.sr_date, '%d-%m-%Y') AS smndt, `case`.id AS case_id, company.name AS company_name, case_type.case_type AS case_type_name, court.name AS cname, city.name AS city_name FROM `case` INNER JOIN `company` ON `case`.company_id = company.id INNER JOIN `case_type` ON `case`.case_type = case_type.id INNER JOIN `court` ON court.id = `case`.court_name INNER JOIN `city` ON city.id = `case`.city_id WHERE `case`.id IN (SELECT DISTINCT case_id FROM `task` WHERE alloted_to = ?) ORDER BY `case`.id DESC;");

                                // Bind the parameter
                                $stmt->bind_param("i", $alloted_to_value);

                                $stmt->execute();
                                $Resp = $stmt->get_result();
                                $i = 1;

                            while ($row = mysqli_fetch_array($Resp)) { ?>
                            <tr>

                                <th scope="row"><?php echo $i; ?></th>
                                <td><?php echo $row["case_no"] ?></td>

                                <td><?php echo $row["company_name"] ?></td>
                                <td><?php echo $row["cname"] ?></td>
                                <td><?php echo $row["city_name"] ?></td>
                                <td><?php echo $row["smndt"] ?></td>
                                <td>
                                    <h4><span
                                            class="badge rounded-pill bg-<?php echo ($row['status']=='pending')?'warning':'primary'?>"><?php echo ucfirst($row["status"]); ?></span>
                                    </h4>
                                </td>

                                <td>
                                    <a href="javascript:viewdata('<?php echo $row["case_id"]?>')"><i
                                            class="bx bx-show-alt bx-sm me-2"></i> </a>
                                    <a href="javascript:file_data('<?php echo $row["case_id"] ?>')"><i
                                            class="bi bi-file-earmark-text  bx-sm me-2 text-success"></i> </a>
                                </td>



                                <?php $i++;}?>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "case_hist.php";
}

</script>

<?php
include "footer_intern.php";
?>