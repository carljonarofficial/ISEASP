<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    redirect("index.php");
}

global $mydb;

// Get applicant details
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID = $id");
$mydb->executeQuery();
$applicant = $mydb->loadSingleResult();

if (!$applicant) {
    message("Applicant not found!", "error");
    redirect("index.php");
}

// Get requirements with status
$sql = "
    SELECT 
        r.*,
        COALESCE(c.IS_SUBMITTED, 0) as IS_SUBMITTED,
        COALESCE(c.IS_VERIFIED, 0) as IS_VERIFIED,
        c.REMARKS,
        c.VERIFIED_DATE,
        c.VERIFIED_BY,
        u.FULLNAME as VERIFIED_BY_NAME
    FROM tbl_requirement r
    LEFT JOIN tbl_applicant_requirement_checklist c 
        ON r.REQUIREMENT_ID = c.REQUIREMENT_ID AND c.APPLICANTID = $id
    LEFT JOIN tblusers u ON c.VERIFIED_BY = u.USERID
    ORDER BY r.CATEGORY, r.DISPLAY_ORDER
";

$mydb->setQuery($sql);
$mydb->executeQuery();
$requirements = $mydb->loadResultList();

// If the main query fails or returns empty, try without the users join
if (empty($requirements)) {
    $sql = "
        SELECT 
            r.*,
            COALESCE(c.IS_SUBMITTED, 0) as IS_SUBMITTED,
            COALESCE(c.IS_VERIFIED, 0) as IS_VERIFIED,
            c.REMARKS,
            c.VERIFIED_DATE,
            c.VERIFIED_BY
        FROM tbl_requirement r
        LEFT JOIN tbl_applicant_requirement_checklist c 
            ON r.REQUIREMENT_ID = c.REQUIREMENT_ID AND c.APPLICANTID = $id
        ORDER BY r.CATEGORY, r.DISPLAY_ORDER
    ";
    
    $mydb->setQuery($sql);
    $mydb->executeQuery();
    $requirements = $mydb->loadResultList();
}

// Calculate progress
$total_req = count($requirements);
$verified_req = 0;
$submitted_req = 0;

foreach ($requirements as $req) {
    if (isset($req->IS_VERIFIED) && $req->IS_VERIFIED) $verified_req++;
    if (isset($req->IS_SUBMITTED) && $req->IS_SUBMITTED) $submitted_req++;
}

$progress_percentage = ($total_req > 0) ? round(($verified_req / $total_req) * 100) : 0;
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Requirements Checklist - <?= htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME) ?></h1>
    </div>
</div>

<!-- Action Buttons -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <a href="index.php?view=manage&id=<?= $id ?>" class="btn btn-primary">
            <i class="fa fa-edit"></i> Manage Requirements
        </a>
        <a href="../applications/" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Applicant Info -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <i class="fa fa-user"></i> Applicant Information
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Full Name:</strong><br>
                        <?= htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME . ' ' . ($applicant->MIDDLENAME ?? '')) ?>
                    </div>
                    <div class="col-md-2">
                        <strong>Municipality:</strong><br>
                        <?= htmlspecialchars($applicant->MUNICIPALITY ?? 'N/A') ?>
                    </div>
                    <div class="col-md-3">
                        <strong>School:</strong><br>
                        <?= htmlspecialchars($applicant->SCHOOL ?? 'N/A') ?>
                    </div>
                    <div class="col-md-2">
                        <strong>Course:</strong><br>
                        <?= htmlspecialchars($applicant->COURSE ?? 'N/A') ?>
                    </div>
                    <div class="col-md-2">
                        <strong>Year Level:</strong><br>
                        <?= htmlspecialchars($applicant->YEARLEVEL ?? 'N/A') ?>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-12">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" style="width: <?= $progress_percentage ?>%">
                                <?= $progress_percentage ?>% Complete (<?= $verified_req ?>/<?= $total_req ?>)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Requirements Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-check-square-o"></i> Requirements Checklist
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Requirement</th>
                                <th>Required</th>
                                <th class="text-center">Submitted</th>
                                <th class="text-center">Verified</th>
                                <th>Remarks</th>
                                <th>Verified By</th>
                                <th>Verified Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_category = '';
                            foreach ($requirements as $req): 
                                if ($current_category != ($req->CATEGORY ?? '')):
                                    $current_category = $req->CATEGORY ?? '';
                            ?>
                            <tr class="active">
                                <td colspan="8"><strong><?= $current_category ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td></td>
                                <td>
                                    <?= htmlspecialchars($req->REQUIREMENT_NAME ?? '') ?>
                                    <?php if (!empty($req->DESCRIPTION)): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($req->DESCRIPTION) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($req->REQUIRED ?? '') == 'Yes'): ?>
                                        <span class="label label-danger">Required</span>
                                    <?php else: ?>
                                        <span class="label label-default">Optional</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($req->IS_SUBMITTED) && $req->IS_SUBMITTED): ?>
                                        <span class="label label-success">Yes</span>
                                    <?php else: ?>
                                        <span class="label label-danger">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($req->IS_VERIFIED) && $req->IS_VERIFIED): ?>
                                        <span class="label label-success">Yes</span>
                                    <?php else: ?>
                                        <span class="label label-warning">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($req->REMARKS ?? '') ?></td>
                                <td>
                                    <?php
                                    // Try to get verified by name from various sources
                                    $verified_by_name = '';
                                    
                                    if (isset($req->VERIFIED_BY_NAME) && !empty($req->VERIFIED_BY_NAME)) {
                                        $verified_by_name = $req->VERIFIED_BY_NAME;
                                    } elseif (isset($req->VERIFIED_BY) && !empty($req->VERIFIED_BY)) {
                                        // Query the user name
                                        $user_sql = "SELECT FULLNAME FROM tblusers WHERE USERID = " . intval($req->VERIFIED_BY);
                                        $mydb->setQuery($user_sql);
                                        $mydb->executeQuery();
                                        $user = $mydb->loadSingleResult();
                                        if ($user && isset($user->FULLNAME)) {
                                            $verified_by_name = $user->FULLNAME;
                                        }
                                    }
                                    
                                    echo htmlspecialchars($verified_by_name);
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($req->VERIFIED_DATE) && !empty($req->VERIFIED_DATE)) {
                                        echo date('M d, Y', strtotime($req->VERIFIED_DATE));
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($requirements)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-warning" style="margin: 20px;">
                                        <i class="fa fa-warning"></i> No requirements found. Please add requirements first.
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>