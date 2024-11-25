<?php
include "header.php";

if (isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT *,case.id as case_id, sr_date AS rem_days, DATEDIFF(CURRENT_DATE, sr_date) AS days_diff, court.name as crt_name, city.name as city_name FROM `case` inner join `court` on case.court_name = court.id  inner join `city` on case.city_id = city.id where case.id = ? ORDER BY case.id DESC");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Case Counter</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Case Counter</li>
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
                    <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">
                            <div class="col-md-12">
                                <label for="title" class="form-label">Case No</label>
                                <input type="text" class="form-control" id="case_no" name="case_no"
                                    value="<?php echo (isset($mode)) ? $data['case_no'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label"> Court</label>
                                <input type="text" class="form-control" id="court" name="court"
                                    value="<?php echo (isset($mode)) ? $data['crt_name'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label"> Opponent</label>
                                <input type="text" class="form-control" id="opponent" name="opponent"
                                    value="<?php echo (isset($mode)) ? $data['opp_name'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                    value="<?php echo (isset($mode)) ? $data['city_name'] : '' ?>" 
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label"> Remainings Days</label>
                                <input type="text" class="form-control" id="rem_days" name="rem_days"
                                    value="<?php echo (isset($mode)) ? 45 - $data['days_diff'] : '' ?>"
                                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>>
                            </div>

                        <div class="text-left mt-4">
                            <button type="submit"
                                name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'd-none' : '' ?>"><?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
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

<script>
function go_back() {
    eraseCookie("view_id");
    window.location = "case_counter.php";
}

</script>
<?php
include "footer.php";
?>