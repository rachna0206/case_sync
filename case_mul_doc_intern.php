<?php
include "header_intern.php";

$case_id = isset($_COOKIE['edit_id']) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];

if (isset($_COOKIE['edit_muldocs_id']) || isset($_COOKIE['view_muldocs_id'])) {
    $mode = (isset($_COOKIE['edit_muldocs_id'])) ? 'edit' : 'view';
    $id = (isset($_COOKIE['edit_muldocs_id'])) ? $_COOKIE['edit_muldocs_id'] : $_COOKIE['view_muldocs_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `multiple_doc` where id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Save documents
// Save documents
if (isset($_REQUEST["save"])) {
    $old_img = $_REQUEST['old_img']; // Only useful if you're replacing old files, optional here
    $Resp = false;

    try {
        // Loop through all uploaded files
        foreach ($_FILES["docs"]['name'] as $key => $value) {
            if (!empty($_FILES["docs"]['name'][$key])) {
                $originalName = $_FILES["docs"]["name"][$key];
                $originalTmpPath = $_FILES["docs"]["tmp_name"][$key];

                // Generate unique file name and sanitize
                $uniqueFileName = generateUniqueFileName("documents/case/", $originalName);
                $uniqueFileName = str_replace(' ', '_', $uniqueFileName);

                // Move uploaded file to destination
                if (move_uploaded_file($originalTmpPath, "documents/case/" . $uniqueFileName)) {
                    // Save file reference in database
                    $stmt_image = $obj->con1->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`,`added_by`) VALUES (?, ?, ?)");
                    $stmt_image->bind_param("isi", $case_id, $uniqueFileName, $_SESSION["id"]);
                    $Resp = $stmt_image->execute();
                    $stmt_image->close();

                    if (!$Resp) {
                        throw new Exception("Problem inserting file record for: " . $uniqueFileName);
                    }
                } else {
                    throw new Exception("Failed to move uploaded file: " . $originalName);
                }
            }
        }

        if (!$Resp) {
            throw new Exception("No files were uploaded or saved.");
        }

        // Success
        setcookie("msg", "data", time() + 3600, "/");
        header("location:case_files_intern.php");
        exit();

    } catch (Exception $e) {
        // Log error
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case_files_intern.php");
        exit();
    }
}
// Update documents
if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_muldocs_id'];
    $file_name_one = $_FILES['file_name_one']['name'];
    $file_name_one = str_replace(' ', '_', $file_name_one);
    $file_path_one = $_FILES['file_name_one']['tmp_name'];
    //$old_img = $_REQUEST['old_img'];

    // Rename file for product image
    if ($file_name_one != "") {
        $PicFileName = generateUniqueFileName("documents/case/", $file_name_one);
        unlink("documents/case/" . $old_img);
        move_uploaded_file($file_path_one, "documents/case/" . $PicFileName);
    } else {
        $PicFileName = $old_img;
    }

    try {
        $stmt = $obj->con1->prepare("UPDATE `multiple_doc` SET `docs`=? WHERE `id`=?");
        $stmt->bind_param("si", $PicFileName, $e_id);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception("Problem in updating! " . strtok($obj->con1->error, "("));
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("edit_muldocs_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        header("location:case_files_intern.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case_files_intern.php");
    }
}

// Function to generate unique file name
function generateUniqueFileName($directory, $filename)
{
    if (file_exists($directory . $filename)) {
        $i = 0;
        $Arr = explode('.', $filename);
        $baseName = $Arr[0];
        $extension = end($Arr);
        do {
            $i++;
            $filename = $baseName . $i . '.' . $extension;
        } while (file_exists($directory . $filename));
    }
    return $filename;
}

?>

<div class="pagetitle">
    <h1>Case Documents</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index_intern.php">Home</a></li>
            <li class="breadcrumb-item">Case Documents</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Case Documents
            </li>
        </ol>
    </nav>
</div>

<!-- End Page Title -->
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body mt-3">
                    <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">
                        <div class="col-md-12" <?php echo (isset($mode)) ? 'hidden' : '' ?>>
                            <label for="docs" class="form-label">Documents</label>
                            <input type="file" class="form-control mb-3" id="docs" name="docs[]"
                                onchange="readURL_multiple(this)" multiple <?php echo (isset($mode)) ? '' : 'required' ?>>
                            <div id="preview_file_div" style="color:blue"></div>
                        </div>
                        <div <?php echo (isset($mode) && $mode == 'edit') ? '' : 'hidden' ?>>
                            <label for="file_name_one">Choose File</label>
                            <input type="file" class="form-control" id="file_name_one" name="file_name_one"
                                onchange="readURL_multiple(this, 'PreviewImage')" />
                        </div>
                        <div class="col-md-12">
                            <img src="<?php echo (isset($mode)) ? 'documents/case/' . $data["docs"] : '' ?>"
                                name="PreviewImage" id="PreviewImage" height="300" width="400"
                                style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>"
                                class="object-cover shadow rounded">
                            <input type="hidden" name="old_img" id="old_img"
                                value="<?php echo (isset($mode) && $mode == 'edit') ? $data["docs"] : '' ?>" />
                        </div>
                        <div class="text-left mt-4">
                            <button type="submit"
                                name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'd-none' : '' ?>">
                                <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="javascript: go_back() ;">
                                Close</button>
                        </div>
                    </form><!-- End Multi Columns Form -->

                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    function readURL_multiple(input) {
        $('#preview_file_div').html(""); // Clear previous preview
        var filesAmount = input.files.length;
        for (let i = 0; i < filesAmount; i++) {
            if (input.files && input.files[i]) {
                var filename = input.files[i].name;
                var extn = filename.split(".").pop().toLowerCase();

                if (["pdf", "doc", "docx", "xlsx", "jpg", "png", "jpeg", "bmp", "txt"].includes(extn)) {
                    document.getElementById('save').disabled = false; // Enable save button if valid file

                    // Display file name with a delete "X" button
                    $('#preview_file_div').append('<p id="file_' + i + '">' + filename +
                        ' <button type="button" class="btn btn-danger btn-sm" onclick="deleteFile(' + i + ')">' +
                        '<i class="bi bi-x-circle"></i></button></p>');
                } else {
                    $('#preview_file_div').html("Please select a valid file (PDF, DOC, and DOCX)");
                    document.getElementById('save').disabled = true;
                    break; // Stop the loop for invalid file
                }
            }
        }
    }

    function deleteFile(index) {
        $('#file_' + index).remove(); // Remove the corresponding file preview

    }
</script>
<script>
    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        eraseCookie("edit_muldocs_id");
        eraseCookie("view_muldocs_id");
        window.location = "case_files_intern.php";
    }
</script>
<?php
include "footer_intern.php";
?>