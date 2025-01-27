<?php
include "header_intern.php";
include "alert.php";;

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT c1.case_no,c2.name,c3.case_type, a1.name FROM `case` c1, company c2,case_type c3, advocate a1 WHERE c1.company_id=c2.id and c1.case_type=c3.id and c1.handle_by = a1.id and c1.id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}


?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Case History</h1>
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
                    <h5 class="card-title">Case No : <?php echo $data["case_no"] ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Company : <?php echo $data["name"] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Case Type : <?php echo $data["case_type"] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Advocate : <?php echo $data["name"] ?></h5>


                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr. no.</th>
                                <th scope="col">Intern</th>
                                <th scope="col">Task Instruction</th>
                                <th scope="col">Stage</th>
                                <th scope="col">Remark</th>
                                <th scope="col">Remark Date</th>
                                <th scope="col">System Date</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id = $_COOKIE["view_id"];
                            $intern_id = $_SESSION['intern_id'];
                            $stmt = $obj->con1->prepare("SELECT `case_hist`.*, task.instruction, DATE_FORMAT(`case_hist`.dos, '%d-%m-%Y') AS fdos, DATE_FORMAT(`case_hist`.date_time, '%d-%m-%Y') AS fdt, interns.name AS intern_name, stage.stage AS stage_name, `case`.case_no, advocate.name AS advocate_name FROM `case_hist` INNER JOIN `task` ON task.id = `case_hist`.task_id INNER JOIN `case` ON `case`.id = task.case_id INNER JOIN `stage` ON `case_hist`.stage = stage.id INNER JOIN `interns` ON task.alloted_to = interns.id INNER JOIN advocate ON advocate.id = task.alloted_by WHERE task.case_id=? ORDER BY `case_hist`.id DESC;");
                            $stmt->bind_param("i", $Id);

                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                                <tr>
                                    <th scope="row"><?php echo $i; ?></th>
                                    <td><?php echo $row["intern_name"] ?></td>
                                    <td><?php echo $row["instruction"] ?></td>
                                    <td><?php echo $row["stage_name"] ?></td>
                                    <td><?php echo $row["remarks"] ?></td>
                                    <td><?php echo $row["fdos"] ?></td>
                                    <td><?php echo $row["fdt"] ?></td>
                                    <td>
                                        <h4>
                                            <span class="badge rounded-pill bg-<?php echo ($row['status'] == 'completed') ? 'success' : 'warning' ?>">
                                                <?php echo ucfirst($row["status"]); ?>
                                            </span>
                                        </h4>
                                    </td>
                                </tr>
                            <?php
                                $i++;
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