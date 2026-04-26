<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;

// // Debug - Check what's in the database
// $mydb->setQuery("SELECT AWARD_ID, APPLICANTID, STATUS FROM tbl_scholarship_awards WHERE STATUS = 'Graduated'");
// $mydb->executeQuery();
// $debug_graduates = $mydb->loadResultList();

// echo '<div class="alert alert-info">';
// echo '<strong>Debug Info:</strong> Found ' . count($debug_graduates) . ' graduated scholars in database.<br>';
// foreach($debug_graduates as $dg) {
//     echo 'Award ID: ' . $dg->AWARD_ID . ', Applicant ID: ' . $dg->APPLICANTID . ', Status: ' . $dg->STATUS . '<br>';
// }
// echo '</div>';
?>

<!-- Graduation Statistics -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-md-3">
        <div class="small-box bg-green">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_scholarship_awards WHERE STATUS = 'Graduated'");
                $mydb->executeQuery();
                $result = $mydb->loadSingleResult();
                $total = $result ? $result->total : 0;
                ?>
                <h3><?= $total ?></h3>
                <p>Total Graduates</p>
            </div>
            <div class="icon">
                <i class="fa fa-graduation-cap"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="small-box bg-aqua">
            <div class="inner">
                <?php
                $current_year = date('Y');
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_applicants 
                                 WHERE STATUS = 'Graduated' AND YEAR(LAST_UPDATED) = '$current_year'");
                $mydb->executeQuery();
                $result = $mydb->loadSingleResult();
                $this_year = $result ? $result->total : 0;
                ?>
                <h3><?= $this_year ?></h3>
                <p>Graduated This Year</p>
            </div>
            <div class="icon">
                <i class="fa fa-calendar"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="small-box bg-yellow">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT AVG(GPA) as avg FROM tbl_applicants WHERE STATUS = 'Graduated' AND GPA IS NOT NULL");
                $mydb->executeQuery();
                $result = $mydb->loadSingleResult();
                $avg_gpa = $result->avg !== null ? round($result->avg, 2) : 0;
                ?>
                <h3><?= $avg_gpa ?>%</h3>
                <p>Average GPA</p>
            </div>
            <div class="icon">
                <i class="fa fa-line-chart"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="small-box bg-red">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_alumni WHERE HONORS IS NOT NULL AND HONORS != ''");
                $mydb->executeQuery();
                $result = $mydb->loadSingleResult();
                $with_honors = $result ? $result->total : 0;
                ?>
                <h3><?= $with_honors ?></h3>
                <p>With Honors</p>
            </div>
            <div class="icon">
                <i class="fa fa-star"></i>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row">
    <div class="col-lg-12" style="margin-bottom: 15px;">
        <a href="../applications/index.php?stage=scholar" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back to Scholars
        </a>
        <!-- <a href="#" onclick="window.print()" class="btn btn-default">
            <i class="fa fa-print"></i> Print List
        </a> -->
    </div>
</div>

<!-- Filter Section -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-filter"></i> Filter Graduates
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="graduates">
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>School Year:</label>
                        <select name="school_year" class="form-control input-sm">
                            <option value="">All Years</option>
                            <?php
                            $mydb->setQuery("SELECT DISTINCT SCHOOL_YEAR FROM tbl_scholarship_awards WHERE STATUS = 'Graduated' ORDER BY SCHOOL_YEAR DESC");
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
                            $mydb->setQuery("SELECT 
                                    DISTINCT a.MUNICIPALITY
                                FROM tbl_scholarship_awards sa
                                INNER JOIN tbl_applicants a ON sa.APPLICANTID = a.APPLICANTID
                                LEFT JOIN tbl_alumni al ON a.APPLICANTID = al.APPLICANTID
                                WHERE sa.STATUS = 'Graduated' ");
                            $mydb->executeQuery();
                            $municipalities = $mydb->loadResultList();
                            foreach ($municipalities as $m):
                            ?>
                            <option value="<?= htmlspecialchars($m->MUNICIPALITY) ?>" <?= isset($_GET['municipality']) && $_GET['municipality'] == $m->MUNICIPALITY ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m->MUNICIPALITY) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 10px;">
                        <label>School:</label>
                        <select name="school" class="form-control input-sm">
                            <option value="">All Schools</option>
                            <?php
                            $mydb->setQuery("SELECT 
                                    DISTINCT a.SCHOOL
                                FROM tbl_scholarship_awards sa
                                INNER JOIN tbl_applicants a ON sa.APPLICANTID = a.APPLICANTID
                                LEFT JOIN tbl_alumni al ON a.APPLICANTID = al.APPLICANTID
                                WHERE sa.STATUS = 'Graduated' ");
                            $mydb->executeQuery();
                            $municipalities = $mydb->loadResultList();
                            foreach ($municipalities as $m):
                            ?>
                            <option value="<?= htmlspecialchars($m->SCHOOL) ?>" <?= isset($_GET['school']) && $_GET['school'] == $m->SCHOOL ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m->SCHOOL) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Apply Filter
                    </button>
                    <a href="index.php?view=graduates" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Graduates List -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-success">
            <div class="panel-heading">
                <i class="fa fa-graduation-cap"></i> Graduated Scholars List
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="graduates-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Award ID</th>
                                <th>Name</th>
                                <th>Municipality</th>
                                <th>School</th>
                                <th>Course</th>
                                <th>School Year</th>
                                <th>Final GPA</th>
                                <th>Honors</th>
                                <th>Graduation Date</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Build query with filters
                            $where = array();
                            $where[] = "sa.STATUS = 'Graduated'";
                            
                            if (isset($_GET['school_year']) && !empty($_GET['school_year'])) {
                                $where[] = "sa.SCHOOL_YEAR = '" . $_GET['school_year'] . "'";
                            }
                            
                            if (isset($_GET['municipality']) && !empty($_GET['municipality'])) {
                                $where[] = "a.MUNICIPALITY LIKE '%" . $_GET['municipality'] . "%'";
                            }

                            if (isset($_GET['school']) && !empty($_GET['school'])) {
                                $where[] = "a.SCHOOL LIKE '%" . $_GET['school'] . "%'";
                            }
                            
                            $where_clause = "WHERE " . implode(" AND ", $where);
                            
                            $sql = "
                                SELECT 
                                    sa.AWARD_ID,
                                    sa.SCHOOL_YEAR,
                                    sa.STATUS as AWARD_STATUS,
                                    a.APPLICANTID,
                                    a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME, a.SUFFIX,
                                    a.MUNICIPALITY, a.SCHOOL, a.COURSE,
                                    a.GPA as FINAL_GPA,
                                    a.STATUS as APPLICANT_STATUS,
                                    a.LAST_UPDATED as GRADUATION_DATE,
                                    al.HONORS
                                FROM tbl_scholarship_awards sa
                                INNER JOIN tbl_applicants a ON sa.APPLICANTID = a.APPLICANTID
                                LEFT JOIN tbl_alumni al ON a.APPLICANTID = al.APPLICANTID
                                WHERE sa.STATUS = 'Graduated' 
                                ORDER BY a.LAST_UPDATED DESC, a.LASTNAME ASC
                            ";
                            
                            $mydb->setQuery($sql);
                            $mydb->executeQuery();
                            $graduates = $mydb->loadResultList();
                            
                            foreach ($graduates as $g):
                                $fullname = $g->LASTNAME . ', ' . $g->FIRSTNAME;
                                if(!empty($g->MIDDLENAME)) {
                                    $fullname .= ' ' . substr($g->MIDDLENAME, 0, 1) . '.';
                                }
                                if(!empty($g->SUFFIX)) {
                                    $fullname .= ' ' . $g->SUFFIX;
                                }
                                
                                // Determine honors
                                $honors = $g->HONORS ?? 'None';
                                $honors_class = 'label-default';
                                if ($honors == 'Summa Cum Laude') {
                                    $honors_class = 'label-success';
                                } elseif ($honors == 'Magna Cum Laude') {
                                    $honors_class = 'label-info';
                                } elseif ($honors == 'Cum Laude') {
                                    $honors_class = 'label-primary';
                                } elseif ($honors != 'None') {
                                    $honors_class = 'label-warning';
                                }
                                
                                $graduation_date = $g->GRADUATION_DATE ? date('M d, Y', strtotime($g->GRADUATION_DATE)) : 'N/A';
                            ?>
                            <tr>
                                <td class="text-center"><?= str_pad($g->AWARD_ID, 5, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($fullname) ?></td>
                                <td><?= htmlspecialchars($g->MUNICIPALITY ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($g->SCHOOL ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($g->COURSE ?? 'N/A') ?></td>
                                <td><?= $g->SCHOOL_YEAR ?></td>
                                <td class="text-center"><strong><?= $g->FINAL_GPA ? $g->FINAL_GPA . '%' : 'N/A' ?></strong></td>
                                <td class="text-center"><span class="label <?= $honors_class ?>"><?= $honors ?></span></td>
                                <td><?= $graduation_date ?></td>
                                <td class="text-center">
                                    <a href="index.php?view=view&id=<?= $g->AWARD_ID ?>" class="btn btn-info btn-xs">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($graduates)): ?>
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="alert alert-info" style="margin: 20px;">
                                        <i class="fa fa-info-circle"></i> No graduated scholars found.
                                    </div>
                                </span>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#graduates-table').DataTable({
        "pageLength": 25,
        "order": [[8, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [9] }
        ],
        "language": {
            "emptyTable": "No graduated scholars found",
            "info": "Showing _START_ to _END_ of _TOTAL_ graduates",
            "infoEmpty": "Showing 0 to 0 of 0 graduates",
            "infoFiltered": "(filtered from _MAX_ total graduates)"
        }
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

.small-box.bg-aqua {
    background-color: #00c0ef;
    color: #fff;
}

.small-box.bg-yellow {
    background-color: #f39c12;
    color: #fff;
}

.small-box.bg-red {
    background-color: #dd4b39;
    color: #fff;
}
</style>