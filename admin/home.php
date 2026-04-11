<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;

// Get statistics from database
// Total Scholars (Active awards)
$mydb->setQuery("SELECT COUNT(*) as total FROM tbl_scholarship_awards WHERE STATUS = 'Active'");
$mydb->executeQuery();
$total_scholars = $mydb->loadSingleResult();
$scholar_count = $total_scholars ? $total_scholars->total : 0;

// Pending Applications
$mydb->setQuery("SELECT COUNT(*) as total FROM tbl_applicants WHERE STATUS = 'Pending'");
$mydb->executeQuery();
$pending_apps = $mydb->loadSingleResult();
$pending_count = $pending_apps ? $pending_apps->total : 0;

// For Interview
$mydb->setQuery("SELECT COUNT(*) as total FROM tbl_applicants WHERE STATUS = 'For Interview'");
$mydb->executeQuery();
$for_interview = $mydb->loadSingleResult();
$interview_count = $for_interview ? $for_interview->total : 0;

// Qualified Applicants
$mydb->setQuery("SELECT COUNT(*) as total FROM tbl_applicants WHERE STATUS = 'Qualified'");
$mydb->executeQuery();
$qualified = $mydb->loadSingleResult();
$qualified_count = $qualified ? $qualified->total : 0;

// Total Municipalities
$mydb->setQuery("SELECT COUNT(*) as total FROM tbl_municipalities WHERE IS_ACTIVE = 'Yes'");
$mydb->executeQuery();
$total_municipalities = $mydb->loadSingleResult();
$municipality_count = $total_municipalities ? $total_municipalities->total : 0;

// Total Graduates
$mydb->setQuery("SELECT COUNT(*) as total FROM tbl_scholarship_history WHERE STATUS = 'Graduated'");
$mydb->executeQuery();
$total_graduates = $mydb->loadSingleResult();
$graduate_count = $total_graduates ? $total_graduates->total : 0;

// Recent Activities - FIXED: Using new table structure with USERID instead of ACTION_BY
$mydb->setQuery("
    SELECT l.*, u.FULLNAME as USER_NAME, CONCAT(a.LASTNAME, ', ', a.FIRSTNAME) as APPLICANT_NAME 
    FROM tbl_application_log l 
    LEFT JOIN tblusers u ON l.USERID = u.USERID 
    LEFT JOIN tbl_applicants a ON l.APPLICANTID = a.APPLICANTID 
    ORDER BY l.LOG_DATE DESC 
    LIMIT 10
");
$mydb->executeQuery();
$recent_activities = $mydb->loadResultList();

// Upcoming Exams
$mydb->setQuery("
    SELECT a.APPLICANTID, a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME, 
           a.EXAM_DATE, a.EXAM_TIME, a.EXAM_VENUE 
    FROM tbl_applicants a 
    WHERE a.EXAM_SLIP_GENERATED IS NOT NULL 
    AND a.EXAM_STATUS = 'Pending' 
    AND a.EXAM_DATE >= CURDATE() 
    ORDER BY a.EXAM_DATE ASC 
    LIMIT 5
");
$mydb->executeQuery();
$upcoming_exams = $mydb->loadResultList();

// Top Schools by Scholar Count
$mydb->setQuery("
    SELECT a.SCHOOL, COUNT(*) as scholar_count 
    FROM tbl_applicants a 
    INNER JOIN tbl_scholarship_awards sa ON a.APPLICANTID = sa.APPLICANTID 
    WHERE sa.STATUS = 'Active' 
    GROUP BY a.SCHOOL 
    ORDER BY scholar_count DESC 
    LIMIT 5
");
$mydb->executeQuery();
$top_schools = $mydb->loadResultList();

// Statistics by District
$mydb->setQuery("
    SELECT 
        m.DISTRICT,
        COUNT(DISTINCT a.APPLICANTID) as applicant_count,
        COUNT(DISTINCT sa.APPLICANTID) as scholar_count
    FROM tbl_municipalities m
    LEFT JOIN tbl_applicants a ON a.MUNICIPALITY = m.MUNICIPALITY_NAME
    LEFT JOIN tbl_scholarship_awards sa ON sa.APPLICANTID = a.APPLICANTID AND sa.STATUS = 'Active'
    GROUP BY m.DISTRICT
");
$mydb->executeQuery();
$district_stats = $mydb->loadResultList();

// Get current school year
$current_sy = '2025-2026';
?>

<!-- Dashboard Header -->
<section class="content-header">
    <h1>
        Dashboard
        <small>Scholarship Overview - School Year <?php echo $current_sy; ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
</section>

<section class="content">
    <!-- Statistics Boxes Row 1 -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?php echo $scholar_count; ?></h3>
                    <p>Active Scholars</p>
                </div>
                <div class="icon">
                    <i class="fa fa-graduation-cap"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/scholars/" class="small-box-footer">
                    View Scholars <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending Applications</p>
                </div>
                <div class="icon">
                    <i class="fa fa-file-text"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/applications/index.php?view=pending" class="small-box-footer">
                    Review Applications <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3><?php echo $interview_count; ?></h3>
                    <p>For Interview</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/applications/index.php?view=for_interview" class="small-box-footer">
                    View Interviews <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?php echo $qualified_count; ?></h3>
                    <p>Qualified Applicants</p>
                </div>
                <div class="icon">
                    <i class="fa fa-star"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/applications/index.php?view=qualified" class="small-box-footer">
                    View Qualified <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Boxes Row 2 -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3><?php echo $graduate_count; ?></h3>
                    <p>Graduates</p>
                </div>
                <div class="icon">
                    <i class="fa fa-trophy"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/scholars/index.php?view=graduates" class="small-box-footer">
                    View Graduates <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3><?php echo $municipality_count; ?></h3>
                    <p>Municipalities</p>
                </div>
                <div class="icon">
                    <i class="fa fa-map-marker"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/municipalities/" class="small-box-footer">
                    View Municipalities <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <?php
                    $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_exam_results");
                    $mydb->executeQuery();
                    $exams = $mydb->loadSingleResult();
                    ?>
                    <h3><?php echo $exams ? $exams->total : 0; ?></h3>
                    <p>Exams Taken</p>
                </div>
                <div class="icon">
                    <i class="fa fa-pencil-square-o"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/exam/index.php?view=results" class="small-box-footer">
                    View Results <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-orange">
                <div class="inner">
                    <?php
                    $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_interview");
                    $mydb->executeQuery();
                    $interviews = $mydb->loadSingleResult();
                    ?>
                    <h3><?php echo $interviews ? $interviews->total : 0; ?></h3>
                    <p>Interviews</p>
                </div>
                <div class="icon">
                    <i class="fa fa-comments"></i>
                </div>
                <a href="<?php echo web_root; ?>admin/interview/" class="small-box-footer">
                    View Interviews <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Row -->
    <div class="row">
        <!-- Left Column - Recent Activities & Upcoming Exams -->
        <div class="col-md-8">
            <!-- Recent Activities -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Recent Activities</h3>
                    <div class="box-tools pull-right">
                        <a href="<?php echo web_root; ?>admin/system/index.php?view=logs" class="btn btn-box-tool">
                            <i class="fa fa-external-link"></i> View All
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <ul class="timeline">
                        <?php 
                        $current_date = '';
                        foreach ($recent_activities as $activity):
                            $log_date = date('Y-m-d', strtotime($activity->LOG_DATE));
                            if ($current_date != $log_date):
                                $current_date = $log_date;
                        ?>
                        <li class="time-label">
                            <span class="bg-blue"><?php echo date('F d, Y', strtotime($activity->LOG_DATE)); ?></span>
                        </li>
                        <?php endif; ?>
                        <li>
                            <i class="fa 
                                <?php 
                                if (strpos($activity->ACTION, 'Created') !== false) echo 'fa-plus-circle bg-green';
                                elseif (strpos($activity->ACTION, 'Updated') !== false) echo 'fa-edit bg-yellow';
                                elseif (strpos($activity->ACTION, 'Deleted') !== false) echo 'fa-trash bg-red';
                                elseif (strpos($activity->ACTION, 'Exam') !== false) echo 'fa-pencil bg-aqua';
                                elseif (strpos($activity->ACTION, 'Interview') !== false) echo 'fa-users bg-purple';
                                else echo 'fa-info-circle bg-blue';
                                ?>
                            "></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('h:i A', strtotime($activity->LOG_DATE)); ?></span>
                                <h3 class="timeline-header">
                                    <strong><?php echo htmlspecialchars($activity->USER_NAME ?? 'System'); ?></strong>
                                </h3>
                                <div class="timeline-body">
                                    <?php echo htmlspecialchars($activity->ACTION); ?>
                                    <?php if (!empty($activity->APPLICANT_NAME)): ?>
                                        on <strong><?php echo htmlspecialchars($activity->APPLICANT_NAME); ?></strong>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recent_activities)): ?>
                        <li>
                            <i class="fa fa-info-circle bg-gray"></i>
                            <div class="timeline-item">
                                <div class="timeline-body">
                                    No recent activities found.
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <li>
                            <i class="fa fa-clock-o bg-gray"></i>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Upcoming Exams -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calendar"></i> Upcoming Examinations</h3>
                    <div class="box-tools pull-right">
                        <a href="<?php echo web_root; ?>admin/exam/index.php?view=schedule" class="btn btn-box-tool">
                            <i class="fa fa-external-link"></i> View All
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming_exams as $exam): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo web_root; ?>admin/applications/index.php?view=view&id=<?php echo $exam->APPLICANTID; ?>">
                                        <?php echo htmlspecialchars($exam->LASTNAME . ', ' . $exam->FIRSTNAME); ?>
                                    </a>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($exam->EXAM_DATE)); ?></td>
                                <td><?php echo date('h:i A', strtotime($exam->EXAM_TIME)); ?></td>
                                <td><?php echo htmlspecialchars($exam->EXAM_VENUE); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($upcoming_exams)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No upcoming examinations scheduled.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Stats and Info -->
        <div class="col-md-4">
            <!-- District Statistics -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-pie-chart"></i> District Distribution</h3>
                </div>
                <div class="box-body">
                    <canvas id="districtChart" style="height: 200px;"></canvas>
                    <table class="table table-condensed table-striped" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>District</th>
                                <th>Applicants</th>
                                <th>Scholars</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $district_labels = [];
                            $district_data = [];
                            foreach ($district_stats as $stat):
                                $district_labels[] = $stat->DISTRICT;
                                $district_data[] = $stat->scholar_count;
                            ?>
                            <tr>
                                <td><?php echo $stat->DISTRICT; ?></td>
                                <td><?php echo $stat->applicant_count; ?></td>
                                <td><?php echo $stat->scholar_count; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Schools -->
            <!-- <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-trophy"></i> Top Performing Schools</h3>
                </div>
                <div class="box-body">
                    <ol>
                        <?php foreach ($top_schools as $school): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($school->SCHOOL); ?></strong> 
                            <span class="label label-success pull-right"><?php echo $school->scholar_count; ?> scholars</span>
                        </li>
                        <?php endforeach; ?>
                        
                        <?php if (empty($top_schools)): ?>
                        <li>No data available</li>
                        <?php endif; ?>
                    </ol>
                </div>
            </div> -->

            <!-- Quick Actions -->
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <div class="btn-group-vertical" style="width: 100%;">
                        <a href="<?php echo web_root; ?>admin/applications/index.php?view=add" class="btn btn-default btn-block">
                            <i class="fa fa-plus-circle text-green"></i> Add New Applicant
                        </a>
                        <a href="<?php echo web_root; ?>admin/exam/index.php?view=add" class="btn btn-default btn-block">
                            <i class="fa fa-pencil text-aqua"></i> Record Exam Result
                        </a>
                        <a href="<?php echo web_root; ?>admin/interview/index.php?view=add" class="btn btn-default btn-block">
                            <i class="fa fa-calendar text-yellow"></i> Schedule Interview
                        </a>
                        <a href="<?php echo web_root; ?>admin/reports/index.php?view=statistics" class="btn btn-default btn-block">
                            <i class="fa fa-bar-chart text-purple"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row - Status Summary -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-tasks"></i> Application Status Summary</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <?php
                        $statuses = ['Pending', 'For Interview', 'Qualified', 'Scholar', 'Graduated', 'Rejected'];
                        $colors = ['warning', 'info', 'primary', 'success', 'success', 'danger'];
                        
                        foreach ($statuses as $index => $status):
                            $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_applicants WHERE STATUS = '$status'");
                            $mydb->executeQuery();
                            $count = $mydb->loadSingleResult();
                        ?>
                        <div class="col-md-2 col-xs-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-<?php echo $colors[$index]; ?>">
                                    <i class="fa 
                                        <?php 
                                        if ($status == 'Pending') echo 'fa-clock-o';
                                        elseif ($status == 'For Interview') echo 'fa-users';
                                        elseif ($status == 'Qualified') echo 'fa-star';
                                        elseif ($status == 'Scholar') echo 'fa-graduation-cap';
                                        elseif ($status == 'Graduated') echo 'fa-trophy';
                                        elseif ($status == 'Rejected') echo 'fa-times-circle';
                                        ?>
                                    "></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text"><?php echo $status; ?></span>
                                    <span class="info-box-number"><?php echo $count ? $count->total : 0; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // District Distribution Chart
    var ctx = document.getElementById('districtChart').getContext('2d');
    var districtChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($district_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($district_data); ?>,
                backgroundColor: ['#00a65a', '#f39c12', '#00c0ef', '#dd4b39', '#605ca8'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });
});
</script>

<style>
/* Custom styles for dashboard */
.info-box {
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}
.info-box-icon {
    border-top-left-radius: 2px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    color: #fff;
}
.info-box-content {
    padding: 10px;
    margin-left: 90px;
}
.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}
.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}
.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}
.timeline > li {
    position: relative;
    margin-right: 10px;
    margin-bottom: 15px;
}
.timeline > li > .timeline-item {
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #444;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}
.timeline > li > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}
.timeline > li > .timeline-item > .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}
.timeline > li > .timeline-item > .timeline-body {
    padding: 10px;
}
.timeline > li > .fa {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #fff;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}
</style>