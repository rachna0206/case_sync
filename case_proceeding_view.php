<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {

    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT c1.case_no,c2.name,c3.case_type FROM `case` c1, company c2,case_type c3 WHERE c1.company_id=c2.id and c1.case_type=c3.id and c1.id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}





?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Task Proceeding History</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case History</li>
            <li class="breadcrumb-item active">
                View - Data</li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <h5 class="card-title">Case No : <?php echo $data["case_no"] ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Company : <?php echo $data["name"] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Case Type :
                        <?php echo $data["case_type"] ?>
                    </h5>
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr. no.</th>
                                <th scope="col">Inserted By</th>
                                <th scope="col">Stage</th>
                                <th scope="col">Remark</th>
                                <th scope="col">Remark Date</th>
                                <th scope="col">System Date</th>
                                <!-- <th scope="col">Status</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id = $_COOKIE["view_id"];
                            $stmt = $obj->con1->prepare("SELECT cp.*,s.stage,st.name as inserted_by_name from case_procedings as cp join stage as s on s.id = cp.next_stage join staff as st on st.id = cp.inserted_by where cp.case_id = ? order by cp.id desc;");
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                                <tr>

                                    <th scope="row"><?php echo $i; ?></th>

                                    <td><?php echo $row["inserted_by_name"] ?></td>
                                    <td><?php echo $row["stage"] ?></td>
                                    <td><?php echo $row["remarks"] ?></td>
                                    <td><?php echo $row["next_date"] ?></td>
                                    <td><?php echo $row["date_of_creation"] ?></td>
                                    <!--<td>
                                        <h4><span
                                                class="badge rounded-pill bg-<?php //echo ($row['status'] == 'completed') ? 'success' : 'warning' ?>"><?php  // echo ucfirst($row["status"]); ?></span>
                                        </h4>
                                    </td>-->
                                    <?php $i++;
                            } ?>
                            </tr>
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
        window.location = "case_hist.php";
    }

</script>
<?php
include "footer.php";
?>