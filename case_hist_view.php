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
                <h5 class="card-title">Case No : <?php echo $data["case_no"]?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Company : <?php echo $data["name"]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Case Type : <?php echo $data["case_type"]?></h5>
                    <table class="table datatable">
                        <thead>
                            <tr>
                            <th scope="col">Sr. no.</th>
                                <th scope="col">Intern</th>
                                <th scope="col">Advocate</th>
                                
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
                            // $stmt = $obj->con1->prepare("SELECT *, stage.stage as stage_name, interns.name as intern_name FROM `case_hist` inner join `task` on case_hist.task_id = task.id inner join `stage` on case_hist.stage = stage.id inner join `interns` on task.alloted_to = interns.id  where task_id = '$id' order by case_hist.id DESC");
             //               $stmt = $obj->con1->prepare("SELECT *, interns.name as intern_name ,stage.stage as stage_name from `case_hist` inner join `task` on task.id = case_hist.task_id inner join `case` on case.id = task.case_id inner join `stage` on case_hist.stage = stage.id inner join `interns` on task.alloted_to = interns.id where task.case_id = '$id' order by case_hist.id DESC");
                   $stmt = $obj->con1->prepare("SELECT c1.id,c1.case_no,case_hist.remarks,case_hist.status, date_format(`case_hist`.dos,'%d-%m-%Y') as fdos , date_format(`case_hist`.date_time,'%d-%m-%Y') as fdt , interns.name as intern_name ,stage.stage as stage_name , c1.case_no,advocate.name as advocate_name from `case_hist` inner join `task` on task.id = case_hist.task_id inner join `case` c1 on c1.id = task.case_id inner join `stage` on case_hist.stage = stage.id inner join `interns` on task.alloted_to = interns.id inner join advocate on advocate.id = task.alloted_by where task.case_id = ? order by case_hist.id DESC");
                   $stmt->bind_param("i",$id);
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $i = 1;
                            while ($row = mysqli_fetch_array($Resp)) { ?>
                            <tr>

                                <th scope="row"><?php echo $i; ?></th>

                                <td ><?php echo $row["intern_name"] ?></td>
                                <td ><?php echo $row["advocate_name"] ?></td>
                                
                                <td ><?php echo $row["stage_name"] ?></td>
                                <td ><?php echo $row["remarks"] ?></td>
                                <td><?php echo $row["fdos"] ?></td>
                                <td><?php echo $row["fdt"] ?></td>
                                <td>
                                <h4><span
                                        class="badge rounded-pill bg-<?php echo ($row['status']=='completed')?'success':'warning'?>"><?php echo ucfirst($row["status"]); ?></span>
                                </h4>
                                </td>
                                <?php $i++;}?>
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