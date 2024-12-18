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



if (isset($_SESSION["excel_msg"])) {
?>
  <div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Excel Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php echo $_SESSION["excel_msg"]; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    // Automatically show the modal after page load
    document.addEventListener("DOMContentLoaded", function () {
      var myModal = new bootstrap.Modal(document.getElementById('excelModal'));
      myModal.show();
    });
  </script>
<?php
  unset($_SESSION['excel_msg']);
}
?>