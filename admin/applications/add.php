<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;
?>

<style>
:root {
    --primary-color: #3E64D6;
    --primary-dark: #219a52;
    --primary-light: #2ecc71;
    --secondary-color: #3498db;
    --danger-color: #e74c3c;
    --warning-color: #f39c12;
    --success-color: #27ae60;
    --dark-bg: #2c3e50;
    --light-bg: #ecf0f1;
    --text-dark: #2c3e50;
    --text-light: #7f8c8d;
    --border-color: #bdc3c7;
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.page-header {
    background: linear-gradient(135deg, #3E64D6, #3E64D6);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-lg);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: rgba(255,255,255,0.1);
    transform: rotate(45deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

.page-header h1 {
    margin: 0;
    font-size: 1em;
    font-weight: 600;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.page-header small {
    display: block;
    font-size: 1.2em;
    margin-top: 10px;
    opacity: 0.9;
    font-weight: 300;
    color: white;
}

.instruction-card {
    background: white;
    border-radius: 15px;
    padding: 0;
    margin-bottom: 30px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: none;
}

.instruction-header {
    background: var(--dark-bg);
    color: white;
    padding: 15px 25px;
    border-bottom: none;
}

.instruction-header h4 {
    margin: 0;
    font-size: 1.3em;
    font-weight: 500;
}

.instruction-header i {
    margin-right: 10px;
    color: var(--warning-color);
}

.instruction-body {
    padding: 25px;
    background: #f8fafc;
}

.instruction-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.instruction-list li {
    padding: 12px 15px;
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    font-size: 0.95em;
    color: var(--text-dark);
    border-left: 4px solid var(--primary-color);
    transition: var(--transition);
}

.instruction-list li:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-lg);
}

.instruction-list li i {
    margin-right: 12px;
    font-size: 1.2em;
    color: var(--primary-color);
}

.instruction-list li strong {
    color: var(--primary-dark);
    margin: 0px 5px;
}

.warning-highlight {
    background: #fff3cd;
    color: #856404;
    border-left: 4px solid var(--warning-color) !important;
}

.warning-highlight i {
    color: var(--warning-color) !important;
}

.application-card {
    background: white;
    border-radius: 15px;
    margin-bottom: 25px;
    box-shadow: var(--shadow);
    overflow: hidden;
    border: none;
    transition: var(--transition);
}

.application-card:hover {
    box-shadow: var(--shadow-lg);
}

.card-header {
    padding: 18px 25px;
    background: linear-gradient(135deg, #3E64D6, #3E64D6);
    color: white;
    border-bottom: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: var(--transition);
}

.card-header:hover {
    background: linear-gradient(135deg, #2c385d, #2c385d);
}

.card-header h3 {
    margin: 0;
    font-size: 1.3em;
    font-weight: 500;
}

.card-header h3 i {
    margin-right: 10px;
    font-size: 1.2em;
}

.card-header .toggle-icon {
    font-size: 1.2em;
    transition: var(--transition);
}

.card-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.card-body {
    padding: 25px;
    background: white;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    min-width: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-dark);
    font-weight: 500;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group label i {
    margin-right: 8px;
    color: var(--primary-color);
    font-size: 1.1em;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95em;
    transition: var(--transition);
    background: #f8f9fa;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.form-group select.form-control {
    min-height: 0;
    line-height: 16px;
    padding: 0px 12px;
}

select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232c3e50' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 4px center;
    background-size: 12px;
    padding-right: 45px;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

select.form-control:hover {
    border-color: var(--primary-color);
    background-color: white;
}

select.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    background-color: white;
    box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1);
}

select.form-control option {
    padding: 12px;
    background: white;
    color: var(--text-dark);
}

input.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    background: white;
    box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1);
}

input.form-control[readonly] {
    background: #e9ecef;
    cursor: not-allowed;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.requirements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.requirements-category {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 2px solid transparent;
    transition: var(--transition);
}

.requirements-category.required-category {
    border-color: var(--danger-color);
}

.requirements-category h4 {
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary-color);
    color: var(--text-dark);
    font-size: 1.1em;
    font-weight: 600;
}

.requirements-category h4 i {
    margin-right: 8px;
    color: var(--primary-color);
}

.requirement-item {
    margin-bottom: 15px;
    padding: 12px;
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-left: 4px solid transparent;
}

.requirement-item:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-lg);
}

.requirement-item.required {
    border-left-color: var(--danger-color);
}

.requirement-item.optional {
    border-left-color: var(--secondary-color);
}

.requirement-item .checkbox {
    margin: 0;
    display: flex;
    align-items: flex-start;
}

.requirement-item .checkbox input[type="checkbox"] {
    margin-top: 3px;
    margin-right: 12px;
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary-color);
}

.requirement-item .checkbox label {
    flex: 1;
    margin: 0;
    font-size: 0.95em;
    color: var(--text-dark);
    cursor: pointer;
}

.requirement-item .checkbox small {
    display: block;
    margin-top: 5px;
    color: var(--text-light);
    font-size: 0.85em;
}

.requirement-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 15px;
    font-size: 0.75em;
    font-weight: 600;
    text-transform: uppercase;
    margin-left: 10px;
}

.requirement-badge.required {
    background: var(--danger-color);
    color: white;
}

.requirement-badge.optional {
    background: var(--secondary-color);
    color: white;
}

.radio-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.radio-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
}

.radio-item:hover {
    background: #e9ecef;
}

.radio-item input[type="radio"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: var(--primary-color);
}

.radio-item label {
    margin: 0;
    cursor: pointer;
    font-weight: 500;
}

.btn-submit {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
    color: white;
    border: none;
    padding: 15px 40px;
    font-size: 1.2em;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow-lg);
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(39, 174, 96, 0.3);
}

.btn-submit:active {
    transform: translateY(0);
}

.btn-submit i {
    margin-right: 10px;
    font-size: 1.2em;
}

.btn-cancel {
    background: white;
    color: var(--text-dark);
    border: 2px solid #e9ecef;
    padding: 13px 30px;
    font-size: 1.1em;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-block;
    margin-left: 15px;
}

.btn-cancel:hover {
    background: #f8f9fa;
    border-color: var(--text-light);
    color: var(--text-dark);
    text-decoration: none;
}

.table-responsive {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: var(--primary-color);
    color: white;
    font-weight: 500;
    padding: 12px 15px;
    border: none;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody td {
    padding: 12px 15px;
    vertical-align: middle;
    border-bottom: 1px solid #e9ecef;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.table .form-control {
    padding: 8px 12px;
    font-size: 0.9em;
}

.progress-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: var(--shadow);
}

.progress-step {
    flex: 1;
    text-align: center;
    position: relative;
}

.progress-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 50%;
    right: -15px;
    width: 30px;
    height: 2px;
    background: #e9ecef;
    transform: translateY(-50%);
}

.progress-step .step-number {
    width: 35px;
    height: 35px;
    background: #e9ecef;
    color: var(--text-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-weight: 600;
    transition: var(--transition);
}

.progress-step.active .step-number {
    background: var(--primary-color);
    color: white;
    box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.2);
}

.progress-step .step-label {
    font-size: 0.9em;
    color: var(--text-dark);
    font-weight: 500;
}

.progress-step.active .step-label {
    color: var(--primary-color);
    font-weight: 600;
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 1em;
    }
    
    .instruction-list {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .requirements-grid {
        grid-template-columns: 1fr;
    }
    
    .progress-indicator {
        flex-direction: column;
        gap: 15px;
    }
    
    .progress-step:not(:last-child)::after {
        display: none;
    }
    
    .btn-submit, .btn-cancel {
        width: 100%;
        margin: 10px 0;
    }
}

.requirement-item.checked {
    background-color: var(--success-color);
    color: white;
    border-left-color: var(--success-color);
}

.requirement-item.checked .checkbox label {
    color: white;
}

.requirement-item.checked .checkbox small {
    color: rgba(255,255,255,0.8);
}

.requirements-category.completed {
    border: 2px solid var(--success-color) !important;
    border-radius: 8px;
}
</style>

<div class="container-fluid">
    <div class="page-header">
        <h1>
            <i class="fa fa-graduation-cap"></i> ILOCOS SUR EDUCATIONAL ASSISTANCE & SCHOLARSHIP PROGRAM
            <small>Application Form for Academic Year 2025-2026</small>
        </h1>
    </div>

    <div class="progress-indicator">
        <div class="progress-step active">
            <div class="step-number">1</div>
            <div class="step-label">Personal Info</div>
        </div>
        <div class="progress-step">
            <div class="step-number">2</div>
            <div class="step-label">Education</div>
        </div>
        <div class="progress-step">
            <div class="step-number">3</div>
            <div class="step-label">Family</div>
        </div>
        <div class="progress-step">
            <div class="step-number">4</div>
            <div class="step-label">Requirements</div>
        </div>
    </div>

    <div class="instruction-card">
        <div class="instruction-header">
            <h4><i class="fa fa-exclamation-circle"></i> IMPORTANT INSTRUCTIONS</h4>
        </div>
        <div class="instruction-body">
            <ul class="instruction-list">
                
                <li><i class="fa fa-arrow-up"></i> All entries should be in <strong>UPPERCASE</strong> format</li>
                <li><i class="fa fa-check-square-o"></i> Place <strong>(X)</strong> in the appropriate space provided</li>
                <li><i class="fa fa-ban"></i> Put <strong>N/A</strong> if not applicable</li>
                <li><i class="fa fa-file-text-o"></i> Form must be accomplished properly and accurately</li>
                <li><i class="fa fa-copy"></i> All photocopied documents must be true copies of the original</li>
                <li class="warning-highlight"><i class="fa fa-warning"></i> <strong>IMPORTANT:</strong> Incomplete applications will NOT be processed</li>
            </ul>
        </div>
    </div>

    <form class="application-form" action="controller.php?action=add" method="POST" enctype="multipart/form-data" id="applicationForm">
        
        <!-- SECTION A: PERSONAL BACKGROUND -->
        <div class="application-card">
            <div class="card-header" onclick="toggleSection(this)">
                <h3><i class="fa fa-user"></i> A. PERSONAL BACKGROUND</h3>
                <span class="toggle-icon"><i class="fa fa-chevron-down"></i></span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Surname <span style="color:red;">*</span></span></label>
                        <input type="text" name="LASTNAME" class="form-control" required placeholder="SURNAME">
                    </div>
                    <div class="form-group">
                        <label>First Name <span style="color:red;">*</span></span></label>
                        <input type="text" name="FIRSTNAME" class="form-control" required placeholder="FIRST NAME">
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="MIDDLENAME" class="form-control" placeholder="MIDDLE NAME">
                    </div>
                    <div class="form-group">
                        <label>Ext. (Jr., III)</label>
                        <input type="text" name="SUFFIX" class="form-control" placeholder="e.g., JR., III">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Learner Reference Number (LRN) (For Incoming 1st Year College Students) <span style="color:red;">*</span></span></label>
                        <input type="text" name="LRN" class="form-control" required placeholder="12-DIGIT LRN" maxlength="12" pattern="[0-9]{12}">
                    </div>
                    <div class="form-group">
                        <label>ID Number (For Current College Students)  <span style="color:red;">*</span></span></label>
                        <input type="text" name="ID_NUMBER" class="form-control" required placeholder="Enter Unique ID Number">
                    </div>
                    <div class="form-group">
                        <label>Birthdate <span style="color:red;">*</span></span></label>
                        <input type="date" name="BIRTHDATE" class="form-control" required onchange="calculateAge(this)">
                    </div>
                    <div class="form-group">
                        <label>Birthplace</label>
                        <input type="text" name="BIRTHPLACE" class="form-control" placeholder="CITY, PROVINCE">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" id="age" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender <span style="color:red;">*</span></span></label>
                        <select name="GENDER" class="form-control" required>
                            <option value="">SELECT</option>
                            <option value="Male">MALE</option>
                            <option value="Female">FEMALE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Civil Status</label>
                        <select name="CIVIL_STATUS" class="form-control">
                            <option value="Single">SINGLE</option>
                            <option value="Married">MARRIED</option>
                            <option value="Widowed">WIDOWED</option>
                            <option value="Separated">SEPARATED</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><Religion</label>
                        <input type="text" name="RELIGION" class="form-control" value="ROMAN CATHOLIC">
                    </div>
                    <div class="form-group">
                        <label>Contact No. <span style="color:red;">*</span></label>
                        <input 
                            type="text" 
                            name="CONTACT" 
                            class="form-control" 
                            required 
                            placeholder="09XXXXXXXXX"
                            maxlength="11"
                            pattern="^09\d{9}$"
                            inputmode="numeric"
                            title="Contact number must be 11 digits and start with 09">
                        <small style="color: var(--text-light); display:block; margin-top:6px;">
                            Format: 09XXXXXXXXX
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address <span style="color:red;">*</span></span></label>
                        <input type="email" name="EMAIL" class="form-control" required placeholder="name@email.com">
                    </div>
                    <div class="form-group">
                        <label>Facebook URL (Optional)</label>
                        <input type="text" name="FACEBOOK_URL" class="form-control" placeholder="facebook.com/username">
                    </div>
                </div>

                <!-- EMERGENCY CONTACT SECTION - ADDED -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Emergency Contact Name</label>
                        <input type="text" name="EMERGENCY_CONTACT_NAME" class="form-control" placeholder="FULL NAME">
                    </div>
                    <div class="form-group">
                        <label>Emergency Contact Number</label>
                        <input type="text" name="EMERGENCY_CONTACT_NUMBER" class="form-control" placeholder="09123456789">
                    </div>
                    <div class="form-group">
                        <label>Relationship</label>
                        <select name="EMERGENCY_CONTACT_RELATION" class="form-control" style="color: #000;">
                            <option value="" style="color: #000;">SELECT</option>
                            <option value="Father" style="color: #000;">Father</option>
                            <option value="Mother" style="color: #000;">Mother</option>
                            <option value="Guardian" style="color: #000;">Guardian</option>
                            <option value="Spouse" style="color: #000;">Spouse</option>
                            <option value="Sibling" style="color: #000;">Sibling</option>
                            <option value="Relative" style="color: #000;">Relative</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="application-card">
            <div class="card-header" onclick="toggleSection(this)">
                <h3>ADDRESS INFORMATION</h3>
                <span class="toggle-icon"><i class="fa fa-chevron-down"></i></span>
            </div>
            <div class="card-body">

                <h4 style="margin-bottom: 15px; color: #3E64D6;">
                    Permanent Address  <span style="color:red;">*</span></span>
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label>Street / House No. <span style="color:red;">*</span></span></label>
                        <input type="text" name="PERM_STREET" class="form-control" required placeholder="HOUSE NO., STREET, PUROK">
                    </div>
                    <div class="form-group">
                        <label>Barangay <span style="color:red;">*</span></span></label>
                        <select name="PERM_BARANGAY" id="perm_barangay" class="form-control" required>
                            <option value="">SELECT BARANGAY</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Municipality / City <span style="color:red;">*</span></span></label>
                        <select name="PERM_MUNICIPALITY" class="form-control" required onchange="populateBarangays(this.value, 'perm_barangay')">
                            <option value="">SELECT MUNICIPALITY</option>
                            <?php
                            $municipalities = $mydb->setQuery("SELECT * FROM tbl_municipalities WHERE IS_ACTIVE = 'Yes' ORDER BY MUNICIPALITY_NAME");
                            $municipalities = $mydb->loadResultList();
                            foreach($municipalities as $town):
                            ?>
                            <option value="<?php echo strtoupper($town->MUNICIPALITY_NAME); ?>"><?php echo strtoupper($town->MUNICIPALITY_NAME); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Province <span style="color:red;">*</span></span></label>
                        <input type="text" name="PERM_PROVINCE" class="form-control" required placeholder="PROVINCE" value="ILOCOS SUR" disabled>
                    </div>
                </div>

                <hr style="margin: 25px 0;">

                <h4 style="margin-bottom: 15px; color: #3E64D6;">
                    <i class="fa fa-location-arrow"></i> Present Address
                </h4>
                <p style="margin-top: -5px; color: var(--text-light); font-size: 0.9em;">
                    Leave blank if same as permanent address.
                </p>

                <div class="form-row">
                    <div class="form-group">
                        <label>Street / House No.</label>
                        <input type="text" name="CURR_STREET" class="form-control" placeholder="HOUSE NO., STREET, PUROK">
                    </div>
                    <div class="form-group">
                        <label>Barangay</label>
                        <select name="CURR_BARANGAY" id="curr_barangay" class="form-control" required>
                            <option value="">SELECT BARANGAY</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Municipality / City</label>
                        <select name="CURR_MUNICIPALITY" class="form-control" required onchange="populateBarangays(this.value, 'curr_barangay')">
                            <option value="">SELECT MUNICIPALITY</option>
                            <?php
                            $municipalities = $mydb->setQuery("SELECT * FROM tbl_municipalities WHERE IS_ACTIVE = 'Yes' ORDER BY MUNICIPALITY_NAME");
                            $municipalities = $mydb->loadResultList();
                            foreach ($municipalities as $town):
                                ?>
                                <option value="<?php echo strtoupper($town->MUNICIPALITY_NAME); ?>">
                                    <?php echo strtoupper($town->MUNICIPALITY_NAME); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Province</label>
                        <input type="text" name="CURR_PROVINCE" class="form-control" placeholder="PROVINCE" value="ILOCOS SUR" disabled>
                    </div>
                </div>

            </div>
        </div>

        <!-- COURSE AND SCHOOL INFORMATION -->
        <div class="application-card">
            <div class="card-header" onclick="toggleSection(this)">
                <h3>COURSE AND SCHOOL INFORMATION</h3>
                <span class="toggle-icon"><i class="fa fa-chevron-down"></i></span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Course/Course Preference <span style="color:red;">*</span></span></label>
                        <input type="text" name="COURSE" class="form-control" required placeholder="e.g., BS INFORMATION TECHNOLOGY">
                    </div>
                    <div class="form-group">
                        <label>Year Level <span style="color:red;">*</span></span></label>
                        <select name="YEARLEVEL" class="form-control" required>
                            <option value="">SELECT YEAR</option>
                            <option value="1st Year">1ST YEAR</option>
                            <option value="2nd Year">2ND YEAR</option>
                            <option value="3rd Year">3RD YEAR</option>
                            <option value="4th Year">4TH YEAR</option>
                            <option value="5th Year">5TH YEAR</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>GWA (last SEM)</label>
                        <input type="number" name="GPA" class="form-control" step="0.01" min="0" max="100" placeholder="e.g., 83.00">
                    </div>
                    <div class="form-group">
                        <label>School Year</label>
                        <select name="SCHOOL_YEAR" class="form-control" style="color: #000;">
                            <option value="2025-2026" selected style="color: #000;">2025-2026</option>
                            <option value="2026-2027" style="color: #000;">2026-2027</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>School/College/University <span style="color:red;">*</span></span></label>
                        <!-- <input type="text" name="SCHOOL" class="form-control" required placeholder="NAME OF SCHOOL"> -->
                        <select name="SCHOOL", class="form-control" required style="color: #000;">
                            <option value="" style="color: #000;">SELECT SCHOOL</option>
                            <option value="UNIVERSITY OF NORTHERN PHILIPPINES" style="color: #000;">UNIVERSITY OF NORTHERN PHILIPPINES</option>
                            <option value="ILOCOS SUR COMMUNITY COLLEGE" style="color: #000;">ILOCOS SUR COMMUNITY COLLEGE</option>
                            <option value="ILOCOS SUR POLYTECHNIC STATE COLLEGE/UNIVERSITY OF ILOCOS PHILIPPINES" style="color: #000;">ILOCOS SUR POLYTECHNIC STATE COLLEGE/UNIVERSITY OF ILOCOS PHILIPPINES</option>
                            <option value="ST. PAUL COLLEGE OF ILOCOS SUR" style="color: #000;">ST. PAUL COLLEGE OF ILOCOS SUR</option>
                            <option value="DIVINE WORLD COLLEGE OF VIGAN" style="color: #000;">DIVINE WORLD COLLEGE OF VIGAN</option>
                            <option value="IMMACULATE CONCEPTION SCHOOL OF THEOLOGY" style="color: #000;">IMMACULATE CONCEPTION SCHOOL OF THEOLOGY</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>District <span style="color:red;">*</span></span></label>
                        <select name="DISTRICT" class="form-control" required style="color: #000;">
                            <option value="" style="color: #000;">SELECT DISTRICT</option>
                            <option value="1st District" style="color: #000;">1ST DISTRICT</option>
                            <option value="2nd District" style="color: #000;">2ND DISTRICT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Municipality <span style="color:red;">*</span></span></label>
                        <select name="MUNICIPALITY" class="form-control" required onchange="" style="color: #000;">
                            <option value="">SELECT MUNICIPALITY</option>
                            <?php
                            $municipalities = $mydb->setQuery("SELECT * FROM tbl_municipalities WHERE IS_ACTIVE = 'Yes' ORDER BY MUNICIPALITY_NAME");
                            $municipalities = $mydb->loadResultList();
                            foreach($municipalities as $town):
                            ?>
                            <option value="<?php echo strtoupper($town->MUNICIPALITY_NAME); ?>"><?php echo strtoupper($town->MUNICIPALITY_NAME); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- <div class="form-row">
                    <div class="form-group">
                        <label><i class="fa fa-building"></i> School Preference (if multiple)</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="school_pref[]" value="UNP"> UNP
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="school_pref[]" value="ISCC"> ISCC
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="school_pref[]" value="ISPSC"> ISPSC
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="school_pref[]" value="SPC"> SPC
                            </label>
                        </div>
                    </div>
                </div> -->

                <div class="form-row">
                    <div class="form-group">
                        <label>Other Scholarship?</label>
                        <div class="radio-group">
                            <label class="radio-item">
                                <input type="radio" name="other_scholarship" value="Yes"> <span>YES</span>
                            </label>
                            <label class="radio-item">
                                <input type="radio" name="other_scholarship" value="No" checked> <span>NO</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="other_scholarship_details" style="display: none; margin-top: 15px;">
                    <div class="form-group">
                        <label>If YES, please specify the nature of the other Scholarship Grant:</label>
                        <textarea name="other_scholarship_details" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION B: EDUCATIONAL BACKGROUND -->
        <div class="application-card">
            <div class="card-header" onclick="toggleSection(this)">
                <h3><i class="fa fa-history"></i> B. EDUCATIONAL BACKGROUND</h3>
                <span class="toggle-icon"><i class="fa fa-chevron-down"></i></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Name of School</th>
                                <th>School Address</th>
                                <th>Year Graduated</th>
                                <th>Honors/Awards</th>
                            </thead>
                        <tbody>
                            <tr>
                                <td><strong>ELEMENTARY</strong></td>
                                <td><input type="text" name="elem_school" class="form-control input-sm" placeholder="ELEMENTARY SCHOOL"></td>
                                <td><input type="text" name="elem_address" class="form-control input-sm" placeholder="ADDRESS"></td>
                                <td><input type="number" name="elem_year" class="form-control input-sm" placeholder="YYYY" min="1900" max="2100"></td>
                                <td><input type="text" name="elem_honors" class="form-control input-sm" placeholder="HONORS"></td>
                            </tr>
                            <tr>
                                <td><strong>SECONDARY</strong></td>
                                <td><input type="text" name="sec_school" class="form-control input-sm" placeholder="HIGH SCHOOL"></td>
                                <td><input type="text" name="sec_address" class="form-control input-sm" placeholder="ADDRESS"></td>
                                <td><input type="number" name="sec_year" class="form-control input-sm" placeholder="YYYY" min="1900" max="2100"></td>
                                <td><input type="text" name="sec_honors" class="form-control input-sm" placeholder="HONORS"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION C: FAMILY BACKGROUND -->
        <div class="application-card">
            <div class="card-header" onclick="toggleSection(this)">
                <h3><i class="fa fa-users"></i> C. FAMILY BACKGROUND</h3>
                <span class="toggle-icon"><i class="fa fa-chevron-down"></i></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="25%"></th>
                                <th width="25%">Father</th>
                                <th width="25%">Mother</th>
                                <th width="25%">Guardian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Full Name</strong></td>
                                <td><input type="text" name="father_name" class="form-control input-sm" placeholder="FATHER'S FULL NAME"></td>
                                <td><input type="text" name="mother_name" class="form-control input-sm" placeholder="MOTHER'S FULL NAME"></td>
                                <td><input type="text" name="guardian_name" class="form-control input-sm" placeholder="GUARDIAN'S FULL NAME"></td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>
                                    <select name="father_status" class="form-control input-sm" style="line-height: 15px;padding: 0px 12px;">
                                        <option value="Living">LIVING</option>
                                        <option value="Deceased">DECEASED</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="mother_status" class="form-control input-sm" style="line-height: 15px;padding: 0px 12px;">
                                        <option value="Living">LIVING</option>
                                        <option value="Deceased">DECEASED</option>
                                    </select>
                                </td>
                                <td><input type="text" class="form-control input-sm" readonly value="N/A"></td>
                            </tr>
                            <tr>
                                <td><strong>Occupation</strong></td>
                                <td><input type="text" name="father_occupation" class="form-control input-sm" placeholder="OCCUPATION"></td>
                                <td><input type="text" name="mother_occupation" class="form-control input-sm" placeholder="OCCUPATION"></td>
                                <td><input type="text" name="guardian_occupation" class="form-control input-sm" placeholder="OCCUPATION"></td>
                            </tr>
                            <tr>
                                <td><strong>Place of Work</strong></td>
                                <td><input type="text" name="father_workplace" class="form-control input-sm" placeholder="WORKPLACE"></td>
                                <td><input type="text" name="mother_workplace" class="form-control input-sm" placeholder="WORKPLACE"></td>
                                <td><input type="text" name="guardian_workplace" class="form-control input-sm" placeholder="WORKPLACE"></td>
                            </tr>
                            <tr>
                                <td><strong>Highest Educational Attainment</strong></td>
                                <td>
                                    <select name="father_education" class="form-control input-sm" style="line-height: 15px;padding: 0px 12px;">
                                        <option value="" style="color: #000;">SELECT</option>
                                        <option value="Elementary" style="color: #000;">ELEMENTARY</option>
                                        <option value="High School" style="color: #000;">HIGH SCHOOL</option>
                                        <option value="College" style="color: #000;">COLLEGE</option>
                                        <option value="Post Graduate" style="color: #000;">POST GRADUATE</option>
                                        <option value="Vocational" style="color: #000;">VOCATIONAL</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="mother_education" class="form-control input-sm" style="line-height: 15px;padding: 0px 12px;">
                                        <option value="" style="color: #000;">SELECT</option>
                                        <option value="Elementary" style="color: #000;">ELEMENTARY</option>
                                        <option value="High School" style="color: #000;">HIGH SCHOOL</option>
                                        <option value="College" style="color: #000;">COLLEGE</option>
                                        <option value="Post Graduate" style="color: #000;">POST GRADUATE</option>
                                        <option value="Vocational" style="color: #000;">VOCATIONAL</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="guardian_education" class="form-control input-sm" style="line-height: 15px;padding: 0px 12px;">
                                        <option value="" style="color: #000;">SELECT</option>
                                        <option value="Elementary" style="color: #000;">ELEMENTARY</option>
                                        <option value="High School" style="color: #000;">HIGH SCHOOL</option>
                                        <option value="College" style="color: #000;">COLLEGE</option>
                                        <option value="Post Graduate" style="color: #000;">POST GRADUATE</option>
                                        <option value="Vocational" style="color: #000;">VOCATIONAL</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Contact Number</strong></td>
                                <td><input type="text" name="father_contact" class="form-control input-sm" placeholder="CONTACT NUMBER"></td>
                                <td><input type="text" name="mother_contact" class="form-control input-sm" placeholder="CONTACT NUMBER"></td>
                                <td><input type="text" name="guardian_contact" class="form-control input-sm" placeholder="CONTACT NUMBER"></td>
                            </tr>
                            <tr>
                                <td><strong>Monthly Income (₱)</strong></td>
                                <td><input type="number" name="father_income" class="form-control input-sm" step="0.01" placeholder="0.00"></td>
                                <td><input type="number" name="mother_income" class="form-control input-sm" step="0.01" placeholder="0.00"></td>
                                <td><input type="number" name="guardian_income" class="form-control input-sm" step="0.01" placeholder="0.00"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Siblings Section -->
                <div class="form-row" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>Number of siblings in the family</label>
                        <input type="number" name="number_of_siblings" class="form-control" min="0" value="0" id="siblingCount" onchange="generateSiblingFields()">
                    </div>
                </div>

                <div id="siblings-container"></div>

                <div class="form-row" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>Do you have any brother/sister who is also a recipient of the Ilocos Sur Educational Assistance and Scholarship Program?</label>
                        <div class="radio-group">
                            <label class="radio-item">
                                <input type="radio" name="sibling_scholar" value="Yes"> <span>YES</span>
                            </label>
                            <label class="radio-item">
                                <input type="radio" name="sibling_scholar" value="No" checked> <span>NO</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="sibling_scholar_details" style="display: none; margin-top: 15px;">
                    <div class="form-group">
                        <label>If YES, state the Name, Year & Course and School where he/she is currently enrolled as scholar:</label>
                        <textarea name="sibling_scholar_details" class="form-control" rows="3" placeholder="Name: ___________________________ Year & Course: ___________________________ School: ___________________________"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOCUMENTARY REQUIREMENTS -->
        <div class="application-card">
            <div class="card-header" onclick="toggleSection(this)">
                <h3><i class="fa fa-file-text-o"></i> DOCUMENTARY REQUIREMENTS CHECKLIST</h3>
                <span class="toggle-icon"><i class="fa fa-chevron-down"></i></span>
            </div>

            <div class="card-body">
                <div class="requirements-grid">

                    <?php
                    // Fetch ALL requirements
                    $mydb->setQuery("SELECT * FROM tbl_requirement ORDER BY REQUIRED DESC, REQUIREMENT_NAME ASC");
                    $requirements = $mydb->loadResultList();
                    ?>

                    <!-- REQUIRED DOCUMENTS -->
                    <div class="requirements-category required-category">
                        <h4>REQUIRED DOCUMENTS</h4>

                        <?php foreach ($requirements as $req): ?>
                            <?php if ($req->REQUIRED == 'Yes'): ?>
                                <div class="requirement-item required">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox"
                                                name="requirements[]"
                                                value="<?php echo $req->REQUIREMENT_ID; ?>"
                                                class="required-checkbox">

                                            <span>
                                                <strong><?php echo $req->REQUIREMENT_NAME; ?></strong>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- OPTIONAL DOCUMENTS -->
                    <div class="requirements-category">
                        <h4>FINANCIAL DOCUMENTS (Optional)</h4>

                        <?php foreach ($requirements as $req): ?>
                            <?php if ($req->REQUIRED == 'No'): ?>
                                <div class="requirement-item optional">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox"
                                                name="requirements[]"
                                                value="<?php echo $req->REQUIREMENT_ID; ?>">

                                            <span>
                                                <strong><?php echo $req->REQUIREMENT_NAME; ?></strong>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                </div>

                <!-- KEEP THIS PART (unchanged) -->
                <div class="form-row" style="margin-top: 20px;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>4Ps Beneficiary?</label>
                            <select name="IS_4PS_BENEFICIARY" class="form-control" style="color: #000;">
                                <option value="No" style="color: #000;">NO</option>
                                <option value="Yes" style="color: #000;">YES</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Indigenous People (IP)?</label>
                            <select name="IS_INDIGENOUS" class="form-control" style="color: #000;">
                                <option value="No" style="color: #000;">NO</option>
                                <option value="Yes" style="color: #000;">YES</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Additional Comments</label>
                    <textarea name="missing_notes" class="form-control" rows="2" placeholder="Note any additional comments regarding this applicant"></textarea>
                </div>

                <div class="warning-box" style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                    <i class="fa fa-exclamation-triangle text-warning"></i>
                    <strong>REMINDER:</strong> Applications with incomplete required documents will be marked as INCOMPLETE and will NOT be processed until all requirements are submitted.
                </div>

                <input type="hidden" id="requirement_status" name="requirement_status" value="Incomplete">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div style="text-align: center; margin: 40px 0;">
            <!-- <button type="submit" name="save" class="btn-submit" onclick="return validateRequirements()"> -->
                <button type="submit" name="save" class="btn-submit">
                <i class="fa fa-check-circle"></i> SUBMIT APPLICATION
            </button>
            <a href="index.php" class="btn-cancel">
                <i class="fa fa-times"></i> CANCEL
            </a>
        </div>
    </form>
</div>

<script>
// Ilocos Sur Barangays Data (for dynamic dropdown)
const ilocosSurData = {
        "ALILEM": ["Alilem Daya (Poblacion)", "Amilongan", "Anaao", "Apang", "Apaya", "Batbato", "Daddaay", "Dalawa", "Kiat"],
        "BANAYOYO": ["Bagbagotot", "Banbanaal", "Bisangol", "Cadanglaan", "Casilagan Norte", "Casilagan Sur", "Elefante", "Guardia", "Lintic", "Lopez", "Montero", "Naguimba", "Pila", "Poblacion"],
        "BANTAY": ["Aggay", "An-annam", "Balaleng", "Banaoang", "Barangay 1 (Poblacion)", "Barangay 2 (Poblacion)", "Barangay 3 (Poblacion)", "Barangay 4 (Poblacion)", "Barangay 5 (Poblacion)", "Barangay 6 (Poblacion)", "Bulag", "Buquig", "Cabalanggan", "Cabaroan", "Cabusligan", "Capangdanan", "Guimod", "Lingsat", "Malingeb", "Mira", "Naguiddayan", "Ora", "Paing", "Puspus", "Quimmarayan", "Sagneb", "Sagpat", "San Isidro", "San Julian", "San Mariano (Sallacong)", "Sinabaan", "Taguiporo", "Taleb", "Tay-ac"],
        "BURGOS": ["Ambugat", "Balugang", "Bangbangar", "Bessang", "Cabcaburao", "Cadacad", "Callitong", "Dayanki", "Dirdirig (Dirdirig-Paday)", "Lesseb", "Lubing", "Lucaban", "Luna", "Macaoayan", "Manaboc", "Mapanit", "Nagpanaoan", "Paduros", "Patac", "Poblacion Norte (Bato)", "Poblacion Sur (Masingit)", "Sabangan Pinggan", "Subadi Norte", "Subadi Sur", "Taliao"],
        "CABUGAO": ["Alinaay", "Aragan", "Arnap", "Baclig (Poblacion)", "Bato", "Bonifacio (Poblacion)", "Bungro", "Cacadiran", "Caellayan", "Carusipan", "Catucdaan", "Cuancabal", "Cuantacla", "Daclapan", "Dardarat", "Lipit", "Margaay", "Nagsantaan", "Nagsincaoan", "Namruangan", "Pila", "Pug-os", "Quezon (Poblacion)", "Reppaac", "Rizal (Poblacion)", "Sabang", "Sagayaden", "Salapasap", "Salomague", "Sisim", "Turod", "Turod-Patac"],
        "CANDON CITY": ["Allangigan Primero", "Allangigan Segundo", "Amguid", "Ayudante", "Bagani Camposanto", "Bagani Gabor", "Bagani Tocgo", "Bagani Ubbog", "Bagar", "Balingaoan", "Boburot", "Bugnay", "Calaoaan", "Calongbuyan", "Caterman", "Cubcubboot", "Darapidap", "Langlangca Primero", "Langlangca Segundo", "Oaig-Daya", "Palacapac", "Paras", "Parioc Primero", "Parioc Segundo", "Patpata Primero", "Patpata Segundo", "Paypayad", "Salvador Primero", "Salvador Segundo", "San Agustin", "San Andres", "San Antonio (Poblacion)", "San Isidro (Poblacion)", "San Jose (Poblacion)", "San Juan (Poblacion)", "San Nicolas", "San Pedro", "Santo Tomas", "Tablac", "Talogtog", "Tamurong Primero", "Tamurong Segundo", "Villarica"],
        "CAOAYAN": ["Anonang Mayor", "Anonang Menor", "Baggoc", "Callaguip", "Caparacadan", "Don Alejandro Quirologico (Poblacion)", "Don Dimas Querubin (Poblacion)", "Don Lorenzo Querubin (Poblacion)", "Fuerte", "Manangat", "Naguilian", "Nansuagao", "Pandan", "Pantay-Quitiquit", "Pantay Tamurong", "Puro", "Villamar"],
        "CERVANTES": ["Aluling", "Comillas North", "Comillas South", "Concepcion (Poblacion)", "Dinwede East", "Dinwede West", "Libang", "Malaya", "Pilipil", "Remedios", "Rosario (Poblacion)", "San Juan", "San Luis"],
        "GALIMUYOD": ["Abaya", "Bidbiday", "Bitong", "Borobor", "Calimugtong", "Calongbuyan", "Calumbaya", "Daldagan", "Kilang", "Matanubong", "Mckinley", "Nagsingcaoan", "Oaig-Daya", "Pagangpang", "Patac", "Poblacion", "Rubio", "Sabangan-Bato", "Sacaang", "San Vicente", "Sapang"],
        "GREGORIO DEL PILAR": ["Alfonso (Tangaoan)", "Bussot", "Concepcion", "Dapdappig", "Matue-Butarag", "Poblacion Norte", "Poblacion Sur"],
        "LIDLIDDA": ["Banucal", "Bequi-Walin", "Bugui", "Calungbuyan", "Carcarabasa", "Labut", "Poblacion Norte", "Poblacion Sur", "San Vicente", "Suysuyan", "Tay-ac"],
        "MAGSINGAL": ["Alangan", "Bacar", "Barbarit", "Bungro", "Cabaroan", "Cadanglaan", "Caraisan", "Dacutan", "Labut", "Maas-asin", "Macatcatud", "Manzante", "Maratudo", "Miramar", "Namalpalan", "Napo", "Pagsanaan Norte", "Pagsanaan Sur", "Panay Norte", "Panay Sur", "Patong", "Puro", "San Basilio (Poblacion)", "San Clemente (Poblacion)", "San Julian (Poblacion)", "San Lucas (Poblacion)", "San Ramon (Poblacion)", "San Vicente (Poblacion)", "Santa Monica", "Sarsaracat"],
        "NAGBUKEL": ["Balaweg", "Bandril", "Bantugo", "Cadacad", "Casilagan", "Casocos", "Lapting", "Mapisi", "Mission", "Poblacion East", "Poblacion West", "Taleb"],
        "NARVACAN": ["Abuor", "Ambulogan", "Aquib", "Banglayan", "Bantay Abot", "Bulanos", "Cadacad", "Cagayungan", "Camarao", "Casilagan", "Codoog", "Dasay", "Dinalaoan", "Estancia", "Lanipao", "Lungog", "Margaay", "Marozo", "Naguneg", "Orence", "Pantoc", "Paratong", "Parparia", "Quinarayan", "Rivadavia", "San Antonio", "San Jose (Poblacion)", "San Pablo", "San Pedro", "Santa Lucia (Poblacion)", "Sarmingan", "Sucoc", "Sulvec", "Turod"],
        "QUIRINO": ["Banoen", "Cayus", "Lamag (Tubtuba)", "Legleg (Poblacion)", "Malideg", "Namitpit", "Patiacan", "Patungcaleo (Lamag)", "Suagayan"],
        "SALCEDO": ["Atabay", "Balidbid", "Baluarte", "Baybayading", "Boguibog", "Bulala-Leguey", "Calangcuasan", "Culiong", "Dinaratan", "Kaliwakiw", "Kinmarin", "Lucbuban", "Madarang", "Maligcong", "Pias", "Poblacion Norte", "Poblacion Sur", "San Gaspar", "San Tiburcio", "Ubbog"],
        "SAN EMILIO": ["Cabaroan (Poblacion)", "Kalumsing", "Lancuas", "Matibuey", "Paltoc", "San Miliano", "Sibsibbu", "Tiagan"],
        "SAN ESTEBAN": ["Ansad", "Apatot", "Bateria", "Cabaroan", "Cappa-cappa", "Poblacion", "San Nicolas", "San Pablo", "San Rafael", "Villa Quirino"],
        "SAN ILDEFONSO": ["Arnap", "Bahet", "Belen", "Bungro", "Busiing Norte", "Busiing Sur", "Dongalo", "Gongogong", "Iboy", "Kinamantirisan", "Otol-Patac", "Poblacion East", "Poblacion West", "Sagneb", "Sagsagat"],
        "SAN JUAN": ["Asilang", "Barbar", "Bacsil", "Baliw", "Bannuar (Poblacion)", "Cabanglotan", "Cacandongan", "Camanggaan", "Camindoroan", "Caronoan", "Darao", "Dardarat", "Guimod Norte", "Guimod Sur", "Immayos Norte", "Immayos Sur", "Labnig", "Lapting", "Lira (Poblacion)", "Malamin", "Muraya", "Nagsabaran", "Nagsupotan", "Pandayan (Poblacion)", "Refaro", "Resurreccion (Poblacion)", "Sabangan", "San Isidro", "Saoang", "Solotsolot", "Sunggiam", "Surngit"],
        "SAN VICENTE": ["Bantaoay", "Bayubay Norte", "Bayubay Sur", "Lubong", "Poblacion", "Pudoc", "San Sebastian"],
        "SANTA": ["Ampandula", "Banaoang", "Basug", "Bucalag", "Cabangaran", "Calungboyan", "Casiber", "Dammay", "Labut Norte", "Labut Sur", "Mabilbila Norte", "Mabilbila Sur", "Magsaysay District (Poblacion)", "Manueva", "Marcos (Poblacion)", "Nagpanaoan", "Namalangan", "Oribi", "Pasungol", "Quezon (Poblacion)", "Quirino (Poblacion)", "Rancho", "Rizal", "Sacuyya Norte", "Sacuyya Sur", "Tabucolan"],
        "SANTA CATALINA": ["Cabaroan", "Cabittaogan", "Cabuloan", "Pangada", "Paratong", "Poblacion", "Sinabaan", "Subec", "Tamorong"],
        "SANTA CRUZ": ["Amarao", "Babayoan", "Bacsayan", "Banay", "Bayugao Este", "Bayugao Oeste", "Besalan", "Bugbuga", "Calaoaan", "Camanggaan", "Casilagan", "Coscosnong", "Daligan", "Dili", "Gabor Norte", "Gabor Sur", "Guimod", "Lalong", "Lantag", "Las-ud", "Mambog", "Mantanas", "Nagtengnga", "Padaoil", "Paratong", "Pattiqui", "Pidpid", "Pilar", "Pinipin", "Poblacion Este", "Poblacion North", "Poblacion Sur", "Poblacion Weste", "Quinfermin", "Quinsoriano", "Sagat", "San Antonio", "San Jose", "San Pedro", "Saoat", "Sevilla", "Sidaoen", "Suyo", "Tampugo", "Turod", "Villa Garcia", "Villa Hermosa", "Villa Laurencia"],
        "SANTA LUCIA": ["Alincaoeg", "Angkileng", "Arangin", "Ayusan (Poblacion)", "Banbanaba", "Bani", "Bao-as", "Barangobong (Poblacion)", "Buliclic", "Burgos (Poblacion)", "Cabaritan", "Catayagan", "Conconig East", "Conconig West", "Damacuag", "Lubong", "Nagrebcan", "Nagtablaan", "Namin-amin", "Nangalisan", "Palali Norte", "Palali Sur", "Paoc Norte", "Paoc Sur", "Paratong", "Pila East", "Pila West", "Poblacion", "Quinabalayangan", "Ronda", "Sabuanan", "San Juan", "San Pedro", "Sapang", "Suagayan", "Vical"],
        "SANTA MARIA": ["Ag-agrao", "Ampuagan", "Baballasioan", "Baliw Daya", "Baliw Laud", "Bia-o", "Butir", "Cabaroan", "Casitan", "Danuman East", "Danuman West", "Dunglayan", "Gusing", "Langaoan", "Laslasong Norte", "Laslasong Sur", "Laslasong West", "Lingsat", "Lubong", "Maynganay Norte", "Maynganay Sur", "Nagsayaoan", "Nagtupacan", "Nalvo", "Pacang", "Penned", "Poblacion Norte", "Poblacion Sur", "Silag", "Sumagui", "Suso", "Tangaoan", "Tinaan"],
        "SANTIAGO": ["Al-aludig", "Ambucao", "Baybayabas", "Bigbiga", "Bulbulala", "Busel-busel", "Butol", "Caburao", "Dan-ar", "Gabao", "Guinabang", "Imus", "Lang-ayan", "Mambug", "Nalasin", "Olo-olo Norte", "Olo-olo Sur", "Poblacion Norte", "Poblacion Sur", "Sabangan", "Salincub", "San Jose (Baraoas)", "San Roque", "Ubbog"],
        "SANTO DOMINGO": ["Binalayangan", "Binongan", "Borobor", "Cabaritan", "Cabigbigaan", "Calautit", "Calay-ab", "Camestizoan", "Casili", "Flora", "Lagatit", "Laoingen", "Lussoc", "Nagbettedan", "Naglaoa-an", "Nalasin", "Nambaran", "Nanerman", "Napo", "Padu Chico", "Padu Grande", "Paguraper", "Panay", "Pangpangdan", "Parada", "Paras", "Poblacion", "Puerta Real", "Pussuac", "Quimmarayan", "San Pablo", "Santa Cruz", "Santo Tomas", "Sived", "Suksukit", "Vacunero"],
        "SIGAY": ["Abaccan", "Mabileg", "Matallucod", "Poblacion (Madayaw)", "San Elias", "San Ramon", "Santo Rosario"],
        "SINAIT": ["Aguing", "Baliw", "Ballaigui (Poblacion)", "Baracbac", "Battog", "Binacud", "Cabangtalan", "Cabarambanan", "Cabulalaan", "Cadanglaan", "Calanutian", "Calingayan", "Curtin", "Dadalaquiten Norte", "Dadalaquiten Sur", "Dean Leopoldo Yabes (Pug-os)", "Duyayyat", "Jordan", "Katipunan", "Macabiag (Poblacion)", "Magsaysay", "Marnay", "Masadag", "Nagbalioartian", "Nagcullooban", "Nagongburan", "Namnama (Poblacion)", "Pacis", "Paratong", "Poblacion", "Purag", "Quibit-quibit", "Quimmallogong", "Rang-ay (Poblacion)", "Ricudo", "Sabangan (Marcos)", "Sallacapo", "Santa Cruz", "Sapriana", "Tapao", "Teppeng", "Tubigay", "Ubbog", "Zapat"],
        "SUGPON": ["Balbalayang (Poblacion)", "Banga", "Caoayan", "Danac", "Licungan (Cullang)", "Pangotan"],
        "SUYO": ["Baringcucurong", "Cabugao", "Man-atong", "Patoc-ao", "Poblacion (Kimpusa)", "Suyo Proper", "Urzadan", "Uso"],
        "TAGUDIN": ["Ag-aguman", "Ambalayat", "Baracbac", "Baritao", "Bario-an", "Becques", "Bimmanga", "Bio", "Bitalag", "Borono", "Bucao East", "Bucao West", "Cabaroan", "Cabugbugan", "Cabulanglangan", "Dacutan", "Dardarat", "Del Pilar (Poblacion)", "Farola", "Gabur", "Garitan", "Jardin", "Lacong", "Lantag", "Las-ud", "Libtong", "Lubnac", "Magsaysay (Poblacion)", "Malacañang", "Pacac", "Pallogan", "Poblacion", "Pudoc East", "Pudoc West", "Pula", "Quirino (Poblacion)", "Ranget", "Rizal (Poblacion)", "Salvacion", "San Miguel", "Sawat", "Tallaoen", "Tampugo", "Tarangotong"],
        "VIGAN CITY": ["Ayusan Norte", "Ayusan Sur", "Barangay I (Poblacion)", "Barangay II - Amianance (Poblacion)", "Barangay III (Poblacion)", "Barangay IV - Solid West (Poblacion)", "Barangay IX - Cuta (Poblacion)", "Barangay V - Pagpartian (Poblacion)", "Barangay VI - Pagpandayan (Poblacion)", "Barangay VII - Pagburnayan (Poblacion)", "Barangay VIII - Cabasaan/Santa Elena (Poblacion)", "Barraca", "Beddeng Daya", "Beddeng Laud", "Bongtolan", "Bulala", "Cabalangegan", "Cabaroan Daya", "Cabaroan Laud", "Camangaan", "Capangpangan", "Mindoro", "Nagsangalan", "Pantay Daya", "Pantay Fatima", "Pantay Laud", "Paoa", "Paratong", "Pong-ol", "Purok-a-bassit", "Purok-a-dackel", "Raois", "Rugsuanan", "San Jose", "San Julian Norte", "San Julian Sur", "San Pedro", "Tamag"]
    };

// Toggle sections
function toggleSection(header) {
    var card = header.closest('.application-card');
    var body = card.querySelector('.card-body');
    var icon = header.querySelector('.toggle-icon i');
    
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.className = 'fa fa-chevron-down';
        header.classList.remove('collapsed');
    } else {
        body.style.display = 'none';
        icon.className = 'fa fa-chevron-right';
        header.classList.add('collapsed');
    }
}

// Populate barangays based on selected municipality
function populateBarangays(munValue, barangayId) {
    var barangaySelect = document.getElementById(barangayId);
    barangaySelect.innerHTML = '<option value="">SELECT BARANGAY</option>';
    console.log('Selected Municipality:', munValue);
    
    if (ilocosSurData[munValue]) {
        ilocosSurData[munValue].forEach(function(barangay) {
            var option = document.createElement('option');
            option.value = barangay.toUpperCase();
            option.textContent = barangay.toUpperCase();
            barangaySelect.appendChild(option);
        });
    }

}


// Calculate age from birthdate
function calculateAge(input) {
    var birthdate = new Date(input.value);
    var today = new Date();
    var age = today.getFullYear() - birthdate.getFullYear();
    var m = today.getMonth() - birthdate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) {
        age--;
    }
    document.getElementById('age').value = age;
}

// Generate sibling fields dynamically
function generateSiblingFields() {
    var count = document.getElementById('siblingCount').value;
    var container = document.getElementById('siblings-container');
    var html = '';
    
    if (count > 0) {
        html += '<div class="table-responsive" style="margin-top: 20px;">';
        html += '<table class="table table-bordered">';
        html += '<thead> <tr><th>Name</th><th>Age</th><th>Highest Educational Attainment</th></tr> </thead>';
        html += '<tbody>';
        
        for (var i = 1; i <= count; i++) {
            html += '<tr>';
            html += '<td><input type="text" name="sibling_name_' + i + '" class="form-control input-sm" placeholder="FULL NAME"></td>';
            html += '<td><input type="number" name="sibling_age_' + i + '" class="form-control input-sm" placeholder="AGE"></td>';
            html += '<td><select name="sibling_education_' + i + '" class="form-control input-sm" style="line-height: 15px;padding: 0px 12px;"><option value="" style="color: #000;">SELECT</option><option value="Elementary" style="color: #000;">ELEMENTARY</option><option value="High School" style="color: #000;">HIGH SCHOOL</option><option value="College" style="color: #000;">COLLEGE</option><option value="Post Graduate" style="color: #000;">POST GRADUATE</option><option value="Vocational" style="color: #000;">VOCATIONAL</option></select></td>';
            html += '</tr>';
        }
        
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
    }
    
    container.innerHTML = html;
}

// Validate Contact Number
document.querySelectorAll('input[name="CONTACT"], input[name="EMERGENCY_CONTACT_NUMBER"], input[name="father_contact"], input[name="mother_contact"], input[name="guardian_contact"]').forEach(function(input) {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });

    input.addEventListener('blur', function() {
        if (this.value !== '' && !/^09\d{9}$/.test(this.value)) {
            this.setCustomValidity('Contact number must be 11 digits and start with 09.');
        } else {
            this.setCustomValidity('');
        }
    });
});

// Show/hide other scholarship details
document.querySelectorAll('input[name="other_scholarship"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('other_scholarship_details').style.display = 
            this.value === 'Yes' ? 'block' : 'none';
    });
});

// Show/hide sibling scholar details
document.querySelectorAll('input[name="sibling_scholar"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('sibling_scholar_details').style.display = 
            this.value === 'Yes' ? 'block' : 'none';
    });
});

// Validate requirements before submission
function validateRequirements() {
    var requiredCheckboxes = document.querySelectorAll('.required-checkbox');
    var allChecked = true;
    var missingList = [];
    
    requiredCheckboxes.forEach(function(checkbox) {
        if (!checkbox.checked) {
            allChecked = false;
            var label = checkbox.closest('.requirement-item').querySelector('strong').innerText;
            missingList.push(label);
        }
    });
    
    if (!allChecked) {
        var message = 'The following REQUIRED documents are missing:\n\n';
        missingList.forEach(function(item) {
            message += '• ' + item + '\n';
        });
        message += '\nAre you sure you want to continue? The application will be marked as INCOMPLETE and will NOT be processed until all requirements are submitted.';
        
        return confirm(message);
    }
    
    document.getElementById('requirement_status').value = 'Complete';
    
    var btn = document.querySelector('.btn-submit');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> SUBMITTING...';
    btn.disabled = true;
    
    return true;
}

// Auto-uppercase inputs
document.querySelectorAll('input[type="text"], textarea').forEach(function(input) {
    input.addEventListener('input', function() {
        if (this.type !== 'email' && !this.placeholder.includes('@')) {
            this.value = this.value.toUpperCase();
        }
    });
});

// Validate LRN
document.querySelector('input[name="LRN"]').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
});

// Smooth scroll to top on submit
document.getElementById('applicationForm').addEventListener('submit', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Change requirement item color when checkbox is checked
document.addEventListener('DOMContentLoaded', function() {
    // Function to check if all checkboxes in a category are checked
    function checkCategoryCompletion() {
        // Check required category
        var requiredCategory = document.querySelector('.required-category');
        if (requiredCategory) {
            var requiredCheckboxes = requiredCategory.querySelectorAll('input[type="checkbox"]');
            var allRequiredChecked = Array.from(requiredCheckboxes).every(cb => cb.checked);
            if (allRequiredChecked) {
                requiredCategory.classList.add('completed');
            } else {
                requiredCategory.classList.remove('completed');
            }
        }

        // Check optional category
        var optionalCategory = document.querySelector('.requirements-category:not(.required-category)');
        if (optionalCategory) {
            var optionalCheckboxes = optionalCategory.querySelectorAll('input[type="checkbox"]');
            var allOptionalChecked = Array.from(optionalCheckboxes).every(cb => cb.checked);
            if (allOptionalChecked) {
                optionalCategory.classList.add('completed');
            } else {
                optionalCategory.classList.remove('completed');
            }
        }
    }

    document.querySelectorAll('input[type="checkbox"][name="requirements[]"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var item = this.closest('.requirement-item');
            if (this.checked) {
                item.classList.add('checked');
            } else {
                item.classList.remove('checked');
            }
            // Check category completion after each change
            checkCategoryCompletion();
        });
    });
});
</script>