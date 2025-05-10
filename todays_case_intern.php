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
    <h1>Today's Cases</h1>
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
                            $stmt = $obj->con1->prepare("
                            SELECT 
                                `case`.*, 
                                DATE_FORMAT(case.sr_date, '%d-%m-%Y') AS smndt,
                                DATE_FORMAT(case.next_date, '%d-%m-%Y') AS nextdt, 
                                case.id AS case_id, 
                                company.name AS company_name, 
                                case_type.case_type AS case_type_name, 
                                court.name AS cname, 
                                city.name AS city_name,
                                temp_sequence.sequence AS priority_number
                            FROM `case`
                            INNER JOIN `company` ON case.company_id = company.id 
                            INNER JOIN `case_type` ON case.case_type = case_type.id 
                            INNER JOIN `court` ON court.id = case.court_name 
                            INNER JOIN `city` ON city.id = case.city_id 
                            LEFT JOIN temp_sequence ON case.id = temp_sequence.case_id 
                            WHERE case.next_date = CURRENT_DATE()
                            ORDER BY 
                                CASE WHEN temp_sequence.sequence IS NULL THEN 1 ELSE 0 END, 
                                temp_sequence.sequence ASC
                        ");

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

                                    <td><?php echo $row["company_name"] ?></td>
                                    <td><?php echo $row["cname"] ?></td>
                                    <td><?php echo $row["city_name"] ?></td>
                                    <td><?php echo $row["nextdt"] ?></td>
                                    <td><?php echo $row["smndt"] ?></td>
                                    <td>
                                        <h4><span
                                                class="status-label badge rounded-pill bg-<?php echo $class ?>"><?php echo ucfirst($row["status"]); ?></span>
                                        </h4>
                                    </td>

                                    <td>
                                        <?php
                                        // Check priority for this case
                                        $priority_stmt = $obj->con1->prepare("SELECT id, sequence, remark FROM temp_sequence WHERE case_id = ?");
                                        $priority_stmt->bind_param("i", $row["case_id"]);
                                        $priority_stmt->execute();
                                        $priority_result = $priority_stmt->get_result();
                                        $priority_data = $priority_result->fetch_assoc();

                                        if ($priority_data) {
                                            echo '<div class="priority-icon circle-priority" onclick="openPriorityPopup(' . $row["case_id"] . ', ' . $priority_data["sequence"] . ', \'' . addslashes($priority_data["remark"]) . '\', ' . $priority_data["id"] . ')">
        <span>' . $priority_data["sequence"] . '</span>
      </div>';
                                        } else {
                                            if ($row["nextdt"] == date("d-m-Y")) {
                                                echo '<div class="priority-icon">
                                    <a href="javascript:void(0)" onclick="openPriorityPopup(' . $row["case_id"] . ')" class="text-white">
                                        <i class="bi bi-plus"></i> Priority
                                    </a>
                                  </div>';
                                            }
                                        }
                                        ?>
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

<!-- Priority Modal -->
<div id="priorityModal"
    style="display:none; position:fixed; top:20%; left:35%; width:30%; background:#fff; padding:20px; border:1px solid #000; z-index:9999;">
    <h4 id="modalTitle">Set Priority</h4>
    <form id="priorityForm">
        <input type="hidden" id="case_id" name="case_id">
        <input type="hidden" id="priority_id" name="priority_id">
        <div class="mb-3">
            <label for="priority_number">Priority Number:</label>
            <input type="number" id="priority_number" name="priority_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="remark">Remark:</label>
            <textarea id="remark" name="remark" class="form-control" required></textarea>
        </div>
        <button type="button" onclick="savePriority()" class="btn btn-primary">Save</button>
        <button type="button" onclick="deletePriority()" class="btn btn-danger" id="deleteBtn"
            style="display:none;">Delete</button>
        <button type="button" onclick="closePriorityPopup()" class="btn btn-secondary">Cancel</button>
    </form>
</div>

<!-- Background Overlay -->
<div id="modalOverlay"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9998;">
</div>

<!-- Add the following styles -->

<style>
    .priority-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: rgb(84, 79, 83);
        padding: 5px 15px;
        border-radius: 20px;
        color: white;
        cursor: pointer;
    }

    .priority-icon i {
        margin-right: 8px;
        font-size: 20px;
    }

    .priority-icon a {
        color: white;
        text-decoration: none;
    }

    .priority-icon a:hover {
        color: white;
    }

    .circle-priority {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        padding: 0;
        font-weight: bold;
        font-size: 16px;
    }

    .circle-priority span {
        display: inline-block;
        line-height: 40px;
    }
</style>
<script>
    function openPriorityPopup(case_id, sequence = '', remark = '', priority_id = '') {
        document.getElementById('case_id').value = case_id;
        document.getElementById('priority_number').value = sequence;
        document.getElementById('remark').value = remark;
        document.getElementById('priority_id').value = priority_id;

        document.getElementById('modalTitle').innerText = sequence ? 'Edit Priority' : 'Set Priority';
        document.getElementById('deleteBtn').style.display = priority_id ? 'inline-block' : 'none';

        document.getElementById('priorityModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function closePriorityPopup() {
        document.getElementById('priorityModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }

    function savePriority() {
        var case_id = document.getElementById('case_id').value;
        var priority_number = document.getElementById('priority_number').value;
        var remark = document.getElementById('remark').value;
        var priority_id = document.getElementById('priority_id').value;

        if (priority_number === "" || remark === "") {
            alert("Please fill all fields!");
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "save_priority.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            alert(this.responseText);
            closePriorityPopup();
            location.reload();
        };
        xhr.send("case_id=" + case_id + "&priority_number=" + priority_number + "&remark=" + encodeURIComponent(remark) + "&priority_id=" + priority_id);
    }

    function deletePriority() {
        var priority_id = document.getElementById('priority_id').value;
        if (!confirm("Are you sure you want to delete this priority?")) return;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "action.php", true); // <--- now goes to action.php
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            alert(this.responseText);
            closePriorityPopup();
            location.reload();
        };
        xhr.send("action=delete_priority&priority_id=" + priority_id);
    }

</script>


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