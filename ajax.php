<?php 
    include "header.php";
    include "alert.php";

    $con = mysqli_connect("localhost","root","","pragmanx_case_sync");


    if (isset($_REQUEST["cs_ty_id"])) {

        $cs_id = $_REQUEST["cs_ty_id"];
        $stmt = $obj->con1->prepare("SELECT * FROM `case` WHERE `case_type` = ?");
        $stmt->bind_param('i',$cs_id);
        $stmt->execute();
        $Resp = $stmt->get_result();
        ?>

        <option value="">Select Case</option>
        <?php     

        while($row = mysqli_fetch_array($Resp)) {
        
        ?>

            <option value="<?= $row["id"] ?>"><?= $row["case_no"] ?></option>

        <?php     
        }
    }
 
    else {

    $stmt = $obj->con1->prepare("SELECT * FROM `case_type`");
    $stmt->execute();
    $Resp = $stmt->get_result();
?>

    <option value="">Select Case Type</option>

<?php
    while ($row = mysqli_fetch_array($Resp)) {

?>
    <option value="<?= $row["id"] ?>"><?= $row["case_type"] ?></option>

<?php
    }
    }
?>