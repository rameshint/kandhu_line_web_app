<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: index.php");
  exit();
}
?>
<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Finance | <?= $title ?></title>
  <!--begin::Primary Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="title" content="Finance | <?= $title ?>" />
  <meta name="author" content="Ramesh N" />
  <meta
    name="description"
    content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS." />
  <meta
    name="keywords"
    content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard" />
  <!--end::Primary Meta Tags-->
  <!--begin::Fonts-->
  <link
    rel="stylesheet"
    href="dist/css/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
    crossorigin="anonymous" />
  <!--end::Fonts-->
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link
    rel="stylesheet"
    href="dist/css/overlayscrollbars.min.css"
    integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->
  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link
    rel="stylesheet"
    href="dist/css/bootstrap-icons.min.css"
    integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->
  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="dist/css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
  <!-- apexcharts -->
  <link
    rel="stylesheet"
    href="dist/css/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
    crossorigin="anonymous" />
  <!-- jsvectormap -->
  <link
    rel="stylesheet"
    href="dist/css/jsvectormap.min.css"
    integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
    crossorigin="anonymous" />
  <link href="dist/css/dataTables.bootstrap5.css" rel="stylesheet" />
  <link rel="stylesheet" href="dist/css/fixedColumns.bootstrap5.css">
  <style>
    @media print {
      .table-33 {
        width: 100%;
        flex: 30%;
      }

      .row {
        display: flex;
      }

      .col-md-4 {
        flex: 33%;
      }

      .table-33 th,
      .table-33 td {
        padding: 3px;
        white-space: no-wrap;
        font-size: small;
      }
    }

    .align-right {
      text-align: right;
    }
    .white-space-nowrap{
      white-space: nowrap;
    }
    .overdue-ind{
      background-color: red !important;
    }
    #loader {
      transition: opacity 0.5s ease;
    }
    #loader.fade-out {
    opacity: 0;
    pointer-events: none;
  }
  </style>

</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">
    <!--begin::Header-->
    <nav class="app-header navbar navbar-expand bg-body">
      <!--begin::Container-->
      <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
              <i class="bi bi-list"></i>
            </a>
          </li>
          <li class="nav-item d-none d-md-block"><a href="<?=$_SESSION['home_page']?>" class="nav-link">Home</a></li>

        </ul>
        <!--end::Start Navbar Links-->
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
          <!--begin::Navbar Search-->
          <!--<li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
              <i class="bi bi-search"></i>
            </a>
          </li>-->
          <!--end::Navbar Search-->
          <!--begin::Fullscreen Toggle-->
          <li class="nav-item" style="align-content: center">
            <?php
            if($_SESSION['role'] == 'admin'){
              $lines = ['Daily', 'Weekly', 'Monthly'];
              
              ?>
              <select class="form-control" style="cursor: pointer" onchange="switchLine()" id="global-line-type">
                <?php
                foreach($lines as $line){
                  if($line == $_SESSION['line']){
                    echo '<option selected>'.$line.'</option>';
                  }else{
                    echo '<option>'.$line.'</option>';
                  }
                }
                ?>
              </select>
              <?php
            }else{
            ?>
            <b><?php
            echo strtoupper($_SESSION['line']);
            ?></b>
            <?php 
            }
            ?>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              <i title="Logout" class="bi bi-box-arrow-right"></i>
              <i class="fa-solid fa-right-from-bracket"></i>
            </a>
          </li>
          <!--end::Fullscreen Toggle-->
          <!--begin::User Menu Dropdown-->

          <!--end::User Menu Dropdown-->
        </ul>
        <!--end::End Navbar Links-->
      </div>
      <!--end::Container-->
    </nav>
    <!--end::Header-->
    <!--begin::Sidebar-->
    <aside class="app-sidebar bg-body-secondary shadow no-print" data-bs-theme="dark">
      <!--begin::Sidebar Brand-->
      <div class="sidebar-brand">
        <!--begin::Brand Link-->
        <a href="<?=$_SESSION['home_page']?>" class="brand-link">
          <!--begin::Brand Image-->
          <img
            src="dist/assets/img/AdminLTELogo.png"
            alt="AdminLTE Logo"
            class="brand-image opacity-75 shadow" />
          <!--end::Brand Image-->
          <!--begin::Brand Text-->
          <span class="brand-text fw-light">Dhanalakshmi &nbsp;<b>Fin</b></a></span>
        <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
      </div>
      <!--end::Sidebar Brand-->
      <!--begin::Sidebar Wrapper-->
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <!--begin::Sidebar Menu-->
          <?php
          $menu = [
            'home.php' => 'Dashboard',
            'collections.php' => 'Collections',
            'loans.php' => 'Loans',
            'customers.php' => 'Customers',
            'expenses.php' => 'Expenses',
            'day_closure.php' => 'Day Closure',
            'temporary_loans.php' => 'Temporary Loans',
            'investments.php' => 'Investments',
            'agents.php' => 'Agents',
            'Reports' => [
              'daily_print.php' => 'Collection Print',
              'day_closure_report.php' => 'Day Closure Report',
              'customer_loans_report.php' => 'Loans Report',
              'open_loan_collection_report.php' => 'Collection Report',
              'monthly_collection_report.php' => 'Monthly Collection Report',
              'monthly_ledger.php' => 'Monthly Ledger',
              'monthly_expense_report.php' => 'Monthly Expense Report',
            ]
          ];

          $privileges = [
            'Collections',
            'Loans',
            'Customers',
            'Expenses',
            'Day Closure',
            'Temporary Loans',
            'Investments',
            'Reports' => ['Collection Print', 'Collection Report','Day Closure Report']
          ];

          

          $icons = [
            'home.php' => 'bi-speedometer2',
            'customers.php' => 'bi-people-fill',
            'investments.php' => 'bi-graph-up-arrow',
            'expenses.php' => 'bi-receipt',
            'loans.php' => 'bi-cash-stack',
            'temporary_loans.php' => 'bi-cash',
            'collections.php' => 'bi-wallet2',
            'agents.php' => 'bi-person-badge-fill',
            'day_closure.php' => 'bi-calendar-check',
            'day_closure_report.php' => 'bi-journal-text',
            'customer_loans_report.php' => 'bi-journal-check',
            'daily_print.php' => 'bi-printer',
            'open_loan_collection_report.php' => 'bi-wallet',
            'monthly_collection_report.php' => 'bi-calendar3',
            'monthly_expense_report.php' => 'bi-journal-arrow-down',
            'monthly_ledger.php' => 'bi-calculator',
          ];

          ?>
          <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="menu"
            data-accordion="false">
            <?php
            $filename = basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH));
            foreach ($menu as $file => $menu_name) {
              $icon = 'bi-speedometer2';

              if (is_array($menu_name)) {
                if ($_SESSION['role'] == 'admin' || ($_SESSION['role'] == 'agent' && array_key_exists($file, $privileges))) {
                  
                  $menu_open = '';
                  $active = '';
                  if (array_key_exists($filename, $menu_name)) {
                    $menu_open = 'menu-open';
                    $active = 'active';
                  }



                  echo '<li class="nav-item ' . $menu_open . '">
                  <a href="#" class="nav-link ' . $active . '">
                    <i class="nav-icon bi bi-box-seam-fill"></i>
                    <p>
                      ' . $file . '
                      <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">';
                  $root_file = $file;
                  foreach ($menu_name as $file => $menu) {
                    if ($_SESSION['role'] == 'admin' || ($_SESSION['role'] == 'agent' && in_array($menu, $privileges[$root_file]))) {
                      if (array_key_exists($file, $icons))
                        $icon = $icons[$file];

                      $active = '';
                      if ($filename == $file) {
                        $active = 'active';
                      }

                      echo '<li class="nav-item">
                          <a href="' . $file . '" class="nav-link ' . $active . '" title="' . $menu . '">
                            <i class="nav-icon bi ' . $icon . '"></i>
                            <p>
                              ' . $menu . '
                            </p>
                          </a>
                        </li>';
                    }
                  }
                  echo '</ul>
              </li>';
                }
              } else {


                if ($_SESSION['role'] == 'admin' || ($_SESSION['role'] == 'agent' && in_array($menu_name, $privileges))) {
                  $active = '';
                  if ($filename == $file) {
                    $active = 'active';
                  }
                  if (array_key_exists($file, $icons))
                    $icon = $icons[$file];
                  echo '<li class="nav-item">
                        <a href="' . $file . '" class="nav-link ' . $active . '">
                          <i class="nav-icon bi ' . $icon . '"></i>
                          <p>
                            ' . $menu_name . '
                          </p>
                        </a>
                      </li>';
                }
              }
            }
            ?>
          </ul>
          <!--end::Sidebar Menu-->
        </nav>
      </div>
      <!--end::Sidebar Wrapper-->
    </aside>
    <!--end::Sidebar-->
    <!--begin::App Main-->
    <main class="app-main">
      <!--begin::App Content Header-->
      <div class="app-content-header" style="display:none">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Row-->
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0"><?= $title ?></h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?=$_SESSION['home_page']?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
              </ol>
            </div>
          </div>
          <!--end::Row-->
        </div>
        <!--end::Container-->
      </div>
      <!--end::App Content Header-->
      <!--begin::App Content-->
      <div class="app-content" style="padding: 1rem 0.5rem !important">
        <!--begin::Container-->
        <div class="container-fluid">