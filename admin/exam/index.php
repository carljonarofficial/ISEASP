<?php
require_once("../../include/initialize.php");
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$view = (isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : '';
$title = "Examination";
$subtitle = "";

switch ($view) {
    case 'schedule':
        $content = 'schedule.php';
        $subtitle = 'Exam Schedule';
        break;
    case 'results':
        $content = 'results.php';
        $subtitle = 'Exam Results';
        break;
    case 'add':
        $content = 'add_result.php';
        $subtitle = 'Add Exam Result';
        break;
    case 'edit':
        $content = 'edit_result.php';
        $subtitle = 'Edit Exam Result';
        break;
    case 'view':
        $content = 'view.php';
        $subtitle = 'Exam Details';
        break;
    case 'batch_add':
        $content = 'batch_add.php';
        $subtitle = 'Batch Add/Edit Results';
        break;
    case 'batch_reschedule':
        $content = 'batch_reschedule.php';
        $subtitle = 'Batch Reschedule Exams';
        break;
    case 'batch_print':
        $content = 'batch_print.php';
        $subtitle = 'Batch Exam Slip Print';
        break;
    case 'batch_cancel':
        $content = 'batch_cancel.php';
        $subtitle = 'Batch Cancel Exams';
        break;
    default:
        $content = 'schedule.php';
        $subtitle = 'Exam Schedule';
}

require_once("../theme/templates.php");
?>