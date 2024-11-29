<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `case` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}


if (isset($_REQUEST["save"])) {
    $case_no = $_REQUEST['case_no'];
    $case_year = $_REQUEST['year'];
    $case_type = $_REQUEST['case_type'];
    $company_id = $_REQUEST['company_id'];
    $handle_by = $_REQUEST["handle_by"];
    $applicant = $_REQUEST['applicant'];
    $opp_name = $_REQUEST['opp_name'];
    $court_name = $_REQUEST['court_name'];
    $city_id = $_REQUEST['city_id'];
    $sr_date = $_REQUEST['sr_date'];
    $status = $_REQUEST['radio'];
    $stage = $_REQUEST['stage'];


    $multi_docs = $_FILES['docs']['name'];
    $multi_docs = str_replace(' ', '_', $multi_docs);
    $multi_docs_path = $_FILES['docs']['tmp_name'];

//echo $multi_docs;

    if ($multi_docs != "") {
    if (file_exists("documents/case/" . $multi_docs)) {
    $i = 0;
    $DocFileName = $multi_docs;
    $Arr1 = explode('.', $DocFileName);
    $DocFileName = $Arr1[0] . $i . "." . $Arr1[1];
    while (file_exists("documents/case/" . $DocFileName)) {
    $i++;
    $DocFileName = $Arr1[0] . $i . "." . $Arr1[1];
    }
    } else {
    $DocFileName = $multi_docs;
    }
    }

    try {
        //echo("INSERT INTO `case`(case_no, year, case_type, company_id, handle_by, docs, applicant, opp_name, court_name, city_id, sr_date, status) VALUES ($case_no, $case_year, $case_type, $company_id, $handle_by, $DocFileName, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status)");
        $stmt = $obj->con1->prepare("INSERT INTO `case`(case_no, year, case_type, company_id, handle_by, docs, applicant, opp_name, court_name, city_id, sr_date, `status`,stage) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sisiissssissi", $case_no, $case_year, $case_type, $company_id, $handle_by, $DocFileName, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status,$stage);
        $Resp = $stmt->execute();
        $insert_doc_id = mysqli_insert_id($obj->con1);

        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        foreach ($_FILES["multiple_file_name"]['name'] as $key => $value) { 
        // rename for product images       
        if($_FILES["multiple_file_name"]['name'][$key]!=""){
            $MultiDoc = $_FILES["multiple_file_name"]["name"][$key];
            if (file_exists("documents/case/" . $MultiDoc )) {
                $i = 0;
                $SubDocName = $MultiDoc;
                $Arr = explode('.', $SubDocName);
                $SubDocName = $Arr[0] . $i . "." . $Arr[1];
                while (file_exists("documents/case/" . $SubDocName)) {
                    $i++;
                    $SubDocName = $Arr[0] . $i . "." . $Arr[1];
                }
            } else {
                $SubDocName = $MultiDoc;
            }
            $SubDocTemp = $_FILES["multiple_file_name"]["tmp_name"][$key];
            $SubDocName = str_replace(' ', '_', $SubDocName);
        
            // sub images qry
            move_uploaded_file($SubDocTemp, "documents/case/".$SubDocName);
        }

        $doc_array = array("pdf", "doc", "docx");
        $extn = strtolower(pathinfo($SubDocName, PATHINFO_EXTENSION)); 
        $file_type = in_array($extn, $doc_array) ? "document" : "invalid";
        
        $stmt_docs = $obj->con1->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`) VALUES (?,?)");
        $stmt_docs->bind_param("is", $insert_doc_id, $SubDocName);
        $Resp = $stmt_docs->execute();
        $stmt_docs->close();
    }

} catch (Exception $e) {
    setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
    move_uploaded_file( $multi_docs_path, "documents/case/" . $DocFileName);
    setcookie("msg", "data", time() + 3600, "/");
    header("location:case.php");
    } else {
    setcookie("msg", "fail", time() + 3600, "/");
    header("location:case.php");
    }
    }
 
if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $case_no = $_REQUEST['case_no'];
    $case_year = $_REQUEST['year'];
    $case_type = $_REQUEST['case_type'];
    $company_id = $_REQUEST['company_id'];
    $handle_by = $_REQUEST["handle_by"];
    $applicant = $_REQUEST['applicant'];
    $opp_name = $_REQUEST['opp_name'];
    $court_name = $_REQUEST['court_name'];
    $city_id = $_REQUEST['city_id'];
    $sr_date = $_REQUEST['sr_date'];
    $status = $_REQUEST['radio'];
     $multi_docs  = $_FILES['docs']['name'];
     $multi_docs  = str_replace(' ', '_', $multi_docs );
     $multi_docs_path = $_FILES['docs']['tmp_name'];
    $old_img = $_REQUEST['old_img'];
    $stage = $_REQUEST['stage'];


    if ($multi_docs  != "") {
    if (file_exists("documents/case/" . $multi_docs)) {
    $i = 0;
    $DocFileName =  $multi_docs ;
    $Arr1 = explode('.', $DocFileName);
    $DocFileName = $Arr1[0] . $i . "." . $Arr1[1];
    while (file_exists("documents/case/" . $DocFileName)) {
    $i++;
    $DocFileName = $Arr1[0] . $i . "." . $Arr1[1];
    }
    } else {
    $DocFileName =  $multi_docs ;
    }
    if (file_exists("documents/case/" . $old_img)) {
    unlink("documents/case/" . $old_img);
    }
    move_uploaded_file( $multi_docs_path, "documents/case/" . $DocFileName);
    } else {
    $DocFileName = $old_img;
    }

    try {
        // Prepare update statement
        $stmt = $obj->con1->prepare("UPDATE `case` SET case_no=?, year=?, case_type=?, company_id=?, handle_by=?, docs=?, applicant=?, opp_name=?, court_name=?, city_id=?, sr_date=?, `status`=?,stage=? WHERE id=?");
        $stmt->bind_param("ssssssssssssii", $case_no, $case_year, $case_type, $company_id, $handle_by, $DocFileName, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status,$stage,$e_id);
        $Resp = $stmt->execute();
            if (!$Resp) {
            throw new Exception("Problem in updating! " . strtok($obj->con1->error, "("));
            }
            $stmt->close();
            } catch (Exception $e) {
            setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
            }
        
            if ($Resp) {
            setcookie("edit_id", "", time() - 3600, "/");
            setcookie("msg", "update", time() + 3600, "/");
            header("location:case.php");
            } else {
            setcookie("msg", "fail", time() + 3600, "/");
            header("location:case.php");
            }
            }
if (isset($_REQUEST["btndelete"])) {
    $delete_id = $_REQUEST['delete_id'];
  
    try {
        $stmt_del = $obj->con1->prepare("DELETE FROM `multiple_doc` WHERE id=?");
        $stmt_del->bind_param("i", $delete_id);
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
    header("location:case_add.php");
  }

?>

<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Case</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case</li>
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

                    <!-- Multi Columns Form -->
                    <form class="row g-3 pt-2" method="post" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="case_no" class="form-label">Case Number</label>
                                <input type="text" class="form-control" id="case_no" name="case_no"
                                    value="<?php echo (isset($mode)) ? $data['case_no'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>

                            <div class="col-md-6">
                                <label for="year" class="form-label">Case Year</label>
                                <input type="text" class="form-control" id="year" name="year"
                                    value="<?php echo (isset($mode)) ? $data['year'] : '' ?>"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="4"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="case_type" class="form-label">Case Type</label>
                                <select class="form-control" id="case_type" name="case_type"
                                    <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                    <option value="">Select a Case Type</option>
                                    <?php 
                                    $comp = "SELECT * FROM `case_type` where status='Enable'";
                                    $result = $obj->select($comp);
                                    $selectedcourtId = isset($data['case_type']) ? $data['case_type'] : '';

                                    while ($row = mysqli_fetch_array($result)) { 
                                        $selected = ($row["id"] == $selectedcourtId) ? 'selected' : '';
                                    ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row["case_type"]) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="company_id" class="form-label">Company</label>
                                <select class="form-control" id="company_id" name="company_id"
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

                            <div class="col-md-4">
                                <label for="handle_by" class="form-label">Handle By</label>
                                <select class="form-control" id="handle_by" name="handle_by"
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
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="docs" class="form-label">Documents</label>
                                <input type="file" class="form-control" id="docs" name="docs"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>
                                    onchange="readURL(this)">
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-primary mt-2 mb-3"
                                style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">
                                Document Preview
                            </h4>

                            <div id="preview_file_div" style="color:blue"></div>
                            <input type="hidden" name="old_file" id="old_file"
                                value="<?php echo (isset($mode) && $mode == 'edit') ? htmlspecialchars($data["docs"]) : '' ?>" />
                        </div>
                        <?php if (isset($mode) && $mode == 'edit' && !empty($data['docs'])): ?>
                        <div>
                            <div style="display: flex; align-items: center;">
                                <span><?php echo htmlspecialchars($data['docs']); ?></span>
                                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="confirmDelete()">
                                    <i class="bi bi-x-circle"></i> Delete
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($mode) && $mode == 'view' && !empty($data['docs'])): ?>
                        <div>

                            <a href="documents/case/<?php echo htmlspecialchars($data['docs']); ?>"
                                class="btn btn-primary" download>
                                <i class="bi bi-download"></i> Download <?php echo htmlspecialchars($data['docs']); ?>
                            </a>
                        </div>
                        <?php endif; ?>



                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="applicant" class="form-label">Applicant / Appellant / Complainant</label>
                                <input type="text" class="form-control" id="applicant" name="applicant"
                                    value="<?php echo (isset($mode)) ? $data['applicant'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>

                            <div class="col-md-4">
                                <label for="opp_name" class="form-label">Opponent / Respondent / Accused</label>
                                <input type="text" class="form-control" id="opp_name" name="opp_name"
                                    value="<?php echo (isset($mode)) ? $data['opp_name'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-4">
                                <label for="stage" class="form-label">Stage</label>
                                <select class="form-control" id="stage" name="stage"
                                    <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?> required>
                                    <option value="">Select a Stage</option>
                                    <?php 
                                    $comp = "SELECT * FROM `stage` where status='Enable'";
                                    $result = $obj->select($comp);
                                   
                                    while ($row = mysqli_fetch_array($result)) { 
                                       
                                    ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= (isset($mode) && $row["id"]== $data["stage"])?"selected":"" ?>>
                                        <?= htmlspecialchars($row["stage"]) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="court_name" class="form-label">Court</label>
                                <select class="form-control" id="court_name" name="court_name"
                                    <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                    <option value="">Select a Court</option>
                                    <?php 
                                    $comp = "SELECT * FROM `court` where status='Enable'";
                                    $result = $obj->select($comp);
                                    $selectedcourtId = isset($data['court_name']) ? $data['court_name'] : ''; 

                                    while ($row = mysqli_fetch_array($result)) { 
                                        $selected = ($row["id"] == $selectedcourtId) ? 'selected' : '';
                                    ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row["name"]) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="city_id" class="form-label">City Name</label>
                                <select class="form-control" id="city_id" name="city_id"
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

                            <div class="col-md-4">
                                <label for="sr_date" class="form-label">Summon Date</label>
                                <input type="date" class="form-control" id="sr_date" name="sr_date"
                                    value="<?php echo (isset($mode) && isset($data['sr_date']) && !empty($data['sr_date'])) ? date('Y-m-d', strtotime($data['sr_date'])) : date('Y-m-d'); ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label> <br />
                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" name="radio" id="radio1"
                                        <?php echo isset($mode) && $data['status'] == 'Enable' ? 'checked' : '' ?>
                                        class="form-radio text-primary" value="Enable" required
                                        <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                                    <label class="form-check-label" for="radio1">Enable</label>
                                </div>
                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" name="radio" id="radio2"
                                        <?php echo isset($mode) && $data['status'] == 'Disable' ? 'checked' : '' ?>
                                        class="form-radio text-danger" value="Disable" required
                                        <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                                    <label class="form-check-label" for="radio2">Disable</label>
                                </div>
                            </div>
                        </div>

                        <div class="text-left mt-4">
                            <button type="submit"
                                name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'd-none' : '' ?>">
                                <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="javascript: go_back();">
                                Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
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

<section class="section" <?php echo (isset($mode))?'':'hidden' ?>>
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <a href="javascript:addmuldocs();"><button type="button" class="btn btn-success"
                                <?php echo ($mode=='edit')?'':'hidden' ?>><i class="bi bi-plus me-1"></i> Add
                                Documents</button></a>
                    </div>

                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr.No</th>
                                <th scope="col">Document</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                    $c_id = $data['id'];
                    // echo("SELECT * FROM `multiple_doc` WHERE c_id=$c_id ORDER BY id DESC");
                    $stmt_images = $obj->con1->prepare("SELECT * FROM `multiple_doc` WHERE c_id=? ORDER BY id DESC");
                    $stmt_images->bind_param("i",$c_id);
                    $stmt_images->execute();
                    $result = $stmt_images->get_result();
                    $stmt_images->close();
                    $i=1;
                    while($row=mysqli_fetch_array($result))
                    {
                  ?>
                            <tr>
                                <th scope="row"><?php echo $i ?></th>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <a href="documents/case/<?php echo $row["docs"] ?>" class="btn btn-primary me-2"
                                            download>
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <span><?php echo $row["docs"] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <a href="javascript:viewmuldocs('<?php echo $row["id"]?>');"><i
                                            class="bx bx-show-alt bx-sm me-2"></i></a>
                                    <a href="javascript:editmuldocs('<?php echo $row["id"]?>');"
                                        <?php echo ($mode=='edit')?'':'hidden' ?>><i
                                            class="bx bx-edit-alt bx-sm text-success me-2"></i></a>
                                    <a href="javascript:deletemuldocs('<?php echo $row["id"]?>');"
                                        <?php echo ($mode=='edit')?'':'hidden' ?>><i
                                            class="bx bx-trash bx-sm text-danger"></i></a>
                                </td>
                            </tr>
                            <?php
                      $i++;
                    }
                  ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->

                </div>
            </div>

        </div>
    </div>
</section>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function go_back() {
    eraseCookie("edit_id");
    eraseCookie("view_id");
    window.location = "case.php";
}

function editmuldocs(id) {
    eraseCookie("view_muldocs_id");
    createCookie("edit_muldocs_id", id, 1);
    window.location = "case_mul_doc.php";
}

function viewmuldocs(id) {
    eraseCookie("edit_muldocs_id");
    createCookie("view_muldocs_id", id, 1);
    window.location = "case_mul_doc.php";
}

function deletemuldocs(id) {
    $('#deleteModal').modal('toggle');
    $('#delete_id').val(id);
}

function addmuldocs(id) {
    window.location = "case_mul_doc.php";
}

function readURL_multiple(input) {
    $('#preview_file_div').html(""); // Clear previous preview
    var filesAmount = input.files.length;
    for (let i = 0; i < filesAmount; i++) {
        if (input.files && input.files[i]) {
            var filename = input.files[i].name;
            var extn = filename.split(".").pop().toLowerCase();

            if (["pdf", "doc", "docx"].includes(extn)) {
                document.getElementById('save').disabled = false; // Enable save button if valid file

                // Display file name with a delete "X" button
                $('#preview_file_div').append('<p id="file_' + i + '">' + filename +
                    ' <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(' + i + ')">' +
                    '<i class="bi bi-x-circle"></i></button></p>');
            } else {
                $('#preview_file_div').html("Please select a valid file (PDF, DOC, and DOCX)");
                document.getElementById('save').disabled = true;
                break; // Stop the loop for invalid file
            }
        }
    }
}

function readURL(input) {
    $('#preview_file_div').html(""); // Clear previous preview
    if (input.files && input.files[0]) {
        var filename = input.files[0].name; // Get the name of the first file
        var extn = filename.split(".").pop().toLowerCase();

        if (["pdf", "doc", "docx","xlsx","jpg","png","jpeg","bmp","txt"].includes(extn)) {
            document.getElementById('save').disabled = false; // Enable save button if valid file

            // Display only the file name with a delete button
            $('#preview_file_div').append('<p>' + filename +
                ' <button type="button" class="btn btn-danger btn-sm" onclick="deleteFile()">' +
                '<i class="bi bi-x-circle"></i></button></p>');
        } else {
            $('#preview_file_div').html("Please select a valid file (PDF, DOC, and DOCX)");
            document.getElementById('save').disabled = true;
        }
    }
}

function deleteFile() {
    // Clear the file input and the preview
    document.getElementById('docs').value = ''; // Clear the file input
    $('#preview_file_div').html(""); // Clear the preview
    document.getElementById('save').disabled = true; // Disable save button
}


function confirmDelete(index) {
    if (confirm("Are you sure you want to delete this document?")) {
        deleteDocument(index);
    }
}

function deleteDocument(index) {
    // Remove the file preview from the list
    $('#file_' + index).remove();

    // If no files are left, disable the save button
    if ($('#preview_file_div').children().length == 0) {
        document.getElementById('save').disabled = true;
    }
}
</script>
<?php
include "footer.php";
?>