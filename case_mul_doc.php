<?php
include "header.php";

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
if (isset($_REQUEST["save"])) {

    $multi_docs = $_FILES['docs']['name'];
    $multi_docs = str_replace(' ', '_', $multi_docs);
    $multi_docs_path = $_FILES['docs']['tmp_name'];
    $old_img = $_REQUEST['old_img'];


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
        if (file_exists("documents/case/" . $old_img)) {
            unlink("documents/case/" . $old_img);
        }
        move_uploaded_file($multi_docs_path, "documents/case/" . $DocFileName);
    } else {
        $DocFileName = $old_img;
    }

    try {
        // Handle multiple document uploads
        foreach ($_FILES["docs"]['name'] as $key => $value) {
            if ($_FILES["docs"]['name'][$key] != "") {
                $PicSubImage = $_FILES["docs"]["name"][$key];
                // Generate unique file name if file already exists
                $SubImageName = generateUniqueFileName("documents/case/", $PicSubImage);
                $SubImageTemp = $_FILES["docs"]["tmp_name"][$key];
                $SubImageName = str_replace(' ', '_', $SubImageName);

                // Move uploaded file
                move_uploaded_file($SubImageTemp, "documents/case/" . $SubImageName);

                //echo("INSERT INTO `multiple_doc`(`c_id`, `docs`) VALUES ($case_id, $SubImageName)");
                $stmt_image = $obj->con1->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`) VALUES (?, ?)");
                $stmt_image->bind_param("is", $case_id, $SubImageName);
                $Resp = $stmt_image->execute();
                $stmt_image->close();
            }
        }

        if (!$Resp) {
            throw new Exception("Problem in adding! " . strtok($obj->con1->error, "("));
        }
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data", time() + 3600, "/");
        header("location:case.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case.php");
    }
}

// Update documents
if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_muldocs_id'];
    $file_name_one = $_FILES['file_name_one']['name'];
    $file_name_one = str_replace(' ', '_', $file_name_one);
    $file_path_one = $_FILES['file_name_one']['tmp_name'];
    $old_img = $_REQUEST['old_img'];

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
        header("location:case_add.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:case_add.php");
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
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case Documents</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Case Documents</li>
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
                            <button type="button" class="btn btn-danger" onclick="window.location='case_add.php'">
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

                if (["pdf", "doc", "docx"].includes(extn)) {
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
        // Optionally, you can manage the files array here to keep track of files to be uploaded
        // For example, you could store the remaining file names in a hidden input if needed
    }
</script>

<?php
include "footer.php";
?>