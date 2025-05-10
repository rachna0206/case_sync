<?php
include "header.php";
include "alert.php";


?>
<script type="text/javascript">

    function add_proceeding(case_id, case_no) {
        eraseCookie("edit_id");
        createCookie("view_id", case_id, 1);
        document.cookie = "case_id=" + case_id;
        document.cookie = "case_no=" + case_no;
        window.location = "add_proceeding.php";
    }
    function viewdata(id) {
        eraseCookie("edit_id");
        createCookie("view_id", id, 1);
        window.location = "case_hist_view.php";
    }
    function file_data(id) {
        eraseCookie("edit_id");
        eraseCookie("view_id", id, 1);
        createCookie("case_doc_id", id, 1);
        window.location = "case_files_advocates.php";
    }

    function deletedata(id) {
        $('#deleteModal').modal('toggle');
        $('#delete_id').val(id);
    }


</script>
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
            <li class="breadcrumb-item">Case Histroy</li>
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
                                <th scope="col">Parties</th>
                                <th scope="col">Company</th>
                                <th scope="col">Court</th>
                                <th scope="col">City</th>
                                <th scope="col">Case Counter</th>
                                <th scope="col">Summon Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // $stmt = $obj->con1->prepare("SELECT *, company.name as company_name, case_type.case_type as case_type_name, court.name as cname, city.name as city_name, task.id as task_id FROM `case` inner join `company` on case.company_id = company.id inner join `case_type` on case.case_type = case_type.id inner join `court` on court.id = case.court_name inner join `city` on city.id = case.city_id inner join `task` on task.case_id = case.id ORDER BY case.id DESC");
                            $stmt = $obj->con1->prepare("SELECT 
                                    `case`.*, 
                                    date_format(case.sr_date,'%d-%m-%Y') as smndt , 
                                    case.id as case_id,
                                    company.name as company_name, 
                                    court.name as cname, 
                                    city.name as city_name,
                                    45-DATEDIFF(CURRENT_DATE, case.sr_date) AS case_counter  
                                    FROM `case`  join `company` on case.company_id = company.id 
                                    join `court` on court.id = case.court_name     
                                    join `city` on city.id = case.city_id 
                                    ORDER BY case.id DESC");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) {
                                if ($row['status'] == 'disposed') {
                                    $class = "danger";
                                } else if ($row['status'] == 'pending') {
                                    $class = "warning";
                                } else {
                                    $class = "light-green";
                                } ?>
                                <tr>

                                    <th scope="row"><?php echo $i; ?></th>
                                    <td><?php echo $row["case_no"] ?></td>
                                    <td><?php echo $row["applicant"] ?> vs <?php echo $row["opp_name"] ?></td>
                                    <td><?php echo $row["company_name"] ?></td>
                                    <td><?php echo $row["cname"] ?></td>
                                    <td><?php echo $row["city_name"] ?></td>
                                    <td><?php echo $row["case_counter"] ?></td>
                                    <td><?php echo $row["smndt"] ?></td>
                                    <td>
                                        <h4><span
                                                class="status-label badge rounded-pill bg-<?php echo $class ?>"><?php echo ucfirst($row["status"]); ?></span>
                                        </h4>
                                    </td>

                                    <td>
                                        <a href="javascript:viewdata('<?php echo $row["case_id"] ?>')"><i
                                                class="bx bx-show-alt bx-sm me-2"></i> </a>
                                        <a href="javascript:file_data('<?php echo $row["case_id"] ?>')"><i
                                                class="bi bi-file-earmark-text  bx-sm me-2 text-success"></i> </a>
                                        <a
                                            href="javascript:add_proceeding('<?php echo $row['case_id'] ?>','<?= $row['case_no'] ?>')">
                                            <i class="bi-stopwatch" style='color: grey; font-size: 24px;'
                                                title="Proceed"></i></a>

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

<?php
include "footer.php";
?>