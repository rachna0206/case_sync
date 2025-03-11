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
            throw new Exception("Problem in deleting! " . strtok($obj->con1->error, '('));
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
    require 'Classes/PHPExcel.php';
    require 'Classes/PHPExcel/IOFactory.php';

    $x_file = $_FILES["excel_file"]["tmp_name"];

    try {
        $objPHPExcel = PHPExcel_IOFactory::load($x_file);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($x_file, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    $worksheet = $objPHPExcel->getActiveSheet();
    $allDataInSheet = $worksheet->toArray(null, true, true, true);

    $added = $updated = $proceedings_added = 0;

    foreach ($allDataInSheet as $i => $row) {
        if ($i < 2) {
            continue;
        }

        $case_no = !empty($row["B"]) ? trim($row["B"]) : null;
        $date_of_filing = !empty($row["A"]) ? $row["A"] : null;
        $complainant = !empty($row["C"]) ? trim($row["C"]) : null;
        $complainant_advocate = !empty($row["D"]) ? trim($row["D"]) : null;
        $opponent = !empty($row["E"]) ? trim($row["E"]) : null;
        $opponent_advocate = !empty($row["F"]) ? trim($row["F"]) : null;
        $next_stage = !empty($row["G"]) ? strtolower(trim($row["G"])) : null;
        $court_name = !empty($row["H"]) ? strtolower(trim($row["H"])) : null;
        $remarks = !empty($row["I"]) ? trim($row["I"]) : null;
        $next_date = !empty($row["J"]) ? $row["J"] : null;
        $company_name = !empty($row["K"]) ? strtolower(trim($row["K"])) : null;
        $case_type_name = !empty($row["L"]) ? strtolower(trim($row["L"])) : null;
        $city_name = !empty($row["M"]) ? strtolower(trim($row["M"])) : null;

        // if (!empty($next_date)) {
        //     $possible_formats = ['Y-m-d', 'd-m-Y', 'm/d/Y', 'd/m/Y', 'M d, Y', 'd-M-Y', 'Y/m/d'];
        //     $next_date_object = null;

        //     foreach ($possible_formats as $format) {
        //         $next_date_object = DateTime::createFromFormat($format, $next_date);
        //         if ($next_date_object !== false) {
        //             break;
        //         }
        //     }

        //     $next_date = $next_date_object ? $next_date_object->format('d-m-y') : null;
        // } else {
        //     $next_date = null;
        // }


        if (is_null($case_no) || is_null($city_name)) {
            continue;
        }

        $stmt = $obj->con1->prepare("SELECT id FROM city WHERE LOWER(name) = ?");
        $stmt->bind_param("s", $city_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $city_id = $result->fetch_assoc()["id"];
        } else {
            $stmt = $obj->con1->prepare("INSERT INTO city (name, status) VALUES (?, 'enable')");
            $stmt->bind_param("s", $city_name);
            $stmt->execute();
            $city_id = $stmt->insert_id;
            $stmt->close();
        }
        $case_type_id = null;
        if (!is_null($case_type_name)) {
            $stmt = $obj->con1->prepare("SELECT id FROM case_type WHERE LOWER(case_type) = ?");
            $stmt->bind_param("s", $case_type_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $case_type_id = $result->fetch_assoc()["id"];
            } else {
                $stmt = $obj->con1->prepare("INSERT INTO case_type (case_type, status) VALUES (?, 'enable')");
                $stmt->bind_param("s", $case_type_name);
                $stmt->execute();
                $case_type_id = $stmt->insert_id;
                $stmt->close();
            }
        }

        $stage_id = null;
        if (!is_null($next_stage)) {
            $stmt = $obj->con1->prepare("SELECT id FROM stage WHERE LOWER(stage) = ?");
            $stmt->bind_param("s", $next_stage);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $stage_id = $result->fetch_assoc()["id"];
            } else {
                // echo "INSERT INTO stage (`stage`,`case_type_id`, `status`) VALUES ('" . $next_stage . "', '" . $next_stage . "', 'enable')";
                $stmt = $obj->con1->prepare("INSERT INTO stage (`stage`,`case_type_id`, `status`) VALUES (?,?, 'enable')");
                $stmt->bind_param("ss", $next_stage, $case_type_id);
                $stmt->execute();
                $stage_id = $stmt->insert_id;
                $stmt->close();
            }
        }

        $court_id = null;
        if (!is_null($court_name)) {
            $stmt = $obj->con1->prepare("SELECT id FROM court WHERE LOWER(name) = ?");
            $stmt->bind_param("s", $court_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $court_id = $result->fetch_assoc()["id"];
            } else {
                $stmt = $obj->con1->prepare("INSERT INTO court (name,case_type, status) VALUES (?,?, 'enable')");
                $stmt->bind_param("ss", $court_name, $case_type_id);
                $stmt->execute();
                $court_id = $stmt->insert_id;
                $stmt->close();
            }
        }

        $company_id = null;
        if (!is_null($company_name)) {
            $stmt = $obj->con1->prepare("SELECT id FROM company WHERE name = ?");
            $stmt->bind_param("s", $company_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $company_id = $result->fetch_assoc()["id"];
            } else {
                $stmt = $obj->con1->prepare("INSERT INTO company (name, status) VALUES (?, 'enable')");
                $stmt->bind_param("s", $company_name);
                $stmt->execute();
                $company_id = $stmt->insert_id;
                $stmt->close();
            }
        }



        $stmt = $obj->con1->prepare("SELECT c.id,  Date_Format(cp.next_date,'%d-%m-%y') as next_date, cp.next_stage as stage FROM `case` as c left join `case_procedings` as cp on cp.case_id = c.id WHERE c.case_no = ? AND c.city_id = ? order by cp.id desc limit 1");
        $stmt->bind_param("si", $case_no, $city_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $case = $result->fetch_assoc();
            $case_id = $case["id"];

            if (($case["next_date"] !== $next_date || $case["stage"] !== $stage_id) && !is_null($next_date)) {
                $stmt = $obj->con1->prepare("INSERT INTO case_procedings (case_id, next_date, remarks, next_stage) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("issi", $case_id, $next_date, $remarks, $stage_id);
                $stmt->execute();
                $proceedings_added++;
                $stmt->close();
            }

            $stmt = $obj->con1->prepare("UPDATE `case` SET date_of_filing = ?, applicant = ?, complainant_advocate = ?, opp_name = ?, respondent_advocate = ?, next_date = ?, stage = ?, court_name = ?, company_id = ?, case_type = ? WHERE id = ? ;");
            $stmt->bind_param("ssssssssssi", $date_of_filing, $complainant, $complainant_advocate, $opponent, $opponent_advocate, $next_date, $stage_id, $court_id, $company_id, $case_type_id, $case_id);
            $stmt->execute();
            $updated++;
            $stmt->close();
        } else {
            // echo "INSERT INTO `case` (case_no, date_of_filing, applicant, complainant_advocate, opp_name, respondent_advocate, next_date, stage, court_name, company_id, case_type, city_id) VALUES('" . $case_no . "', '" . $date_of_filing . "', '" . $complainant . "', '" . $complainant_advocate . "', '" . $opponent . "', '" . $opponent_advocate . "', '" . $next_date . "', '" . $stage_id . "', '" . $court_id . "', '" . $company_id . "', '" . $case_type_id . "', '" . $city_id . "');";
            $stmt = $obj->con1->prepare("INSERT INTO `case` (case_no, date_of_filing, applicant, complainant_advocate, opp_name, respondent_advocate, next_date, stage, court_name, company_id, case_type, city_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssssi", $case_no, $date_of_filing, $complainant, $complainant_advocate, $opponent, $opponent_advocate, $next_date, $stage_id, $court_id, $company_id, $case_type_id, $city_id);
            $stmt->execute();
            $added++;
            $stmt->close();
            // if ($next_date != null && $case_id != null)
            $stmt = $obj->con1->prepare("INSERT INTO case_procedings (case_id, next_date, remarks, next_stage) 
                    VALUES (?, ?, ?, ?)
                ");
            $stmt->bind_param("issi", $case_id, $next_date, $remarks, $stage_id);
            $stmt->execute();
            $proceedings_added++;
            $stmt->close();
        }
    }

    echo "<div>";
    echo "$added records added successfully.<br>";
    echo "$updated records updated successfully.<br>";
    echo "$proceedings_added case proceedings added successfully.<br>";
    echo "</div>";
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
                        <!-- <div>
                            <a class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#excelModal"
                                style="margin-right: 15px; color: #fff;">
                                <i class="bx bx-upload"></i> Import Data
                            </a>
                            <a class="btn btn-primary mt-4" href="excel/demo_client_list.xlsx">
                                <button type="button" class="btn btn-primary mt-4" onclick="">
                                    <i class="bx bx-download"></i> Download Demo Excel
                                </button>
                            </a>
                        </div> -->
                        <div>
                            <a class="btn btn-primary mt-4" href="case_list_export.php">
                                <i class="bx bx-upload"></i> Export Data
                            </a>
                            <a class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#excelModal"
                                style="margin-right: 15px; color: #fff;">
                                <i class="bx bx-download"></i> Import Data
                            </a>

                        </div>
                    </div>
                </div>

                <table class="table datatable">
                    <thead>
                        <tr>
                            <th scope="col">Sr. no.</th>
                            <th scope="col">Case No.</th>
                            <th scope="col">Complainant</th>
                            <th scope="col">Respondent</th>
                            <th scope="col">Complainant Adv.</th>
                            <th scope="col">Respondent Adv.</th>
                            <th scope="col">City</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $obj->con1->prepare("SELECT c1.*,c1.id AS case_id,c2.name AS company_name,c3.case_type AS case_type_name, c4.name AS court_name, c5.name AS city_name, a1.name AS advocate_name,DATE_FORMAT(`c1`.next_date, '%d-%m-%Y') AS nxt_date FROM `case` c1 LEFT JOIN company c2 ON c1.company_id = c2.id LEFT JOIN case_type c3 ON c1.case_type = c3.id LEFT JOIN court c4 ON c1.court_name = c4.id LEFT JOIN city c5 ON c1.city_id = c5.id LEFT JOIN advocate a1 ON c1.handle_by = a1.id ORDER BY c1.id DESC");
                        $stmt->execute();
                        $Resp = $stmt->get_result();
                        $i = 1;
                        while ($row = mysqli_fetch_array($Resp)) {

                            if ($row['status'] == 'disposed') {
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
                                <td><?php echo $row["opp_name"]; ?></td>
                                <td><?php echo $row["complainant_advocate"]; ?></td>
                                <td><?php echo $row["respondent_advocate"]; ?></td>
                                <td><?php echo $row["city_name"] ?></td>
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
                                    <a
                                        href="javascript:deletedata('<?php echo $row["case_id"] ?>','<?php echo $row["case_no"] ?>')"><i
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