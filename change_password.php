<?php
include "header.php";
if (isset($_REQUEST["update"])) {
    $old_pass = $_REQUEST["oldpassword"];
    $new_pass = $_REQUEST["newpassword"];
    $conf_pass = $_REQUEST["confpassword"];
    $username = $_SESSION["username"];

    $stmt1 = $obj->con1->prepare("SELECT `password` FROM `admin` WHERE `username`=?");
    $stmt1->bind_param("s", $username);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $row = $result->fetch_assoc();
    $pass = $row["password"];
    $stmt1->close();

    // echo ("UPDATE `admin` SET `password`=$new_pass WHERE `username`=$username");
    if ($old_pass == $pass && $new_pass != $old_pass) {
        if ($new_pass == $conf_pass) {
            try {
                $stmt = $obj->con1->prepare("UPDATE `admin` SET `password`=? WHERE `username`=?");
                $stmt->bind_param("ss", $new_pass, $username);
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
                header("location:index.php");
            } else {
                setcookie("msg", "fail", time() + 3600, "/");
                // header("location:index.php");
            }
        } else {
            setcookie("msg", "fail", time() + 3600, "/");
            // header("location:index.php");
        }
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
    }
}
?>

<?php
if (isset($_COOKIE["msg"])) {
    if ($_COOKIE["msg"] == 'data') {
?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            Data Added Successfully !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
    }
    if ($_COOKIE["msg"] == 'update') {
    ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            Data Updated Successfully !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
    }
    if ($_COOKIE["msg"] == 'data_del') {
    ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            Data Deleted Successfully !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
    }
    if ($_COOKIE["msg"] == 'fail') {
    ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-octagon me-1"></i>
            An Error Occurred !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
    }
    setcookie("msg", "", time() - 3600, "/");
}
?>
<div class="pagetitle">
    <h1>Change Password</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Change Password</li>
        </ol>
    </nav>
</div>
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <form class="row g-3 pt-3" method="post">
                        <div class="col-md-8">
                            <label for="oldpassword" class="form-label">Old Password</label>
                            <input required type="password" class="form-control" placeholder="Enter Old Password" id="oldpassword" name="oldpassword">
                        </div>
                        <div class="col-md-8">
                            <label for="newpassword" class="form-label">New Password</label>
                            <input required type="password" class="form-control" placeholder="Enter New Password" id="newpassword" name="newpassword">
                        </div>
                        <div class="col-md-8">
                            <label for="confpassword" class="form-label">Confirm Password</label>
                            <input required type="password" class="form-control" placeholder="Enter New Password Again" id="confpassword" name="confpassword">
                        </div>

                        <div class="text-left mt-4">
                            <button type="submit" name="update" id="save" class="btn btn-success">
                                Update
                            </button>
                            <button type="button" class="btn btn-danger" onclick="window.location='index.php'"> Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include "footer.php";
?>