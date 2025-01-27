<?php
ob_start();
include "db_connect.php";
$obj = new DB_Connect();
date_default_timezone_set('Asia/Kolkata');
 
session_start();

if (!isset($_SESSION["userlogin_CS"])) {
  header("location:login.php"); 
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Case Sync</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favi_32.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">


  <!-- =======================================================
  * Template Name: NiceAdmin
  * Updated: May 30 2023 with Bootstrap v5.3.0
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->

  <script>
    function createCookie(name, value, days) {
      var expires;
      if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
      } else {
        expires = "";
      }
      document.cookie = (name) + "=" + String(value) + expires + ";path=/ ";
    }
    function readCookie(name) {
      var nameEQ = (name) + "=";
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return (c.substring(nameEQ.length, c.length));
      }
      return null;
    }
    function eraseCookie(name) {
      createCookie(name, "", -1);
    }

    $(document).ready(function() {
      get_notification();
    });


    setInterval(get_notification, 3000);


    function get_notification() {
      $.ajax({
        async: true,
        url: 'notification.php?action=get_notification',
        type: 'POST',
        data: "",

        success: function (data) {
          // console.log(data);

          var resp = data.split("@@@@");
          $('#noti_list').html('');
          $('#noti_list').append(resp[0]);

          $('#noti_count').html('');
          $('#count').html('');
          if (resp[1] > 0) {


            $('#noti_count').append(resp[1]);
            $('#count').html(resp[1]);

            playSound();
            //notification_orpel.WAV
          }
        }
      });
    }

    function playSound() {

      $.ajax({
        async: true,
        url: '<?php echo $path ?>notification.php?action=get_Playnotification',
        type: 'POST',
        data: "",

        success: function (data) {
          // console.log(data);

          var resp = data.split("@@@@");

          if (resp[0] > 0) {

            var mp3Source = '<source src="notification_doc_sync.mp3" type="audio/mpeg">';
            document.getElementById("sound").innerHTML = '<audio autoplay="autoplay">' + mp3Source + '</audio>';
            removeplaysound(resp[1]);
          }
        }

      });

    }

    function removeplaysound(ids) {

      $.ajax({
        async: true,
        type: "GET",
        url: "<?php echo $path ?>notification.php?action=removeplaysound",
        data: "id=" + ids,
        async: true,
        cache: false,
        timeout: 50000,

      });

    }

    function read_all() {
      $.ajax({
        async: true,
        type: "POST",
        url: "<?php echo $path ?>notification.php?action=read_all",
        data: "",

        cache: false,
        timeout: 50000,

      });
    }


  </script>
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <!-- logo -->
        <img src="assets/img/" alt="">
        <span class="d-none d-lg-block">CaseSync</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->



    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number" id="noti_count"></span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
           
            <div id="noti_list">

            </div>
          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->



        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/user.png" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $_SESSION["username"] ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li>
              <a class="dropdown-item d-flex align-items-center" href="change_password.php">
                <i class="bi bi-key"></i>
                <span>Change Password</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

    
        <li class="nav-item">
        <a class="nav-link " data-bs-target="#forms-nav" data-bs-toggle="collapse">
          <i class='bx bx-gear'></i><span>Operations</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
          
          <li class="nav-item">
            <a class="nav-link collapsed <?php echo basename($_SERVER["PHP_SELF"]) == "case.php" ? "active" : "" ?>" href="case.php">
              <i class="bi bi-file-earmark"></i>
              <span>Case</span>
            </a>
          </li>
          
           <li class="nav-item">
            <a class="nav-link collapsed <?php echo basename($_SERVER["PHP_SELF"]) == "task.php" ? "active" : "" ?>" href="task.php">
              <i class="bi bi-check"></i>
              <span>Task</span>
            </a>
          </li>
           <li class="nav-item">
        <a class="nav-link collapsed <?php echo basename($_SERVER["PHP_SELF"]) == "case_hist.php" ? "active" : "" ?>" href="case_hist.php">
          <i class="bi bi-clock-history"></i>
          <span>Case History</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed <?php echo basename($_SERVER["PHP_SELF"]) == "case_counter.php" ? "active" : "" ?>" href="case_counter.php">
          <i class="bi bi-alarm"></i>
          <span>Case Counter</span>
        </a>
      </li>
          

        </ul>
      </li>

    <li class="nav-item">
        <a class="nav-link " data-bs-target="#material-nav" data-bs-toggle="collapse">
          <i class="bi bi-card-text"></i><span>Masters</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="material-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
          <li>
            <a href="company.php" class="<?php echo basename($_SERVER["PHP_SELF"]) == "company.php" ? "active" : "" ?>">
                <i class="bi bi-building bi-md"></i><span>Company</span>
            </a>
          </li>
          <li>
            <a href="advocate.php"
              class="<?php echo basename($_SERVER["PHP_SELF"]) == "advocate.php" ? "active" : "" ?>">
              <i class="bi bi-person-fill"></i><span>Advocates -Admin</span>
            </a>
          </li>
          <li>
            <a href="intern.php"
              class="<?php echo basename($_SERVER["PHP_SELF"]) == "intern.php" ? "active" : "" ?>">
              <i class="bi bi-people-fill"></i><span>Interns</span>
            </a>
          </li>
          <li>
            <a href="court.php"
              class="<?php echo basename($_SERVER["PHP_SELF"]) == "court.php" ? "active" : "" ?>">
              <i class="bi bi-bank"></i><span>Courts</span>
            </a>
          </li>
          <li>
            <a href="case_type.php"
              class="<?php echo basename($_SERVER["PHP_SELF"]) == "case_type.php" ? "active" : "" ?>">
              <i class="bi bi-alarm"></i><span>Case Type</span>
            </a>
          </li>
          
          
          <li>
            <a href="stage.php"
              class="<?php echo basename($_SERVER["PHP_SELF"]) == "stage.php" ? "active" : "" ?>">
              <i class="bi bi-stopwatch"></i><span>Stages</span>
            </a>
          </li>
          <li>
            <a href="state.php"
              class="<?php echo basename($_SERVER["PHP_SELF"]) == "state.php" ? "active" : "" ?>">
              <i class="bi bi-geo-alt"></i><span>State</span>
            </a>
          </li>
          <li>
            <a href="city.php"
              class="<?php echo basename($_SERVER["PHP_SELF"]) == "city.php" ? "active" : "" ?>">
              <i class="bi bi-geo-alt"></i><span>City</span>
            </a>
          </li>
          
          
        </ul>
      </li>

      
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main min-vh-100">
  <div id="sound"></div>