<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ISEASP - Ilocos Sur Educational Assistance & Scholarship Program</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo web_root;?>uploads/iseasp_logo.ico" type="image/png">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?php echo web_root;?>bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/font-awesome/css/font-awesome.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo web_root;?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="<?php echo web_root;?>dist/css/skins/_all-skins.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/iCheck/flat/blue.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/datatables/dataTables.bootstrap.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/datepicker/datepicker3.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- Bootstrap WYSIHTML5 -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <!-- Custom CSS -->
    <style>
        .skin-blue .main-header .navbar {
            background-color: #3E64D6;
        }
        .skin-blue .main-header .logo {
            background-color: #29428E;
            color: #fff;
            border-bottom: 0 solid transparent;
            font-weight: 700;
        }
        .skin-blue .main-header .logo:hover {
            background-color: #1e336e;
        }
        .profile-img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border: 3px solid #4e73df;
            padding: 3px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .badge-notification {
            background-color: #e74c3c;
            position: absolute;
            top: 8px;
            right: 8px;
        }
        .sidebar-menu > li.active > a {
            border-left-color: #4e73df;
            background: rgba(78, 115, 223, 0.1) !important;
            color: #4e73df !important;
        }
        .content-header {
            background: #ecf0f5;
            padding: 15px;
            border-bottom: 1px solid #d2d6de;
        }
        .box {
            border-top-color: #3E64D6;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        @media (min-width: 768px) {
            .sidebar-mini.sidebar-collapse .main-header .logo {
                padding-left: 6px;
            }
        }
        /* Button and UI refinement */
        .btn-success { background-color: #4e73df; border-color: #2e59d9; }
        .btn-success:hover { background-color: #2e59d9; border-color: #2653d4; }
        .bg-green { background-color: #4e73df !important; }
    </style>
</head>

<body class="hold-transition skin-blue fixed sidebar-mini">
    <div class="wrapper">

        <!-- Header -->
        <header class="main-header">
            <a href="<?php echo web_root;?>admin/" class="logo">
                <img src="<?php echo web_root;?>uploads/iseasp_logo.png" alt="ISEASP Logo" width="40" height="40" class="d-inline-block align-text-top"> ISEASP
            </a>

            <nav class="navbar navbar-static-top" role="navigation">
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <?php
                        $user = New User();
                        $singleuser = $user->single_user($_SESSION['ADMIN_USERID']);
                        
                        // Get notification count (you'll need to implement this)
                        $notification_count = 0; // Placeholder
                        ?>
                        
                        <!-- Notifications Dropdown -->
                        <!-- <li class="dropdown notifications-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-bell-o"></i>
                                <?php if($notification_count > 0): ?>
                                <span class="label label-warning"><?php echo $notification_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">You have <?php echo $notification_count; ?> notifications</li>
                                <li>
                                    <ul class="menu">
                                        <li><a href="#"><i class="fa fa-users text-aqua"></i> New applications pending</a></li>
                                        <li><a href="#"><i class="fa fa-file text-green"></i> Requirements to verify</a></li>
                                    </ul>
                                </li>
                                <li class="footer"><a href="<?php echo web_root;?>admin/notifications/">View all</a></li>
                            </ul>
                        </li> -->

                        <!-- User Menu -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="<?php echo web_root.'admin/user/photos/'. $singleuser->PICLOCATION;?>" class="user-image" alt="User Image">
                                <span class="hidden-xs"><?php echo $singleuser->FULLNAME; ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header">
                                    <img src="<?php echo web_root.'admin/user/photos/'. $singleuser->PICLOCATION;?>" class="img-circle" alt="User Image">
                                    <p>
                                        <?php echo $singleuser->FULLNAME; ?>
                                        <small><?php echo $singleuser->ROLE; ?></small>
                                    </p>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="<?php echo web_root.'admin/user/index.php?view=view&id='.$_SESSION['ADMIN_USERID'];?>" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="<?php echo web_root;?>admin/logout.php" class="btn btn-danger btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #3E64D6; color: white;">
                        <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-key"></i> Change Password</h4>
                    </div>
                    <form action="<?php echo web_root;?>admin/user/controller.php?action=changepassword" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="main-sidebar">
            <section class="sidebar">
                <!-- User Panel -->
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="<?php echo web_root.'admin/user/photos/'. $singleuser->PICLOCATION;?>" class="img-circle" alt="User Image" width="45" height="45">
                    </div>
                    <div class="pull-left info">
                        <p><?php echo $singleuser->FULLNAME; ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>

                <!-- Search Form -->
                <!-- <form action="#" method="get" class="sidebar-form">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search...">
                        <span class="input-group-btn">
                            <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </form> -->

                <!-- Sidebar Menu -->
                <ul class="sidebar-menu">
                    <li class="header">MAIN NAVIGATION</li>

                    <!-- Dashboard -->
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'applications') === false && strpos($_SERVER['REQUEST_URI'], 'checklist') === false && strpos($_SERVER['REQUEST_URI'], 'municipalities') === false && strpos($_SERVER['REQUEST_URI'], 'system') === false) ? "active" : "";?>">
                        <a href="<?php echo web_root;?>admin/">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            <span class="pull-right-container">
                                <small class="label pull-right bg-blue">Home</small>
                            </span>
                        </a>
                    </li>

                    <!-- Applicants & Requirements -->
                    <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'applications') !== false || (strpos($_SERVER['REQUEST_URI'], 'checklist') !== false && !(basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['view']) && $_GET['view'] == 'manage_req'))) ? "active" : ""; ?>">
                        <a href="#">
                            <i class="fa fa-users"></i> <span>Applicants</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo web_root;?>admin/applications/"><i class="fa fa-circle-o"></i> All Applicants</a></li>
                            <li><a href="<?php echo web_root;?>admin/applications/index.php?view=add"><i class="fa fa-circle-o"></i> Add New Applicant</a></li>
                            <!-- <li><a href="<?php echo web_root;?>admin/checklist/"><i class="fa fa-circle-o"></i> Applicant Checklist</a></li> -->
                            <li><a href="<?php echo web_root;?>admin/scholars/index.php?view=payroll"><i class="fa fa-money"></i> Payroll Management</a></li>
                        </ul>
                    </li>

                    <!-- Examination -->
                    <!-- <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'exam') !== false) ? "active" : "";?>">
                        <a href="#">
                            <i class="fa fa-pencil-square-o"></i> <span>Examination</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo web_root;?>admin/exam/index.php?view=schedule"><i class="fa fa-circle-o"></i> Exam Schedule</a></li>
                            <li><a href="<?php echo web_root;?>admin/exam/index.php?view=results"><i class="fa fa-circle-o"></i> Exam Results</a></li>
                        </ul>
                    </li> -->

                    <!-- Interview -->
                    <!-- <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'interview') !== false) ? "active" : "";?>">
                        <a href="#">
                            <i class="fa fa-comments-o"></i> <span>Interview</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo web_root;?>admin/interview/index.php?view=schedule"><i class="fa fa-circle-o"></i> Interview Schedule</a></li>
                            <li><a href="<?php echo web_root;?>admin/interview/index.php?view=results"><i class="fa fa-circle-o"></i> Interview Results</a></li>
                        </ul>
                    </li> -->

                    <!-- Final Evaluation -->
                    <!-- <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'evaluation') !== false) ? "active" : "";?>">
                        <a href="#">
                            <i class="fa fa-gavel"></i> <span>Final Evaluation</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo web_root;?>admin/evaluation/"><i class="fa fa-circle-o"></i> For Evaluation</a></li>
                        </ul>
                    </li> -->

                    <!-- Qualified Applicants -->
                    <!-- <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'evaluation') !== false) ? "active" : "";?>">
                        <a href="#">
                            <i class="fa fa-check-circle-o"></i> <span>Qualified</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo web_root;?>admin/evaluation/index.php?view=qualified"><i class="fa fa-users"></i> Qualified Applicants</a></li>
                        </ul>
                    </li> -->

                    <!-- Scholars -->
                    <!-- <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'scholars') !== false) ? "active" : "";?>">
                        <a href="#">
                            <i class="fa fa-graduation-cap"></i> <span>Scholars</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo web_root;?>admin/scholars/index.php?view=list"><i class="fa fa-users"></i> Active Scholars</a></li>
                            <li><a href="<?php echo web_root;?>admin/scholars/index.php?view=history"><i class="fa fa-history"></i> Scholarship History</a></li>
                            <li><a href="<?php echo web_root;?>admin/scholars/index.php?view=graduates"><i class="fa fa-trophy"></i> Graduates</a></li>
                            <li class="divider"></li>
                            <li><a href="<?php echo web_root;?>admin/scholars/index.php?view=payroll"><i class="fa fa-money"></i> Payroll Management</a></li>
                        </ul>
                    </li> -->

                    <!-- Municipalities -->
                    <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'municipalities') !== false) ? "active" : "";?>">
                        <a href="<?php echo web_root;?>admin/municipalities/">
                            <i class="fa fa-map-marker"></i> <span>Municipalities</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <!-- <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'reports') !== false) ? "active" : "";?>">
                        <a href="#">
                            <i class="fa fa-bar-chart"></i> <span>Reports</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo web_root;?>admin/reports/index.php?view=statistics"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                            <li><a href="<?php echo web_root;?>admin/reports/index.php?view=applicants"><i class="fa fa-users"></i> Applicants Report</a></li>
                            <li><a href="<?php echo web_root;?>admin/reports/index.php?view=scholars"><i class="fa fa-graduation-cap"></i> Scholars Report</a></li>
                            <li><a href="<?php echo web_root;?>admin/reports/index.php?view=district"><i class="fa fa-sun-o"></i> Per District</a></li>
                            <li><a href="<?php echo web_root;?>admin/reports/index.php?view=municipality"><i class="fa fa-map-marker"></i> Per Municipality</a></li>
                        </ul>
                    </li> -->

                    <!-- System Settings -->
                    <li class="treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'system') !== false || (basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['view']) && $_GET['view'] == 'manage_req' && strpos($_SERVER['REQUEST_URI'], 'checklist') !== false)) ? "active" : "";?>">
                        <a href="#">
                            <i class="fa fa-cogs"></i> <span>System</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <!-- <li><a href="<?php echo web_root;?>admin/system/index.php?view=users"><i class="fa fa-users"></i> Manage Users</a></li> -->
                             <li><a href="<?php echo web_root;?>admin/system/index.php?view=list"><i class="fa fa-users"></i> Manage Users</a></li>
                             <li><a href="<?php echo web_root; ?>admin/checklist/index.php?view=manage_req"><i class="fa fa-circle-o"></i> Manage
                                    Requirements</a></li>
                            <li><a href="<?php echo web_root;?>admin/system/index.php?view=school_years"><i class="fa fa-calendar"></i> School Years</a></li>
                            <li><a href="<?php echo web_root;?>admin/system/index.php?view=settings"><i class="fa fa-wrench"></i> System Settings</a></li>
                            <li><a href="<?php echo web_root;?>admin/system/index.php?view=logs"><i class="fa fa-history"></i> Activity Logs</a></li>
                            <!-- <li><a href="<?php echo web_root;?>admin/system/index.php?view=backup"><i class="fa fa-database"></i> Database Backup</a></li> -->
                        </ul>
                    </li>
                </ul>

            </section>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <h1>
                    <?php echo isset($title) ? $title : 'Dashboard'; ?>
                    <small><?php echo isset($subtitle) ? $subtitle : 'Control Panel'; ?></small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo web_root;?>admin/"><i class="fa fa-dashboard"></i> Home</a></li>
                    <?php
                    if (isset($title) && $title != 'Home') {
                        echo '<li class="active">' . $title . '</li>';
                        if (isset($_GET['view'])) {
                            echo '<li class="active">' . ucwords(str_replace('_', ' ', $_GET['view'])) . '</li>';
                        }
                    }
                    ?>
                </ol>
            </section>

            <!-- Main Content -->
            <section class="content">
                <?php 
                check_message();
                if (isset($content) && file_exists($content)) {
                    require_once $content;
                } else {
                    echo '<div class="alert alert-danger">Content file not found!</div>';
                }
                ?>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <!-- PGIS and One Ilocos Sur Logo -->
            <img src="<?php echo web_root; ?>uploads/pgis_logo.png" alt="PGIS Logo" style="height: 40px;">
            <img src="<?php echo web_root; ?>uploads/oneilocossur_logo.png" alt="One Ilocos Sur Logo" style="height: 40px;">
            <strong>&copy; <?php echo date('Y'); ?> Provincial Government of Ilocos Sur - ISEASP. All rights reserved.</strong>
        </footer>
    </div>

    <script src="<?php echo web_root;?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="<?php echo web_root;?>bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo web_root;?>dist/js/app.min.js"></script>

    <script src="<?php echo web_root;?>plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo web_root;?>plugins/datatables/dataTables.bootstrap.min.js"></script>

    <script src="<?php echo web_root;?>plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="<?php echo web_root;?>plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <script src="<?php echo web_root;?>plugins/fastclick/fastclick.min.js"></script>
    <script src="<?php echo web_root;?>dist/js/demo.js"></script>

    <script>
    $(function () {
        $('.datatable').each(function () {
            if ($.fn.DataTable && !$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable({
                    paging: true,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    responsive: true
                });
            }
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        $('.sidebar-menu li.treeview').each(function () {
            if ($(this).hasClass('active')) {
                $(this).addClass('menu-open');
            }
        });

        $('.delete-confirm').on('click', function(e) {
            if (!confirm("Are you sure you want to delete this item?")) {
                e.preventDefault();
            }
        });
    });

    function showNotification(message, type) {
        type = type || 'success';

        if (!$.notify) {
            alert(message);
            return;
        }

        var icon = (type === 'success') ? 'fa-check' : 'fa-warning';
        var title = (type === 'success') ? 'Success!' : 'Error!';

        $.notify({
            icon: icon,
            title: title,
            message: message
        },{
            type: type,
            allow_dismiss: true,
            newest_on_top: true,
            placement: {
                from: "top",
                align: "right"
            },
            animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            }
        });
    }
    </script>

    <?php if (isset($page_script) && !empty($page_script)) { echo $page_script; } ?>

</body>
</html>