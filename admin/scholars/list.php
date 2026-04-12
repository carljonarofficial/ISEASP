<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;
?>

<!-- Summary Cards -->
<!-- <div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_scholarship_awards WHERE STATUS = 'Active'");
                $mydb->executeQuery();
                $active = $mydb->loadSingleResult();
                ?>
                <h3><?= $active->total ?? 0 ?></h3>
                <p>Active Scholars</p>
            </div>
            <div class="icon">
                <i class="fa fa-graduation-cap"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <?php
                $current_year = date('Y') . '-' . (date('Y') + 1);
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_scholarship_awards WHERE STATUS = 'Active' AND SCHOOL_YEAR = '$current_year'");
                $mydb->executeQuery();
                $current = $mydb->loadSingleResult();
                ?>
                <h3><?= $current->total ?? 0 ?></h3>
                <p>Current School Year</p>
            </div>
            <div class="icon">
                <i class="fa fa-calendar"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_scholarship_awards WHERE STATUS = 'Active' AND SEMESTER = '2nd Semester'");
                $mydb->executeQuery();
                $second_sem = $mydb->loadSingleResult();
                ?>
                <h3><?= $second_sem->total ?? 0 ?></h3>
                <p>2nd Semester</p>
            </div>
            <div class="icon">
                <i class="fa fa-book"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT SUM(AMOUNT) as total FROM tbl_scholarship_awards WHERE STATUS = 'Active'");
                $mydb->executeQuery();
                $total_amount = $mydb->loadSingleResult();
                ?>
                <h3>₱ <?= number_format($total_amount->total ?? 0, 2) ?></h3>
                <p>Total Award Amount</p>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
        </div>
    </div>
</div> -->

<!-- Action Buttons -->
<div class="row">
    <div class="col-lg-12" style="margin-bottom: 10px;">
        <a href="index.php?view=print_masterlist" class="btn btn-success" target="_blank">
            <i class="fa fa-print"></i> Print Master List
        </a>
        <a href="#" onclick="window.print()" class="btn btn-default">
            <i class="fa fa-print"></i> Print This Page
        </a>
        <a href="index.php?view=payroll" class="btn btn-info">
            <i class="fa fa-money"></i> Payroll Management
        </a>
        <a href="index.php?view=graduates" class="btn btn-primary">
            <i class="fa fa-graduation-cap"></i> View Graduates
        </a>
        <a href="index.php?view=history" class="btn btn-warning">
            <i class="fa fa-history"></i> Scholarship History
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-filter"></i> Filter Scholars
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="list">
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>School Year:</label>
                        <select name="school_year" class="form-control input-sm">
                            <option value="">All Years</option>
                            <?php
                            $mydb->setQuery("SELECT DISTINCT SCHOOL_YEAR FROM tbl_scholarship_awards ORDER BY SCHOOL_YEAR DESC");
                            $mydb->executeQuery();
                            $years = $mydb->loadResultList();
                            foreach ($years as $year):
                            ?>
                            <option value="<?= $year->SCHOOL_YEAR ?>" <?= isset($_GET['school_year']) && $_GET['school_year'] == $year->SCHOOL_YEAR ? 'selected' : '' ?>>
                                <?= $year->SCHOOL_YEAR ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Municipality:</label>
                        <select name="municipality" class="form-control input-sm">
                            <option value="">All Municipalities</option>
                            <?php
                            $mydb->setQuery("SELECT DISTINCT MUNICIPALITY FROM tbl_applicants WHERE MUNICIPALITY IS NOT NULL ORDER BY MUNICIPALITY");
                            $mydb->executeQuery();
                            $municipalities = $mydb->loadResultList();
                            foreach ($municipalities as $town):
                            ?>
                            <option value="<?= $town->MUNICIPALITY ?>" <?= isset($_GET['municipality']) && $_GET['municipality'] == $town->MUNICIPALITY ? 'selected' : '' ?>>
                                <?= $town->MUNICIPALITY ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 10px;">
                        <label>School:</label>
                        <select name="school" class="form-control input-sm">
                            <option value="">All Schools</option>
                            <?php
                            $mydb->setQuery("SELECT DISTINCT a.SCHOOL FROM tbl_scholarship_awards sa INNER JOIN tbl_applicants a ON sa.APPLICANTID = a.APPLICANTID LEFT JOIN tblusers u ON sa.AWARDED_BY = u.USERID");
                            $mydb->executeQuery();
                            $schools = $mydb->loadResultList();
                            foreach ($schools as $school):
                            ?>
                            <option value="<?= $school->SCHOOL ?>" <?= isset($_GET['school']) && $_GET['school'] == $school->SCHOOL ? 'selected' : '' ?>>
                                <?= $school->SCHOOL ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Year Level:</label>
                        <select name="year_level" class="form-control input-sm">
                            <option value="">All Levels</option>
                            <option value="1st Year" <?= isset($_GET['year_level']) && $_GET['year_level'] == '1st Year' ? 'selected' : '' ?>>1st Year</option>
                            <option value="2nd Year" <?= isset($_GET['year_level']) && $_GET['year_level'] == '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3rd Year" <?= isset($_GET['year_level']) && $_GET['year_level'] == '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4th Year" <?= isset($_GET['year_level']) && $_GET['year_level'] == '4th Year' ? 'selected' : '' ?>>4th Year</option>
                            <option value="5th Year" <?= isset($_GET['year_level']) && $_GET['year_level'] == '5th Year' ? 'selected' : '' ?>>5th Year</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Status:</label>
                        <select name="status" class="form-control input-sm">
                            <option value="Active" <?= !isset($_GET['status']) || $_GET['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Graduated" <?= isset($_GET['status']) && $_GET['status'] == 'Graduated' ? 'selected' : '' ?>>Graduated</option>
                            <option value="Terminated" <?= isset($_GET['status']) && $_GET['status'] == 'Terminated' ? 'selected' : '' ?>>Terminated</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Apply Filter
                    </button>
                    <a href="index.php?view=list" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scholars Table -->
<div class="panel panel-success">
    <div class="panel-heading">
        <i class="fa fa-users"></i> Scholars List
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table id="scholars-table" class="table table-striped table-bordered table-hover" style="font-size:13px">
                <thead>
                    <tr>
                        <th>Scholar ID</th>
                        <th>Name</th>
                        <th>Municipality</th>
                        <th>School</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Allowance Amount</th>
                        <th>Status</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build query with filters
                    $where = array();
                    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'Active';
                    $where[] = "sa.STATUS = '$status_filter'";
                    
                    if (isset($_GET['school_year']) && !empty($_GET['school_year'])) {
                        $where[] = "sa.SCHOOL_YEAR = '" . $_GET['school_year'] . "'";
                    }
                    
                    if (isset($_GET['municipality']) && !empty($_GET['municipality'])) {
                        $where[] = "a.MUNICIPALITY = '" . $_GET['municipality'] . "'";
                    }
                    
                    if (isset($_GET['year_level']) && !empty($_GET['year_level'])) {
                        $where[] = "a.YEARLEVEL = '" . $_GET['year_level'] . "'";
                    }
                    
                    $where_clause = "WHERE " . implode(" AND ", $where);
                    
                    $sql = "
                        SELECT 
                            sa.*,
                            a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME, a.SUFFIX,
                            a.MUNICIPALITY, a.SCHOOL, a.COURSE, a.YEARLEVEL,
                            u.FULLNAME as AWARDED_BY_NAME
                        FROM tbl_scholarship_awards sa
                        INNER JOIN tbl_applicants a ON sa.APPLICANTID = a.APPLICANTID
                        LEFT JOIN tblusers u ON sa.AWARDED_BY = u.USERID
                        $where_clause
                        ORDER BY a.LASTNAME ASC
                    ";
                    
                    $mydb->setQuery($sql);
                    $mydb->executeQuery();
                    $scholars = $mydb->loadResultList();
                    
                    foreach ($scholars as $s):
                        $status_color = match($s->STATUS) {
                            'Active' => 'label-success',
                            'Graduated' => 'label-primary',
                            'Terminated' => 'label-danger',
                            'Inactive' => 'label-warning',
                            default => 'label-default'
                        };
                    ?>
                    <tr>
                        <td class="text-center"><strong>SCH-<?= str_pad($s->AWARD_ID, 5, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= htmlspecialchars($s->LASTNAME . ', ' . $s->FIRSTNAME . ' ' . ($s->MIDDLENAME ?? '') . ' ' . ($s->SUFFIX ?? '')) ?></td>
                        <td><?= htmlspecialchars($s->MUNICIPALITY ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($s->SCHOOL ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($s->COURSE ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($s->YEARLEVEL ?? 'N/A') ?></td>
                        <td><?= $s->SCHOOL_YEAR ?></td>
                        <td><?= $s->SEMESTER ?></td>
                        <td><strong>₱ <?= number_format($s->AMOUNT, 2) ?></strong></td>
                        <td><span class="label <?= $status_color ?>"><?= $s->STATUS ?></span></td>
                        <td class="text-center">
                            <a href="index.php?view=view&id=<?= $s->AWARD_ID ?>" class="btn btn-info btn-xs" title="View Details">
                                <i class="fa fa-eye"></i>
                            </a>
                            <?php if ($s->STATUS == 'Active'): ?>
                            <a href="index.php?view=renew&id=<?= $s->AWARD_ID ?>" class="btn btn-warning btn-xs" title="Process Renewal">
                                <i class="fa fa-refresh"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($_SESSION['ADMIN_ROLE'] == 'Super Admin'): ?>
                            <a href="controller.php?action=delete&id=<?= $s->AWARD_ID ?>" 
                               class="btn btn-danger btn-xs" 
                               onclick="return confirm('Delete this scholarship award? This action cannot be undone.')"
                               title="Delete">
                                <i class="fa fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($scholars)): ?>
                    <tr>
                        <td colspan="11" class="text-center">
                            <div class="alert alert-info" style="margin: 20px;">
                                <i class="fa fa-info-circle"></i> No scholars found.
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?php echo web_root; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
$(document).ready(function() {
    $('#scholars-table').DataTable({
        "pageLength": 25,
        "order": [[1, "asc"]],
        "columnDefs": [
            { "targets": "_all", "defaultContent": "" } // <--- Add this line
        ]
    });
});
</script>

<style>
.small-box {
    border-radius: 5px;
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.small-box .inner {
    padding: 10px;
}

.small-box h3 {
    font-size: 38px;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}

.small-box p {
    font-size: 15px;
    margin: 0;
}

.small-box .icon {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 0;
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}

.small-box.bg-green {
    background-color: #00a65a;
    color: #fff;
}

.small-box.bg-yellow {
    background-color: #f39c12;
    color: #fff;
}

.small-box.bg-aqua {
    background-color: #00c0ef;
    color: #fff;
}

.small-box.bg-red {
    background-color: #dd4b39;
    color: #fff;
}

.btn-xs {
    margin: 2px;
}
</style>