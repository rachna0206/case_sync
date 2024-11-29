<?php
 include "header_intern.php";
 include "alert.php";;

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM case_hist WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $tid = $_REQUEST['taskid'];
    $stage = $_REQUEST['stage'];
    $remark = $_REQUEST['remarks'];
    $date = $_REQUEST['dos'];
    $status = $_REQUEST['radio'];

    try {
       // echo "INSERT INTO company(company_name, contact_person,contact_num, status) VALUES (".$company_name.",".$contact_person.",".$contact_num.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO case_hist(task_id, stage,remarks,dos, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $tid,$stage,$remark,$date, $status);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        
        setcookie("msg", "data", time() + 3600, "/");
        header("location:case_hist.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case_hist.php");
    }

   
}

    

if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $tid = $_REQUEST['taskid'];
    $stage = $_REQUEST['stage'];
    $remark = $_REQUEST['remarks'];
    $date = $_REQUEST['dos'];
    $status = $_REQUEST['radio'];


    try {
        $stmt = $obj->con1->prepare("UPDATE case_hist SET task_id=?, stage=?,remarks=?,dos=?,status=? WHERE id=?");
        $stmt->bind_param("issssi",  $tid,$stage,$remark,$date, $status, $e_id);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in updating! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("edit_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
       
    }
    header("location:case_hist.php");
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
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>- Data</li>
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
            <th scope="col">Sr. no.</th>
            <th scope="col">Intern</th>
            <th scope="col">Advocate</th>
            <th scope="col">Case No.</th>
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
        $stmt = $obj->con1->prepare("SELECT 
            `case_hist`.*, 
            DATE_FORMAT(`case_hist`.dos, '%d-%m-%Y') AS fdos, 
            DATE_FORMAT(`case_hist`.date_time, '%d-%m-%Y') AS fdt, 
            interns.name AS intern_name, 
            stage.stage AS stage_name, 
            `case`.case_no, 
            advocate.name AS advocate_name  -- Updated to include advocate's name
        FROM 
            `case_hist` 
        INNER JOIN 
            `task` ON task.id = `case_hist`.task_id 
        INNER JOIN 
            `case` ON `case`.id = task.case_id 
        INNER JOIN 
            `stage` ON `case_hist`.stage = stage.id 
        INNER JOIN 
            `interns` ON task.alloted_to = interns.id 
        INNER JOIN 
            advocate ON advocate.id = task.alloted_by 
        WHERE 
            task.case_id=?
        ORDER BY 
            `case_hist`.id DESC");
        $stmt->bind_param("i", $Id);

        $stmt->execute();   
        $Resp = $stmt->get_result();
        $i = 1;
        while ($row = mysqli_fetch_array($Resp)) { ?>
        <tr>
            <th scope="row"><?php echo $i; ?></th>
            <td><?php echo $row["intern_name"] ?></td>
            <td><?php echo $row["advocate_name"] ?></td> <!-- Updated to advocate_name -->
            <td><?php echo $row["case_no"] ?></td>
            <td><?php echo $row["stage_name"] ?></td>
            <td><?php echo $row["remarks"] ?></td>
            <td><?php echo $row["fdos"] ?></td>
            <td><?php echo $row["fdt"] ?></td>
            <td>
                <h4>
                    <span class="badge rounded-pill bg-<?php echo ($row['status']=='completed') ? 'success' : 'danger' ?>">
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
                            <button type="submit"
                                name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'd-none' : '' ?>"><?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                            </button>
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