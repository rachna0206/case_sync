<?php
include "header_intern.php";
include "alert.php";


?>
<script type="text/javascript">


    function viewdata(id) {
        eraseCookie("edit_id");
        createCookie("view_id", id, 1);
        window.location = "case_hist_view.php";
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
    <h1>Case History</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Today's Cases</li>
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
                                <th scope="col">Company</th>
                                <th scope="col">Court</th>
                                <th scope="col">City</th>
                                <th scope="col">Hearing Date</th>
                                <th scope="col">Summon Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // $stmt = $obj->con1->prepare("SELECT *, company.name as company_name, case_type.case_type as case_type_name, court.name as cname, city.name as city_name, task.id as task_id FROM `case` inner join `company` on case.company_id = company.id inner join `case_type` on case.case_type = case_type.id inner join `court` on court.id = case.court_name inner join `city` on city.id = case.city_id inner join `task` on task.case_id = case.id ORDER BY case.id DESC");
                            $stmt = $obj->con1->prepare("SELECT `case`.*, date_format(case.sr_date,'%d-%m-%Y') as smndt ,date_format(case.next_date,'%d-%m-%Y') as nextdt , case.id as case_id, company.name as company_name, case_type.case_type as case_type_name, court.name as cname, city.name as city_name FROM `case` inner join `company` on case.company_id = company.id inner join `case_type` on case.case_type = case_type.id inner join `court` on court.id = case.court_name inner join `city` on city.id = case.city_id and case.next_date=CURRENT_DATE()");
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
                                    <td><?php echo $row["nextdt"] ?></td>
                                    <td><?php echo $row["smndt"] ?></td>
                                    <td>
                                        <h4><span
                                                class="badge rounded-pill bg-<?php echo ($row['status'] == 'pending') ? 'warning' : 'primary' ?>"><?php echo ucfirst($row["status"]); ?></span>
                                        </h4>
                                    </td>

                                    <td>

                                    </td>

                                    <?php $i++;
                            } ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    function file_data(id) {
        eraseCookie("edit_id");
        eraseCookie("view_id", id, 1);
        createCookie("case_id", id, 1);
        window.location = "case_files_advocates.php";
    }
</script>

<?php
include "footer.php";
?>