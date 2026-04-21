<?php
require_once("../../include/initialize.php");
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

// Only Super Admin can access system settings
if ($_SESSION['ADMIN_ROLE'] != 'Super Admin') {
    message("Access denied. Super Admin only.", "error");
    redirect(web_root . "admin/index.php");
}

$view = (isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : '';
$title = "System Management";
$subtitle = "";

switch ($view) {
    case 'users':
        $content = 'users.php';
        $subtitle = 'Manage Users';
        break;
    case 'add_user':
        $content = 'add_user.php';
        $subtitle = 'Add New User';
        break;
    case 'edit_user':
        $content = 'edit_user.php';
        $subtitle = 'Edit User';
        break;
    case 'school_years':
        $content = 'school_years.php';
        $subtitle = 'Manage School Years';
        break;
    case 'settings':
        $content = 'settings.php';
        $subtitle = 'System Settings';
        break;
    case 'logs':
        $content = 'logs.php';
        $subtitle = 'Activity Logs';
        break;
    case 'backup':
        $content = 'backup.php';
        $subtitle = 'Database Backup';
        break;
    default:
        $content = 'users.php';
        $subtitle = 'Manage Users';
}

require_once("../theme/templates.php");
?>