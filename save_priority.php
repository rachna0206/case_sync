<?php
session_start();
include 'DB_Connect.php';

$obj = new DB_Connect();
$con = $obj->con1;

if (!isset($_SESSION['id'])) {
    echo "Session expired.";
    exit;
}

if (isset($_POST['case_id'], $_POST['priority_number'], $_POST['remark'])) {
    $case_id = (int) $_POST['case_id'];
    $priority_number = (int) $_POST['priority_number'];
    $remark = trim($_POST['remark']);
    $added_by = (int) $_SESSION["id"];
    $priority_id = isset($_POST['priority_id']) && $_POST['priority_id'] != '' ? (int) $_POST['priority_id'] : null;

    if ($priority_id) {
        // Update existing priority
        $stmt = $con->prepare("UPDATE temp_sequence SET sequence = ?, remark = ?, added_by = ? WHERE id = ?");
        $stmt->bind_param("isii", $priority_number, $remark, $added_by, $priority_id);
        if ($stmt->execute()) {
            echo "Priority updated successfully!";
        } else {
            echo "Failed to update priority.";
        }
    } else {
        // Insert new priority
        $stmt = $con->prepare("INSERT INTO temp_sequence (case_id, sequence, added_by, remark) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $case_id, $priority_number, $added_by, $remark);
        if ($stmt->execute()) {
            echo "Priority saved successfully!";
        } else {
            echo "Failed to save priority.";
        }
    }
} else {
    echo "Invalid input.";
}

?>