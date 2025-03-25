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
                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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
                    <div class="col-md-12 mb-3">
                        <label for="city_id" class="form-label">Case Type</label>

                        <select class="form-select" id="case_type" name="case_type"
                            <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                            <option value="">Select Case Type</option>
                            <?php
                            $case_type = "SELECT * FROM `case_type` where `status`='enable'";
                            $result_case_type = $obj->select($case_type);


                            while ($row_case_type = mysqli_fetch_array($result_case_type)) {

                                ?>
                                    <option value="<?= htmlspecialchars($row_case_type["id"]) ?>">
                                        <?= htmlspecialchars($row_case_type["case_type"]) ?>
                                    </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="javascript:closeButton();" >Close</button>
                    <input type="submit" name="btnexcelsubmit" value="Download">
                    <!-- <button type="submit" name="btnexcelsubmit" class="btn btn-primary">Download</button> -->
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">

    function closeButton(){
        window.location = "";
    }

</script>
<?php

if(isset($_REQUEST["btnexcelsubmit"])){

include "db_connect.php";
require_once 'vendor/autoload.php'; // PhpSpreadsheet is required

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$obj = new DB_connect();

function fill_xlsx($inq_id, $service_id, $stage_id, $file_id)
{
    global $obj;

    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set column headers
    $headers = ['Key', 'Value'];
    $sheet->fromArray([$headers], NULL, 'A1');

    // Fetch data from pr_files_data
    $stmt_files = $obj->con1->prepare("SELECT file_data FROM `pr_files_data` WHERE scheme_id=? AND stage_id=? AND file_id=? AND inq_id=? ORDER BY id DESC LIMIT 1");
    $stmt_files->bind_param("iiii", $service_id, $stage_id, $file_id, $inq_id);
    $stmt_files->execute();
    $result_files = $stmt_files->get_result();
    $stmt_files->close();

    $rowIndex = 2; // Start filling from row 2
    if ($row = $result_files->fetch_assoc()) {
        $file_data = json_decode($row["file_data"], true);
        foreach ($file_data as $key => $value) {
            if (is_array($value)) {
                $value = implode(", ", $value); // Convert array to string
            }
            $sheet->setCellValue("A$rowIndex", $key);
            $sheet->setCellValue("B$rowIndex", $value);
            $rowIndex++;
        }
    }

    // Fetch data from tbl_tdapplication
    $stmt_list = $obj->con1->prepare("SELECT app_data FROM `tbl_tdapplication` WHERE inq_id=? ORDER BY id DESC LIMIT 1");
    $stmt_list->bind_param("i", $inq_id);
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();

    if ($row = $result->fetch_assoc()) {
        $row_data = json_decode($row["app_data"], true);
        foreach ($row_data as $category => $details) {
            if (is_array($details)) {
                foreach ($details as $key => $value) {
                    if (!is_array($value)) {
                        $sheet->setCellValue("A$rowIndex", $key);
                        $sheet->setCellValue("B$rowIndex", $value);
                        $rowIndex++;
                    }
                }
            }
        }
    }

    // Set filename
    $filename = "Case_List.xlsx";
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");

    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit();
}

// Call function to generate xlsx
$inq_id = $_GET['inq_id']; // Fetch ID dynamically
$service_id = $_GET['service_id'];
$stage_id = $_GET['stage_id'];
$file_id = $_GET['file_id'];

fill_xlsx($inq_id, $service_id, $stage_id, $file_id);
}
?>