<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;
?>

<!-- Summary Stats -->
<!-- <div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_interview");
                $mydb->executeQuery();
                $total = $mydb->loadSingleResult();
                ?>
                <h3><?= $total->total ?? 0 ?></h3>
                <p>Total Interviews</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_interview WHERE RECOMMENDATION = 'For Review'");
                $mydb->executeQuery();
                $pending = $mydb->loadSingleResult();
                ?>
                <h3><?= $pending->total ?? 0 ?></h3>
                <p>Pending Review</p>
            </div>
            <div class="icon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_interview WHERE RECOMMENDATION = 'Pass'");
                $mydb->executeQuery();
                $passed = $mydb->loadSingleResult();
                ?>
                <h3><?= $passed->total ?? 0 ?></h3>
                <p>Passed</p>
            </div>
            <div class="icon">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>
    </div>
</div> -->

<!-- Filter Section -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-filter"></i> Filter Schedule
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="schedule">
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Date From:</label>
                        <input type="date" name="date_from" class="form-control input-sm" 
                               value="<?= isset($_GET['date_from']) ? $_GET['date_from'] : '' ?>">
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Date To:</label>
                        <input type="date" name="date_to" class="form-control input-sm" 
                               value="<?= isset($_GET['date_to']) ? $_GET['date_to'] : '' ?>">
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Status:</label>
                        <select name="status" class="form-control input-sm">
                            <option value="">All</option>
                            <option value="For Review" <?= isset($_GET['status']) && $_GET['status'] == 'For Review' ? 'selected' : '' ?>>Pending</option>
                            <option value="Pass" <?= isset($_GET['status']) && $_GET['status'] == 'Pass' ? 'selected' : '' ?>>Pass</option>
                            <option value="Fail" <?= isset($_GET['status']) && $_GET['status'] == 'Fail' ? 'selected' : '' ?>>Fail</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Municipality:</label>
                        <select name="municipality" class="form-control input-sm">
                            <option value="">All</option>
                            <?php
                            $mydb->setQuery("SELECT DISTINCT a.MUNICIPALITY FROM tbl_interview i INNER JOIN tbl_applicants a ON i.APPLICANTID = a.APPLICANTID LEFT JOIN tblusers u ON i.INTERVIEWER_ID = u.USERID");
                            $mydb->executeQuery();
                            $municipalities = $mydb->loadResultList();
                            foreach ($municipalities as $municipality) {
                                echo "<option value='{$municipality->MUNICIPALITY}' " . (isset($_GET['municipality']) && $_GET['municipality'] == $municipality->MUNICIPALITY ? "selected" : "") . ">{$municipality->MUNICIPALITY}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 10px;">
                        <label>School:</label>
                        <select name="school" class="form-control input-sm">
                            <option value="">All</option>
                            <?php
                            $mydb->setQuery("SELECT DISTINCT a.SCHOOL FROM tbl_interview i INNER JOIN tbl_applicants a ON i.APPLICANTID = a.APPLICANTID LEFT JOIN tblusers u ON i.INTERVIEWER_ID = u.USERID");
                            $mydb->executeQuery();
                            $schools = $mydb->loadResultList();
                            foreach ($schools as $school) {
                                echo "<option value='{$school->SCHOOL}' " . (isset($_GET['school']) && $_GET['school'] == $school->SCHOOL ? "selected" : "") . ">{$school->SCHOOL}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Apply Filter
                    </button>
                    <a href="index.php?view=schedule" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Pending Interviews Alert -->
<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> 
            <strong>Note:</strong> Interviews are automatically created when applicants pass the examination. 
            Use the edit button to update interview results.
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="dash-table" class="table table-striped table-bordered table-hover" style="font-size:13px">
        <thead>
            <tr>
                <th>Interview Date</th>
                <th>Applicant Name</th>
                <th>Municipality</th>
                <th>School</th>
                <th>Course</th>
                <th>Interviewer</th>
                <th>Mode</th>
                <th>Score</th>
                <th>Recommendation</th>
                <th width="15%">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Build query with filters
            $where = array();

            // Always exclude passed interviews from the main schedule view
            $where[] = "i.RECOMMENDATION != 'Pass'";

            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $date_from = $_GET['date_from'];
                $where[] = "DATE(i.INTERVIEW_DATE) >= '$date_from'";
            }

            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $date_to = $_GET['date_to'];
                $where[] = "DATE(i.INTERVIEW_DATE) <= '$date_to'";
            }

            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $status = $_GET['status'];
                $where[] = "i.RECOMMENDATION = '$status'";
            }

            if (isset($_GET['municipality']) && !empty($_GET['municipality'])) {
                $municipality = $_GET['municipality'];
                $where[] = "a.MUNICIPALITY = '$municipality'";
            }

            if (isset($_GET['school']) && !empty($_GET['school'])) {
                $school = $_GET['school'];
                $where[] = "a.SCHOOL = '$school'";
            }

            $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            $sql = "
                SELECT 
                    i.*,
                    a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME,
                    a.MUNICIPALITY, a.SCHOOL, a.COURSE,
                    u.FULLNAME as INTERVIEWER_NAME
                FROM tbl_interview i
                INNER JOIN tbl_applicants a ON i.APPLICANTID = a.APPLICANTID
                LEFT JOIN tblusers u ON i.INTERVIEWER_ID = u.USERID
                $where_clause
                ORDER BY i.INTERVIEW_DATE DESC
            ";
            
            $mydb->setQuery($sql);
            $mydb->executeQuery();
            $interviews = $mydb->loadResultList();
            
            foreach ($interviews as $i):
                $rec_color = $i->RECOMMENDATION == 'Pass' ? 'label-success' : 
                            ($i->RECOMMENDATION == 'Fail' ? 'label-danger' : 'label-warning');
            ?>
            <tr>
                <td><?= date('M d, Y h:i A', strtotime($i->INTERVIEW_DATE)) ?></td>
                <td><?= htmlspecialchars($i->LASTNAME . ', ' . $i->FIRSTNAME . ' ' . ($i->MIDDLENAME ?? '')) ?></td>
                <td><?= htmlspecialchars($i->MUNICIPALITY ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($i->SCHOOL ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($i->COURSE ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($i->INTERVIEWER_NAME ?? 'Not Assigned') ?></td>
                <td><?= $i->INTERVIEW_MODE ?></td>
                <td><strong><?= $i->SCORE ? $i->SCORE . '%' : 'Pending' ?></strong></td>
                <td><span class="label <?= $rec_color ?>"><?= $i->RECOMMENDATION ?></span></td>
                <td class="text-center">
                    <a href="index.php?view=view&id=<?= $i->INTERVIEW_ID ?>" 
                       class="btn btn-info btn-xs" title="View Details">
                        <i class="fa fa-eye"></i> View Details
                    </a>
                    <a href="index.php?view=edit&id=<?= $i->INTERVIEW_ID ?>" 
                       class="btn btn-primary btn-xs" title="Edit / Enter Result">
                        <i class="fa fa-edit"></i> Enter Result
                    </a>
                    <?php if ($_SESSION['ADMIN_ROLE'] == 'Super Admin'): ?>
                    <a href="controller.php?action=delete&id=<?= $i->INTERVIEW_ID ?>" 
                       class="btn btn-danger btn-xs" 
                       onclick="return confirm('Delete this interview record?')"
                       title="Delete">
                        <i class="fa fa-trash"></i> Delete
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($interviews)): ?>
            <tr>
                <td colspan="10" class="text-center">
                    <div class="alert alert-info" style="margin: 20px;">
                        <i class="fa fa-info-circle"></i> No interview schedules found.
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="<?php echo web_root; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
$(document).ready(function() {
    $('#dash-table').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]]
    });
});
</script>