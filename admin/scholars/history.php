<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;
?>

<!-- Filter Section -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-filter"></i> Filter History
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="history">
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Status:</label>
                        <select name="status" class="form-control input-sm">
                            <option value="">All Status</option>
                            <option value="Applied" <?= isset($_GET['status']) && $_GET['status'] == 'Applied' ? 'selected' : '' ?>>Applied</option>
                            <option value="Exam Taken" <?= isset($_GET['status']) && $_GET['status'] == 'Exam Taken' ? 'selected' : '' ?>>Exam Taken</option>
                            <option value="Interviewed" <?= isset($_GET['status']) && $_GET['status'] == 'Interviewed' ? 'selected' : '' ?>>Interviewed</option>
                            <option value="Awarded" <?= isset($_GET['status']) && $_GET['status'] == 'Awarded' ? 'selected' : '' ?>>Awarded</option>
                            <option value="Renewed" <?= isset($_GET['status']) && $_GET['status'] == 'Renewed' ? 'selected' : '' ?>>Renewed</option>
                            <option value="Graduated" <?= isset($_GET['status']) && $_GET['status'] == 'Graduated' ? 'selected' : '' ?>>Graduated</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>School Year:</label>
                        <select name="school_year" class="form-control input-sm">
                            <option value="">All Years</option>
                            <?php
                            // Fetch distinct school years from the database
                            $mydb->setQuery("SELECT h.SCHOOL_YEAR FROM tbl_scholarship_history h INNER JOIN tbl_applicants a ON h.APPLICANTID = a.APPLICANTID LEFT JOIN tblusers u ON h.UPDATED_BY = u.USERID;");
                            $years = $mydb->loadResultList();
                            foreach ($years as $year) {
                                $selected = (isset($_GET['school_year']) && $_GET['school_year'] == $year->SCHOOL_YEAR) ? 'selected' : '';
                                echo "<option value=\"{$year->SCHOOL_YEAR}\" $selected>{$year->SCHOOL_YEAR}</option>";
                            }
                            ?>
                        </select>
                        <!-- <input type="text" name="school_year" class="form-control input-sm" 
                               value="<?= isset($_GET['school_year']) ? $_GET['school_year'] : '' ?>" 
                               placeholder="e.g. 2025-2026"> -->
                    </div>

                    <div class="form-group" style="margin-right: 10px;">
                        <label>School:</label>
                        <select name="school" class="form-control input-sm">
                            <option value="">All Schools</option>
                            <?php
                            // Fetch distinct schools from the database
                            $mydb->setQuery("SELECT DISTINCT a.SCHOOL FROM tbl_applicants a INNER JOIN tbl_scholarship_history h ON h.APPLICANTID = a.APPLICANTID LEFT JOIN tblusers u ON h.UPDATED_BY = u.USERID;");
                            $schools = $mydb->loadResultList();
                            foreach ($schools as $school) {
                                $selected = (isset($_GET['school']) && $_GET['school'] == $school->SCHOOL) ? 'selected' : '';
                                echo "<option value=\"{$school->SCHOOL}\" $selected>{$school->SCHOOL}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Apply Filter
                    </button>
                    <a href="index.php?view=history" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="dash-table" class="table table-striped table-bordered table-hover" style="font-size:13px">
        <thead>
            <tr>
                <th>Date</th>
                <th>Applicant Name</th>
                <th>Municipality</th>
                <th>School</th>
                <th>Course</th>
                <th>School Year</th>
                <th>Semester</th>
                <th>Status</th>
                <th>GPA</th>
                <th>Remarks</th>
                <th>Updated By</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Build query with filters
            $where = array();
            
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $where[] = "h.STATUS = '" . $_GET['status'] . "'";
            }
            
            if (isset($_GET['school_year']) && !empty($_GET['school_year'])) {
                $where[] = "h.SCHOOL_YEAR = '" . $_GET['school_year'] . "'";
            }
            
            $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $sql = "
                SELECT 
                    h.*,
                    a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME, a.SUFFIX,
                    a.MUNICIPALITY, a.SCHOOL, a.COURSE,
                    u.FULLNAME as UPDATED_BY_NAME
                FROM tbl_scholarship_history h
                INNER JOIN tbl_applicants a ON h.APPLICANTID = a.APPLICANTID
                LEFT JOIN tblusers u ON h.UPDATED_BY = u.USERID
                $where_clause
                ORDER BY h.UPDATED_AT DESC
            ";
            
            $mydb->setQuery($sql);
            $mydb->executeQuery();
            $history = $mydb->loadResultList();
            
            foreach ($history as $h):
                $status_color = match($h->STATUS) {
                    'Applied' => 'label-default',
                    'Exam Taken' => 'label-info',
                    'Interviewed' => 'label-primary',
                    'Awarded' => 'label-success',
                    'Renewed' => 'label-warning',
                    'Graduated' => 'label-success',
                    default => 'label-default'
                };
            ?>
            <tr>
                <td><?= date('M d, Y', strtotime($h->UPDATED_AT)) ?></td>
                <td><?= htmlspecialchars($h->LASTNAME . ', ' . $h->FIRSTNAME . ' ' . ($h->MIDDLENAME ?? '') . ' ' . ($h->SUFFIX ?? '')) ?></td>
                <td><?= htmlspecialchars($h->MUNICIPALITY ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($h->SCHOOL ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($h->COURSE ?? 'N/A') ?></td>
                <td><?= $h->SCHOOL_YEAR ?></td>
                <td><?= $h->SEMESTER ?></td>
                <td><span class="label <?= $status_color ?>"><?= $h->STATUS ?></span></td>
                <td><strong><?= $h->GPA ?>%</strong></td>
                <td><?= htmlspecialchars($h->REMARKS ?? '') ?></td>
                <td><?= htmlspecialchars($h->UPDATED_BY_NAME ?? 'N/A') ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($history)): ?>
            <tr>
                <td colspan="11" class="text-center">
                    <div class="alert alert-info" style="margin: 20px;">
                        <i class="fa fa-info-circle"></i> No history records found.
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="<?php echo web_root; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
    $(document).ready(function () {
        $('#dash-table').DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]],
            "columnDefs": [
                { "targets": "_all", "defaultContent": "" } // <--- Add this line
            ]
        });
    });
</script>