<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect("index.php");

global $mydb;

// Load applicant data
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID = {$id}");
$mydb->executeQuery();
$app = $mydb->loadSingleResult();

if (!$app) {
    message("Applicant not found!", "error");
    redirect("index.php");
}

// Get municipalities for dropdown
$mydb->setQuery("SELECT * FROM tbl_municipalities WHERE IS_ACTIVE = 'Yes' ORDER BY MUNICIPALITY_NAME");
$mydb->executeQuery();
$municipalities = $mydb->loadResultList();

// Load audit trail - FIXED: Changed ACTION_BY to USERID
$mydb->setQuery("
    SELECT 
        l.ACTION,
        l.LOG_DATE,
        u.FULLNAME
    FROM tbl_application_log l
    LEFT JOIN tblusers u ON l.USERID = u.USERID
    WHERE l.APPLICANTID = '{$id}'
    ORDER BY l.LOG_DATE DESC
");
$mydb->executeQuery();
$logs = $mydb->loadResultList();
?>

<style>
    input[type="text"]:not([name="EMAIL"]), textarea {
        text-transform: uppercase;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Edit Applicant</h1>
    </div>
</div>

<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#personal">Personal Info</a></li>
    <li><a data-toggle="tab" href="#address">Address</a></li>
    <li><a data-toggle="tab" href="#educational">Educational</a></li>
    <li><a data-toggle="tab" href="#contact">Contact</a></li>
    <li><a data-toggle="tab" href="#socio">Socio-Economic</a></li>
    <!-- <li><a data-toggle="tab" href="#status">Status</a></li> -->
    <li><a data-toggle="tab" href="#history">History</a></li>
</ul>

<form class="form-horizontal" action="controller.php?action=edit" method="POST">
    <input type="hidden" name="id" value="<?php echo $app->APPLICANTID; ?>">

    <div class="tab-content">
        <!-- Personal Information Tab -->
        <div id="personal" class="tab-pane fade in active">
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">Personal Information</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">First Name: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="FIRSTNAME" value="<?php echo htmlspecialchars(strtoupper($app->FIRSTNAME)); ?>" required>
                        </div>
                        
                        <label class="col-md-2 control-label">Middle Name:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="MIDDLENAME" value="<?php echo ($app->MIDDLENAME !== '') ? htmlspecialchars(strtoupper($app->MIDDLENAME)) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Last Name: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="LASTNAME" value="<?php echo htmlspecialchars(strtoupper($app->LASTNAME)); ?>" required>
                        </div>
                        
                        <label class="col-md-2 control-label">Suffix:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="SUFFIX">
                                <option value="">None</option>
                                <option value="JR." <?php if($app->SUFFIX == 'JR.') echo 'selected'; ?>>JR.</option>
                                <option value="SR." <?php if($app->SUFFIX == 'SR.') echo 'selected'; ?>>SR.</option>
                                <option value="II" <?php if($app->SUFFIX == 'II') echo 'selected'; ?>>II</option>
                                <option value="III" <?php if($app->SUFFIX == 'III') echo 'selected'; ?>>III</option>
                                <option value="IV" <?php if($app->SUFFIX == 'IV') echo 'selected'; ?>>IV</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">School ID Number:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="LRN" value="<?php echo htmlspecialchars($app->LRN); ?>" maxlength="12" pattern="[0-9]{0,12}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Birthdate:</label>
                        <div class="col-md-4">
                            <input type="date" class="form-control input-sm" name="BIRTHDATE" value="<?php echo $app->BIRTHDATE; ?>">
                        </div>
                        
                        <label class="col-md-2 control-label">Birthplace:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="BIRTHPLACE" value="<?php echo htmlspecialchars(strtoupper($app->BIRTHPLACE)); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Gender:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="GENDER">
                                <option value="">Select</option>
                                <option value="Male" <?php if($app->GENDER == 'Male') echo 'selected'; ?>>Male</option>
                                <option value="Female" <?php if($app->GENDER == 'Female') echo 'selected'; ?>>Female</option>
                            </select>
                        </div>
                        
                        <label class="col-md-2 control-label">Civil Status:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="CIVIL_STATUS">
                                <option value="Single" <?php if($app->CIVIL_STATUS == 'Single') echo 'selected'; ?>>Single</option>
                                <option value="Married" <?php if($app->CIVIL_STATUS == 'Married') echo 'selected'; ?>>Married</option>
                                <option value="Widowed" <?php if($app->CIVIL_STATUS == 'Widowed') echo 'selected'; ?>>Widowed</option>
                                <option value="Separated" <?php if($app->CIVIL_STATUS == 'Separated') echo 'selected'; ?>>Separated</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Religion:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="RELIGION" value="<?php echo htmlspecialchars(strtoupper($app->RELIGION)); ?>">
                        </div>
                        <label class="col-md-2 control-label">Nationality:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="NATIONALITY" value="<?php echo htmlspecialchars(strtoupper($app->NATIONALITY ?? 'FILIPINO')); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Tab -->
        <div id="address" class="tab-pane fade">
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">Address Information</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">District: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="DISTRICT" required id="district">
                                <option value="">Select District</option>
                                <option value="1st District" <?php if($app->DISTRICT == '1st District') echo 'selected'; ?>>1st District</option>
                                <option value="2nd District" <?php if($app->DISTRICT == '2nd District') echo 'selected'; ?>>2nd District</option>
                            </select>
                        </div>
                        
                        <label class="col-md-2 control-label">Municipality: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="MUNICIPALITY" required id="municipality">
                                <option value="">Select Municipality</option>
                                <?php foreach($municipalities as $town): ?>
                                <option value="<?= strtoupper($town->MUNICIPALITY_NAME) ?>" 
                                    data-district="<?= strtoupper($town->DISTRICT) ?>"
                                    <?php if($app->MUNICIPALITY == strtoupper($town->MUNICIPALITY_NAME)) echo 'selected'; ?>>
                                    <?= strtoupper($town->MUNICIPALITY_NAME) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Barangay:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="BARANGAY" value="<?php echo htmlspecialchars(strtoupper($app->BARANGAY)); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Permanent Address:</label>
                        <div class="col-md-10">
                            <textarea class="form-control input-sm" name="PERMANENT_ADDRESS" rows="2"><?php echo htmlspecialchars(strtoupper($app->PERMANENT_ADDRESS)); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Current Address:</label>
                        <div class="col-md-10">
                            <textarea class="form-control input-sm" name="CURRENT_ADDRESS" rows="2"><?php echo htmlspecialchars(strtoupper($app->CURRENT_ADDRESS)); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Educational Tab -->
        <div id="educational" class="tab-pane fade">
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">Educational Information</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">School: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="SCHOOL" value="<?php echo htmlspecialchars(strtoupper($app->SCHOOL)); ?>" required>
                        </div>
                        
                        <label class="col-md-2 control-label">Course: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="COURSE" value="<?php echo htmlspecialchars(strtoupper($app->COURSE)); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Year Level: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="YEARLEVEL" required>
                                <option value="">Select</option>
                                <option <?php if($app->YEARLEVEL == '1st Year') echo 'selected'; ?>>1st Year</option>
                                <option <?php if($app->YEARLEVEL == '2nd Year') echo 'selected'; ?>>2nd Year</option>
                                <option <?php if($app->YEARLEVEL == '3rd Year') echo 'selected'; ?>>3rd Year</option>
                                <option <?php if($app->YEARLEVEL == '4th Year') echo 'selected'; ?>>4th Year</option>
                                <option <?php if($app->YEARLEVEL == '5th Year') echo 'selected'; ?>>5th Year</option>
                            </select>
                        </div>
                        
                        <label class="col-md-2 control-label">GPA:</label>
                        <div class="col-md-4">
                            <input type="number" class="form-control input-sm" name="GPA" value="<?php echo $app->GPA; ?>" step="0.01" min="0" max="100">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Application Type:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="APPLICATION_TYPE">
                                <option value="NEW APPLICANT" <?php if($app->APPLICATION_TYPE == 'NEW APPLICANT') echo 'selected'; ?>>NEW APPLICANT</option>
                                <option value="RENEWAL" <?php if($app->APPLICATION_TYPE == 'RENEWAL') echo 'selected'; ?>>RENEWAL</option>
                            </select>
                        </div>
                        
                        <label class="col-md-2 control-label">School Year:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="SCHOOL_YEAR">
                                <option value="2024-2025" <?php if($app->SCHOOL_YEAR == '2024-2025') echo 'selected'; ?>>2024-2025</option>
                                <option value="2025-2026" <?php if($app->SCHOOL_YEAR == '2025-2026') echo 'selected'; ?>>2025-2026</option>
                                <option value="2026-2027" <?php if($app->SCHOOL_YEAR == '2026-2027') echo 'selected'; ?>>2026-2027</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Tab -->
        <div id="contact" class="tab-pane fade">
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">Contact Information</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Contact No.: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="CONTACT" value="<?php echo htmlspecialchars($app->CONTACT); ?>" required>
                        </div>
                        
                        <label class="col-md-2 control-label">Email: <span class="text-danger">*</span></label>
                        <div class="col-md-4">
                            <input type="email" class="form-control input-sm" name="EMAIL" value="<?php echo htmlspecialchars($app->EMAIL); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Facebook URL:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="FACEBOOK_URL" value="<?php echo ($app->FACEBOOK_URL !== '') ? htmlspecialchars($app->FACEBOOK_URL) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Emergency Contact:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="EMERGENCY_CONTACT_NAME" value="<?php echo ($app->EMERGENCY_CONTACT_NAME !== '') ? htmlspecialchars(strtoupper($app->EMERGENCY_CONTACT_NAME)) : ''; ?>" placeholder="Full Name">
                        </div>
                        
                        <label class="col-md-2 control-label">Emergency No.:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="EMERGENCY_CONTACT_NUMBER" value="<?php echo ($app->EMERGENCY_CONTACT_NUMBER !== '') ? htmlspecialchars($app->EMERGENCY_CONTACT_NUMBER) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Relationship:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="EMERGENCY_CONTACT_RELATION">
                                <option value="">Select</option>
                                <option <?php if($app->EMERGENCY_CONTACT_RELATION == 'Father') echo 'selected'; ?>>Father</option>
                                <option <?php if($app->EMERGENCY_CONTACT_RELATION == 'Mother') echo 'selected'; ?>>Mother</option>
                                <option <?php if($app->EMERGENCY_CONTACT_RELATION == 'Guardian') echo 'selected'; ?>>Guardian</option>
                                <option <?php if($app->EMERGENCY_CONTACT_RELATION == 'Sibling') echo 'selected'; ?>>Sibling</option>
                                <option <?php if($app->EMERGENCY_CONTACT_RELATION == 'Other') echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Socio-Economic Tab -->
        <div id="socio" class="tab-pane fade">
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">Socio-Economic Information</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">4Ps Beneficiary:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="IS_4PS_BENEFICIARY">
                                <option value="No" <?php if($app->IS_4PS_BENEFICIARY == 'No') echo 'selected'; ?>>No</option>
                                <option value="Yes" <?php if($app->IS_4PS_BENEFICIARY == 'Yes') echo 'selected'; ?>>Yes</option>
                            </select>
                        </div>
                        
                        <label class="col-md-2 control-label">Indigenous People:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="IS_INDIGENOUS">
                                <option value="No" <?php if($app->IS_INDIGENOUS == 'No') echo 'selected'; ?>>No</option>
                                <option value="Yes" <?php if($app->IS_INDIGENOUS == 'Yes') echo 'selected'; ?>>Yes</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Family Annual Income (₱):</label>
                        <div class="col-md-4">
                            <input type="number" class="form-control input-sm" name="FAMILY_ANNUAL_INCOME" value="<?php echo $app->FAMILY_ANNUAL_INCOME; ?>" step="0.01" min="0">
                        </div>
                        
                        <label class="col-md-2 control-label">Parent's Occupation:</label>
                        <div class="col-md-4">
                            <input class="form-control input-sm" name="PARENT_OCCUPATION" value="<?php echo ($app->PARENT_OCCUPATION !== '') ? htmlspecialchars(strtoupper($app->PARENT_OCCUPATION)) : ''; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Tab -->
        <!-- <div id="status" class="tab-pane fade">
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">Application Status</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Status:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="STATUS">
                                <option <?php if($app->STATUS=='Pending') echo 'selected'; ?>>Pending</option>
                                <option <?php if($app->STATUS=='Approved') echo 'selected'; ?>>Approved</option>
                                <option <?php if($app->STATUS=='Rejected') echo 'selected'; ?>>Rejected</option>
                                <option <?php if($app->STATUS=='For Interview') echo 'selected'; ?>>For Interview</option>
                                <option <?php if($app->STATUS=='Qualified') echo 'selected'; ?>>Qualified</option>
                                <option <?php if($app->STATUS=='Scholar') echo 'selected'; ?>>Scholar</option>
                                <option <?php if($app->STATUS=='Graduated') echo 'selected'; ?>>Graduated</option>
                            </select>
                        </div>
                        
                        <label class="col-md-2 control-label">Exam Status:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="EXAM_STATUS">
                                <option <?php if($app->EXAM_STATUS=='Pending') echo 'selected'; ?>>Pending</option>
                                <option <?php if($app->EXAM_STATUS=='Passed') echo 'selected'; ?>>Passed</option>
                                <option <?php if($app->EXAM_STATUS=='Failed') echo 'selected'; ?>>Failed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Requirements Status:</label>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="REQUIREMENT_STATUS">
                                <option <?php if($app->REQUIREMENT_STATUS=='Pending') echo 'selected'; ?>>Pending</option>
                                <option <?php if($app->REQUIREMENT_STATUS=='Complete') echo 'selected'; ?>>Complete</option>
                                <option <?php if($app->REQUIREMENT_STATUS=='Incomplete') echo 'selected'; ?>>Incomplete</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-10">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Requirement Progress:</strong> 
                                <a href="../checklist/index.php?view=view&id=<?php echo $app->APPLICANTID; ?>">View Requirements Checklist</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- History Tab -->
        <div id="history" class="tab-pane fade">
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">Audit Trail</div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Performed By</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log->ACTION); ?></td>
                                        <td><?php echo htmlspecialchars($log->FULLNAME ?: 'Unknown'); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($log->LOG_DATE)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No history found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="form-group" style="margin-top: 20px;">
        <div class="col-md-offset-2 col-md-10">
            <button class="btn btn-primary" name="save" type="submit">
                <span class="fa fa-save"></span> Update Applicant
            </button>
            <a href="index.php" class="btn btn-default">
                <span class="fa fa-arrow-left"></span> Back to List
            </a>
        </div>
    </div>
</form>

<script>
document.getElementById('municipality').addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    var district = selected.getAttribute('data-district');
    if(district) {
        document.getElementById('district').value = district;
    }
}); 

$(document).ready(function() {
    var hash = window.location.hash;
    if(hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        window.location.hash = e.target.hash;
    });
});

// Auto-uppercase inputs
document.querySelectorAll('input[type="text"], textarea').forEach(function(input) {
    input.addEventListener('input', function() {
        if (this.type !== 'email' && !this.name.toLowerCase().includes('email')) {
            this.value = this.value.toUpperCase();
        }
    });
});
</script>