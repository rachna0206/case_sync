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

 

if (isset($_REQUEST["btn_stage"])) {

    $case_type = $_REQUEST['case_type_id'];
    $stage_name = $_REQUEST['stage'];
    $status='enable';
    try {
        // echo "INSERT INTO `city`(`name`, `status`) VALUES (". $city_name.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `stage`(`case_type_id`,`stage`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss",$case_type, $stage_name, $status);
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
        
        
        header("location:case_add.php");
    } else {
        
        header("location:case_add.php"); 
    }
}

if (isset($_REQUEST["btn_case_type"])) {

    $case_type_m = $_REQUEST['c_type'];
    $status='enable';
    try {
        // echo "INSERT INTO `city`(`case_type`, `status`) VALUES (". $case_type_m.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `case_type`(`case_type`, `status`) VALUES (?,?)");
        $stmt->bind_param("ss",$case_type_m, $status);
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
        
        
        header("location:case_add.php");
    } else {
        
        header("location:case_add.php"); 
    }
}

if (isset($_REQUEST["btn_court"])) {

    $case_type = $_REQUEST['case_type'];
    $court_name = $_REQUEST['court'];
    $status='enable';
    try {
        // echo "INSERT INTO `city`(`name`, `status`) VALUES (". $city_name.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `court`(`case_type`,`name`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss",$case_type, $court_name, $status);
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
        
        
        header("location:case_add.php");
    } else {
        
        header("location:case_add.php"); 
    }
}

if (isset($_REQUEST["btn_company"])) {

    $comp_name = $_REQUEST['comp_name'];
    $person = $_REQUEST['person'];
    $contact_no = $_REQUEST['contact'];
    $status='enable';
    try {
        
        $stmt = $obj->con1->prepare("INSERT INTO `company`(`name`,`contact_person`,`contact_no`,`status`) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss",$comp_name, $person,$contact_no, $status);
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
        
        
        header("location:case_add.php");
    } else {
        
        header("location:case_add.php"); 
    }
}

if (isset($_REQUEST["btn_handle_by"])) {

    $adv_name = $_REQUEST['adv_name'];
    $adv_contact = $_REQUEST['adv_contact'];
    $adv_email = $_REQUEST['adv_email'];
    $adv_password = $_REQUEST['adv_password'];
    $status='enable';
    try {
        // echo "INSERT INTO `city`(`name`, `status`) VALUES (". $city_name.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `advocate`(`name`,`contact`,`email`,`password`,`status`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss",$adv_name, $adv_contact,$adv_email, $adv_password, $status);
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
        
        
        header("location:case_add.php");
    } else {
        
        header("location:case_add.php"); 
    }
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
                                <div class="d-flex">
                                <select class="form-select" id="case_type" name="case_type"
                                    <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>
                                    onchange="get_stage(this.value)">
                            
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
                                <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                                    data-bs-target="#addcasetypemodal">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="stage" class="form-label">Stage</label>
                            <div class="d-flex">
                            <select class="form-select" id="stage" name="stage"
                                <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?> required>
                            
                                    <option value="">Select Stage</option>
                                    <?php 
                                    if(isset($mode))
                                    {
                                        $comp = "SELECT * FROM `stage` where case_type_id='".$data["case_type"]."' and lower(`status`)='enable'";
                                    }
                                    else
                                    {
                                        $comp = "SELECT * FROM `stage` where lower(`status`)='enable'";
                                    }
                                    
                                    $result = $obj->select($comp);
                                   
                                    while ($row = mysqli_fetch_array($result)) { 
                                       
                                    ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>"
                                        <?= (isset($mode) && $row["id"]== $data["stage"])?"selected":"" ?>>
                                        <?= htmlspecialchars($row["stage"]) ?>
                                    </option>
                                    <?php }
                                     
                                    ?>
                            </select>
                            <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#addstagemodal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>

                </div>
                <div class="col-md-4">
                    <label for="court_name" class="form-label">Court</label>
                    <div class="d-flex">
                    <select class="form-select" id="court_name" name="court_name"
                        <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                        <option value="">Select Court</option>
                        <?php 
                                    if(isset($mode))
                                    {
                                        $comp = "SELECT * FROM `court` where case_type='".$data["case_type"]."' and lower(`status`)='enable'";
                                    }
                                    else
                                    {
                                        $comp = "SELECT * FROM `court` where lower(`status`)='enable'";
                                    }
                                    $result = $obj->select($comp);
                                    $selectedcourtId = isset($data['court_name']) ? $data['court_name'] : ''; 

                                    while ($row = mysqli_fetch_array($result)) { 
                                        $selected = ($row["id"] == $selectedcourtId) ? 'selected' : '';
                                    ?>
                        <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($row["name"]) ?>
                        </option>
                        <?php } 
                                    
                                   ?>
                    </select>
                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#addcourtmodal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                </div>




            </div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="docs" class="form-label">Documents</label>
                    <input type="file" class="form-control" id="docs" name="docs"
                        <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> onchange="readURL(this)">
                </div>
            </div>

            <div>


                <div id="preview_file_div" class="text-danger"></div>
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

                <a href="documents/case/<?php echo htmlspecialchars($data['docs']); ?>" class="btn btn-primary"
                    download>
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
                    <label for="company_id" class="form-label">Company</label>
                    <div class="d-flex">
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
                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#addcompanymodal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                </div>




            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="handle_by" class="form-label">Handled By</label>
                    <div class="d-flex">
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
                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#addhandlebymodal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                </div>


                <div class="col-md-4">
                    <label for="city_id" class="form-label">City Name</label>
                    <div class="d-flex">
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
                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#addcitymodal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
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
                            <?php echo (!isset($mode) || (isset($mode)  && $data['status'])) == 'pending' ? 'checked' : '' ?>
                            class="form-radio text-primary" value="pending" required
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                        <label class="form-check-label" for="radio1">Pending</label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="radio" name="radio" id="radio2"
                            <?php echo isset($mode) && $data['status'] == 'disposed' ? 'checked' : '' ?>
                            class="form-radio text-danger" value="disposed" required
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                        <label class="form-check-label" for="radio2">Disposed</label>
                    </div>
                </div>
            </div>

            <div class="text-left mt-4">
                <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
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

<div class="modal fade" id="addcasetypemodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Case Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">

                    <div class="col-md-12">
                        <label for="title" class="form-label">Case Type</label>
                        <input type="text" class="form-control" id="c_type" name="c_type" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="btn_case_type" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add case type Modal-->

<div class="modal fade" id="addstagemodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="col-md-12 mb-3">
                        <label for="case_type_id" class="form-label">Case Type</label>
                        <select class="form-control" id="case_type_id" name="case_type_id" required>
                            <option value="">Select Case Type</option>
                            <?php
                        $task = "SELECT * FROM `case_type` where `status` = 'Enable'";
                        $result = $obj->select($task);
                        $selectedCaseId = isset($data['case_type_id']) ? $data['case_type_id'] : '';

                        while ($row = mysqli_fetch_array($result)) {
                           
                        ?>
                            <option value="<?= htmlspecialchars($row["id"]) ?>">
                                <?= htmlspecialchars($row["case_type"]) ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>


                    <div class="col-md-12">
                        <label for="stage" class="form-label">Stage</label>
                        <input type="text" class="form-control" id="stage" name="stage" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="btn_stage" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add stage Modal-->

<div class="modal fade" id="addcourtmodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Court</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="col-md-12 mb-3">
                        <label for="case_type" class="form-label">Case Type</label>
                        <select class="form-control" id="case_type" name="case_type" required>
                            <option value="">Select Case Type</option>
                            <?php
                        $task = "SELECT * FROM `case_type` where `status` = 'Enable'";
                        $result = $obj->select($task);
                        $selectedCaseId = isset($data['case_type']) ? $data['case_type'] : '';

                        while ($row = mysqli_fetch_array($result)) {
                           
                        ?>
                            <option value="<?= htmlspecialchars($row["id"]) ?>">
                                <?= htmlspecialchars($row["case_type"]) ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>


                    <div class="col-md-12">
                        <label for="court" class="form-label">Court</label>
                        <input type="text" class="form-control" id="court" name="court" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="btn_court" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add Court Modal-->

<div class="modal fade" id="addcompanymodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Company</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">

                    <div class="col-md-12">
                        <label for="comp_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="comp_name" name="comp_name" required>
                    </div>

                    <div class="col-md-12">
                        <label for="person" class="form-label">Person</label>
                        <input type="text" class="form-control" id="person" name="person" required>
                    </div>

                    <div class="col-md-12">
                        <label for="contact" class="form-label">Contact No.</label>
                        <input type="text" class="form-control" id="contact" name="contact" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="btn_company" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add company Modal-->

<div class="modal fade" id="addhandlebymodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Handle By</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">

                    <div class="col-md-12">
                        <label for="title" class="form-label">Advocate Name</label>
                        <input type="text" class="form-control" id="adv_name" name="adv_name" required>
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label">Advocate Contact No.</label>
                        <input type="text" class="form-control" id="adv_contact" name="adv_contact" required>
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label">Advocate Email</label>
                        <input type="text" class="form-control" id="adv_email" name="adv_email" required>
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label">Password</label>
                        <input type="password" class="form-control" id="adv_password" name="adv_password" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="btn_handle_by" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add handleby Modal-->


<div class="modal fade" id="addcitymodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add City</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="col-md-12 mb-3">
                        <label for="state_id" class="form-label">State</label>
                        <select class="form-control" id="state_id" name="state_id" required>
                            <option value="">Select State</option>
                            <?php
                        $task = "SELECT * FROM `state` where `status` = 'Enable'";
                        $result = $obj->select($task);
                        $selectedCaseId = isset($data['state_id']) ? $data['state_id'] : '';

                        while ($row = mysqli_fetch_array($result)) {
                           
                        ?>
                            <option value="<?= htmlspecialchars($row["id"]) ?>">
                                <?= htmlspecialchars($row["state_name"]) ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>


                    <div class="col-md-12">
                        <label for="title" class="form-label">City Name</label>
                        <input type="text" class="form-control" id="c_name" name="c_name" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" name="btn_city" class="btn btn-primary" onclick="add_city()">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add city Modal-->

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
function get_stage(case_type) {
    $.ajax({
        async: true,
        type: "POST",
        url: "action.php?action=get_stage",
        data: "case_type=" + case_type,
        cache: false,
        success: function(result) {
            var data = result.split("@@@@@");
            $("#stage").html("");
            $("#stage").html(data[0]);
            $("#court_name").html("");
            $("#court_name").html(data[1]);

        }
    });
}

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

        if (["pdf", "doc", "docx", "xlsx", "jpg", "png", "jpeg", "bmp", "txt"].includes(extn)) {
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


function add_city()
{

    var state=$("#state_id").val();
    var city=$("#c_name").val();
    $("#addcitymodal").modal("toggle");

    $.ajax({
        async: true,
        type: "POST",
        url: "action.php?action=add_city",
        data: "state=" + state+"&city="+city,
        cache: false,
        success: function(result) {
            $("#city_id").append(result);
          

        }
    });

}
</script>
<?php
include "footer.php";
?>