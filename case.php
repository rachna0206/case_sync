<?php
include "header.php";
include "alert.php";

if (isset($_REQUEST["btndelete"])) {
    $c_id = $_REQUEST['delete_id'];

    try {
        $stmt_subimg = $obj->con1->prepare("SELECT * FROM `case` WHERE id=?");
        $stmt_subimg->bind_param("i", $c_id);
        $stmt_subimg->execute();
        $Resp_subimg = $stmt_subimg->get_result()->fetch_assoc();
        $stmt_subimg->close();

        if (file_exists("documents/case" . $Resp_subimg["docs"])) {
            unlink("documents/case" . $Resp_subimg["docs"]);
        }

        $stmt_del = $obj->con1->prepare("DELETE FROM `case` WHERE id=?");
        $stmt_del->bind_param("i", $c_id);
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            throw new Exception("Problem in deleting! " . strtok($obj->con1->error,  '('));
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data_del", time() + 3600, "/");
    }
    header("location:case.php");
}



if (isset($_REQUEST["btnexcelsubmit"]) && $_FILES["excel_file"]["tmp_name"] !== "") {
    $x_file = $_FILES["excel_file"]["tmp_name"];
    $company_id = $_REQUEST["company_id"];
    $handle_by = $_REQUEST["handle_by"];
    $city = $_REQUEST["city_id"];
    set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');
    include 'Classes/PHPExcel/IOFactory.php';
    $inputFileName = $x_file;

    try {
        $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    $worksheet = $objPHPExcel->getActiveSheet();
    $allDataInSheet = $worksheet->toArray(null, true, true, true);
     $arrayCount = count($allDataInSheet);

    $msg1 = $msg2 = $msg3 = $msg4 = "";

    for ($i = 2; $i <= $arrayCount; $i++) {
        $case_no = trim($allDataInSheet[$i]["B"]);
        $applicant = trim($allDataInSheet[$i]["C"]);
        $respondent = strtolower(trim($allDataInSheet[$i]["D"])); // Company name$complainant_advocate = trim($allDataInSheet[$i]["E"]);
        $respondent_advocate = strtolower(trim($allDataInSheet[$i]["F"])); // Advocate name
        $date_of_filing = date("Y-m-d",strtotime(trim($allDataInSheet[$i]["G"])));
        $next_date = date("Y-m-d",strtotime(trim($allDataInSheet[$i]["H"])));




        if ($case_no != "" && $company_id && $handle_by) {
           
            $stmt_dmd_ck = $obj->con1->prepare("SELECT * FROM `case` WHERE case_no = ?");
            $stmt_dmd_ck->bind_param("s", $case_no);
            $stmt_dmd_ck->execute();
            $dmd_result = $stmt_dmd_ck->get_result()->num_rows;
            $stmt_dmd_ck->close();

            if ($dmd_result > 0) {
                $msg1 .= '<div style="font-family:serif;font-size:18px;color:rgb(214, 13, 42);padding:0px 0 0 0;margin:10px 0px 0px 0px;"> Record no. ' . $i . ": " . $case_no . " already exists in the database.</div>";
            } else {

               
                $stmt = $obj->con1->prepare("INSERT INTO `case`(`case_no`, `applicant`,`opp_name`, `company_id`, `complainant_advocate`,`respondent_advocate`, `handle_by`, `date_of_filing`, `next_date`) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("sssississ", $case_no, $applicant,$respondent, $company_id, $complainant_advocate,$respondent_advocate, $handle_by, $date_of_filing, $next_date);
                $Resp = $stmt->execute();
                $stmt->close();
                if ($Resp) {
                    $msg2 .= '<div style="font-family:serif;font-size:18px;padding:0px 0 0 0;margin:10px 0px 0px 0px;">Record no. ' . $i . ": " . ' Added Successfully in the database.</div>';
                } else {
                    $msg3 .= '<div style="font-family:serif;font-size:18px;color:rgb(214, 13, 42);padding:0px 0 0 0;margin:10px 0px 0px 0px;">Record no. ' . $i . ": " . ' Record not added in the database.</div>';
                }
            }
        } else {
            $msg4 .= '<div style="font-family:serif;font-size:18px;color:rgb(214, 13, 42);padding:0px 0 0 0;margin:10px 0px 0px 0px;"> Record no. ' . $i . ": Missing or invalid dropdown values.</div>";
        }
    }

    $msges = $msg1 . $msg2 . $msg3 . $msg4;
    echo "msg=".$msges;
    setcookie("excelmsg", $msges, time()+3600, "/");
    header("location:case.php");
}
?>
<script type="text/javascript">
    function add_data() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "case_add.php";
    }

    function editdata(id) {
        eraseCookie("view_id");
        createCookie("edit_id", id, 1);
        window.location = "case_add.php";
    }

    function viewdata(id) {
        eraseCookie("edit_id");
        createCookie("view_id", id, 1);
        window.location = "case_add.php";
    }

    function deletedata(id, case_no) {
        $('#deleteModal').modal('toggle');
        $('#delete_id').val(id);
        $('#delete_record').html(case_no);
    }

    function addmuldocs(id) {
        eraseCookie("view_id");
        eraseCookie("edit_muldocs_id");
        eraseCookie("view_muldocs_id");
        createCookie("edit_id", id, 1);
        window.location = "case_mul_doc.php";
    }
</script>

<!-- Excel Modal -->
<div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="excelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="excelModalLabel">Upload Excel File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="col-md-12">
                        <label for="handle_by" class="form-label">Handled By</label>
                        <select class="form-select" id="handle_by" name="handle_by"
                            <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                            <option value="">Select an Advocate</option>
                            <?php
                            $comp = "SELECT * FROM `advocate` where status='Enable'";
                            $result = $obj->select($comp);
                            $selectedAdvocateId = isset($data['handle_by']) ? $data['handle_by'] : '';

                            while ($row = mysqli_fetch_array($result)) {
                                $selected = ($row["id"] == $selectedAdvocateId) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($row["name"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="company_id" class="form-label">Company</label>
                        <select class="form-select" id="company_id" name="company_id"
                            <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                            <option value="">Select a Company</option>
                            <?php
                            $comp = "SELECT * FROM `company` where status='Enable'";
                            $result = $obj->select($comp);
                            $selectedCompanyId = isset($data['company_id']) ? $data['company_id'] : '';

                            while ($row = mysqli_fetch_array($result)) {
                                $selected = ($row["id"] == $selectedCompanyId) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($row["name"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label for="city_id" class="form-label">City Name</label>

                        <select class="form-select" id="city_id" name="city_id"
                            <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                            <option value="">Select a City</option>
                            <?php
                            $comp = "SELECT * FROM `city`";
                            $result = $obj->select($comp);
                            $selectedCompanyId = isset($data['city_id']) ? $data['city_id'] : '';

                            while ($row = mysqli_fetch_array($result)) {
                                $selected = ($row["id"] == $selectedCompanyId) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($row["name"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Choose Excel File</label>
                        <input type="file" id="excel_file" name="excel_file" class="form-control" required>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btnexcelsubmit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
                    Are you sure you really want to delete Case No: "<span id="delete_record"></span>" ?
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
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 15px;">
                        <!-- Add button -->
                        <a href="javascript:add_data()">
                            <button type="button" class="btn btn-success mt-4" style="margin-right: 15px;">
                                <i class="bi bi-plus me-1"></i> Add
                            </button>
                        </a>
                        <div>
                            <a class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#excelModal"
                                style="margin-right: 15px; color: #fff;">
                                <i class="bx bx-upload"></i> Import Data
                            </a>
                            <a class="btn btn-primary mt-4" href="excel/demo_client_list.xlsx">
                                <i class="bx bx-download"></i> Download Demo Excel
                            </a>
                        </div>
                    </div>
                </div>

                <table class="table datatable">
                    <thead>
                        <tr>
                            <th scope="col">Sr no.</th>
                            <th scope="col">Case No</th>
                            <th scope="col">Complainant</th>
                            <th scope="col">Respondent</th>
                            <th scope="col">Complainant Advocate</th>
                            <th scope="col">Respondent Advocate</th>
                            <th scope="col">Date of Filing</th>
                            <th scope="col">Date of Next Hearing</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $obj->con1->prepare("SELECT c1.*,c1.id AS case_id,c2.name AS company_name,c3.case_type AS case_type_name, c4.name AS court_name, c5.name AS city_name, a1.name AS advocate_name FROM `case` c1 LEFT JOIN company c2 ON c1.company_id = c2.id LEFT JOIN case_type c3 ON c1.case_type = c3.id LEFT JOIN court c4 ON c1.court_name = c4.id LEFT JOIN city c5 ON c1.city_id = c5.id LEFT JOIN advocate a1 ON c1.handle_by = a1.id ORDER BY c1.id DESC");
                        $stmt->execute();
                        $Resp = $stmt->get_result();
                        $i = 1;
                        while ($row = mysqli_fetch_array($Resp)) {

                            if ($row['status'] == 'dispossed') {
                                $class = "success";
                            } else if ($row['status'] == 'pending') {
                                $class = "warning";
                            } else {
                                $class = "secondary";
                            }


                        ?>
                            <tr>

                                <th scope="row"><?php echo $i; ?></th>
                                <td><?php echo $row["case_no"]; ?></td>
                                <td><?php echo $row["applicant"]; ?></td>
                                <td><?php echo $row["company_name"]; ?></td>
                                <td><?php echo $row["complainant_advocate"]; ?></td>
                                <td><?php echo $row["advocate_name"]; ?></td>
                                <td><?php echo date("d/m/Y", strtotime($row["date_of_filing"])); ?></td>
                                <td><?php echo ($row["next_date"] != "") ? date("d/m/Y", strtotime($row["next_date"])) : "-"; ?></td>
                                <td>
                                    <h4><span
                                            class="badge rounded-pill bg-<?php echo $class ?>"><?php echo ucfirst($row["status"]); ?></span>
                                    </h4>
                                </td>

                                <td>
                                    <a href="javascript:addmuldocs('<?php echo $row["case_id"] ?>');"><i
                                            class="bx bx-add-to-queue bx-sm me-2"></i></a>
                                    <a href="javascript:viewdata('<?php echo $row["case_id"] ?>')"><i
                                            class="bx bx-show-alt bx-sm me-2"></i> </a>
                                    <a href="javascript:editdata('<?php echo $row["case_id"] ?>')"><i
                                            class="bx bx-edit-alt bx-sm me-2 text-success"></i> </a>
                                    <a href="javascript:deletedata('<?php echo $row["case_id"] ?>','<?php echo $row["case_no"] ?>')"><i
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