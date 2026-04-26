<?php
require_once("include/initialize.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISEASP - Ilocos Sur Educational Assistance & Scholarship Program</title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo web_root; ?>uploads/iseasp_logo.ico" type="image/png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #3E64D6;
            --primary-dark: #219a52;
            --primary-light: #2ecc71;
            --secondary-color: #3498db;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --success-color: #27ae60;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #2d4281;
            min-height: 100vh;
            color: #333;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            padding: 12px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            color: #3E64D6 !important;
            letter-spacing: -0.5px;
        }

        .navbar-brand i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark-color) !important;
            margin: 0 12px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-login {
            background: linear-gradient(135deg, #3E64D6, #3E64D6);
            color: white !important;
            padding: 8px 25px !important;
            border-radius: 50px;
            transition: all 0.3s ease;
            margin-left: 15px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-login::after {
            display: none;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 0 60px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: -1px;
        }

        .hero-title span {
            color: #3E64D6;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Status Check Card */
        .status-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            max-width: 550px;
            margin: 0 auto;
        }

        .status-card h3 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .status-card p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .lrn-input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .lrn-input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .lrn-input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: monospace;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        .lrn-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1);
        }

        .btn-check {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-check:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.4);
        }

        .btn-check i {
            margin-right: 10px;
        }

        /* Result Card */
        .result-card {
            margin-top: 30px;
            padding: 30px;
            border-radius: 20px;
            animation: slideIn 0.5s ease;
            display: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .result-card.success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 5px solid var(--success-color);
        }

        .result-card.info {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            border-left: 5px solid var(--info-color);
        }

        .result-card.warning {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            border-left: 5px solid var(--warning-color);
        }

        .result-card.danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 5px solid var(--danger-color);
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .result-icon {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .result-icon.success { color: var(--success-color); }
        .result-icon.info { color: var(--info-color); }
        .result-icon.warning { color: var(--warning-color); }
        .result-icon.danger { color: var(--danger-color); }

        .result-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .result-lrn {
            color: #666;
            font-family: monospace;
            font-size: 0.9rem;
        }

        .result-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: rgba(255, 255, 255, 0.9);
            padding: 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .detail-label {
            font-size: 0.8rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .status-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid rgba(0, 0, 0, 0.1);
        }

        .badge-custom {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .badge-custom i {
            font-size: 1rem;
        }

        .badge-success { background: linear-gradient(135deg, #27ae60, #219a52); color: white; }
        .badge-warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
        .badge-info { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
        .badge-danger { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
        .badge-primary { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; }

        .progress-custom {
            height: 10px;
            border-radius: 10px;
            background: #e0e0e0;
            overflow: hidden;
        }

        .progress-bar-custom {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            transition: width 0.5s ease;
        }

        .message-box {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            font-size: 0.9rem;
            color: #333;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 50px;
        }

        .section-title span {
            color: var(--primary-color);
        }

        .feature-card {
            text-align: center;
            padding: 40px 30px;
            border-radius: 20px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            line-height: 80px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            font-size: 2rem;
            border-radius: 50%;
            margin: 0 auto 20px;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .feature-text {
            color: #666;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 40px 0 20px;
        }

        .footer a {
            color: var(--primary-light);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .status-card {
                padding: 30px 20px;
                margin: 0 15px;
            }
            
            .result-details {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .detail-item {
                padding: 10px;
            }
            
            .result-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand logo" href="#">
                <img src="uploads/iseasp_logo.png" alt="ISEASP Logo" width="40" height="40" class="d-inline-block align-text-top">
                ISEASP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-login" href="admin/login.php">
                            <i class="fas fa-sign-in-alt"></i> Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Status Checker -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto hero-content">
                    <h1 class="hero-title">
                        Ilocos Sur Educational<br>
                        <span>Assistance & Scholarship Program</span>
                    </h1>
                    <p class="hero-subtitle">
                        Supporting the dreams of Ilocos Sur's youth through quality education
                    </p>

                    <!-- Status Check Card -->
                    <div class="status-card">
                        <h3><i class="fas fa-search"></i> Check Your Status</h3>
                        
                        <div class="lrn-input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" class="lrn-input" id="lrnInput" 
                                   placeholder="Enter your ID Number" inputmode="numeric">
                        </div>
                        
                        <button class="btn-check" id="checkBtn" type="button">
                            <i class="fas fa-search"></i> Check Status
                        </button>

                        <!-- Result Card -->
                        <div id="resultCard" class="result-card"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="features-section" style="background: #f8f9fa;">
        <div class="container">
            <h2 class="section-title">About <span>ISEASP</span></h2>
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #666;">
                        The Ilocos Sur Educational Assistance and Scholarship Program (ISEASP) is a provincial 
                        government initiative aimed at providing financial assistance to deserving students 
                        from Ilocos Sur. We believe that every student deserves access to quality education, 
                        regardless of their financial background.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="features-section">
        <div class="container">
            <h2 class="section-title">Contact <span>Us</span></h2>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-card" style="background: #f8f9fa;">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Address</h4>
                        <p>Provincial Capitol, Vigan City<br>Ilocos Sur, Philippines</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-card" style="background: #f8f9fa;">
                        <div class="feature-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4>Phone</h4>
                        <p>(077) 123-4567<br>(077) 765-4321</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-card" style="background: #f8f9fa;">
                        <div class="feature-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email</h4>
                        <p>iseasp@ilocossur.gov.ph<br>scholarship@ilocossur.gov.ph</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Ilocos Sur Educational Assistance and Scholarship Program. All rights reserved.</p>
            <p>Provincial Government of Ilocos Sur</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Bind click event properly
        $('#checkBtn').on('click', function(e) {
            e.preventDefault();
            checkStatus();
        });
        
        // Submit on Enter key
        $('#lrnInput').keypress(function(e) {
            if (e.which == 13) {
                e.preventDefault();
                checkStatus();
            }
        });
        
        // Allow only numbers in LRN input
        // $('#lrnInput').on('input', function() {
        //     this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
        // });
    });

    function checkStatus() {
        var lrn = $('#lrnInput').val().trim();
        
        // Validate LRN
        // if (lrn.length !== 12 || !/^\d+$/.test(lrn)) {
        //     showError('Please enter a valid 12-digit LRN');
        //     return;
        // }
        
        // Show loading state
        var btn = $('#checkBtn');
        btn.html('<span class="spinner"></span> Checking...').prop('disabled', true);
        
        // Hide previous result
        $('#resultCard').hide().empty();
        
        // AJAX request
        $.ajax({
            url: 'check-status.php',
            type: 'POST',
            data: { lrn: lrn },
            dataType: 'json',
            success: function(response) {
                btn.html('<i class="fas fa-search"></i> Check Status').prop('disabled', false);
                
                if (response.status === 'success') {
                    displayResult(response.data);
                } else {
                    showError(response.message || 'No record found for this LRN');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error);
                btn.html('<i class="fas fa-search"></i> Check Status').prop('disabled', false);
                showError('An error occurred. Please try again.');
            }
        });
    }

    function displayResult(data) {
        var resultCard = $('#resultCard');
        
        // Determine status class and icon
        // data.status can be 'Pending','Approved','Rejected','For Interview','Qualified','Scholar','Graduated','For Exam','For Evaluation'
        var statusInfo = getStatusInfo(data.status);
        
        // Build full name properly
        var fullName = '';
        if (data.lastname) {
            fullName = data.lastname;
            if (data.firstname) fullName = data.firstname + ' ' + fullName;
            if (data.middlename) fullName = data.firstname + ' ' + data.middlename + ' ' + data.lastname;
        } else {
            fullName = data.fullname || 'Applicant';
        }
        
        // Calculate requirements progress
        var reqPercentage = data.requirements_progress || 0;
        var verified = data.verified_requirements || 0;
        var total = data.total_requirements || 0;
        
        // Format date applied
        var dateApplied = data.date_applied || 'N/A';
        if (dateApplied !== 'N/A') {
            try {
                var dateObj = new Date(dateApplied);
                if (!isNaN(dateObj.getTime())) {
                    dateApplied = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                }
            } catch(e) {}
        }
        
        // Build result HTML
        var html = `
            <div class="result-header">
                <div class="result-icon ${statusInfo.class}">
                    <i class="fas ${statusInfo.icon}"></i>
                </div>
                <div>
                    <div class="result-name">${escapeHtml(fullName)}</div>
                    <div class="result-lrn">
                        <i class="fas fa-id-card"></i> LRN: ${data.lrn}
                    </div>
                </div>
            </div>
            
            <div class="result-details">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-university"></i> SCHOOL
                    </div>
                    <div class="detail-value">
                        ${escapeHtml(data.school || 'N/A')}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-book"></i> COURSE
                    </div>
                    <div class="detail-value">
                        ${escapeHtml(data.course || 'N/A')}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-layer-group"></i> YEAR LEVEL
                    </div>
                    <div class="detail-value">
                        ${escapeHtml(data.year_level || 'N/A')}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-calendar"></i> DATE APPLIED
                    </div>
                    <div class="detail-value">
                        ${escapeHtml(dateApplied)}
                    </div>
                </div>
            </div>
            
            <div class="status-badges">
                <span class="badge-custom ${statusInfo.badgeClass}">
                    <i class="fas ${statusInfo.icon}"></i> ${statusInfo.displayText}
                </span>
        `;
        
        // Add exam status if available and not pending (Only for For Exam or For Interview status)
        if ((data.status === 'For Exam' || data.status === 'For Interview') && data.exam_status && data.exam_status !== 'Pending' && data.exam_status !== '') {
            var examClass = data.exam_status === 'Passed' ? 'badge-success' : 'badge-danger';
            var examIcon = data.exam_status === 'Passed' ? 'fa-check-circle' : 'fa-times-circle';
            html += `
                <span class="badge-custom ${examClass}">
                    <i class="fas ${examIcon}"></i> EXAM: ${data.exam_status.toUpperCase()}
                </span>
            `;
        }
        
        // Add requirement status (Applicable for Pending, Approved, or Rejected status only)
        var reqClass = data.requirement_status === 'Complete' ? 'badge-success' : 'badge-warning';
        var reqIcon = data.requirement_status === 'Complete' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        var reqText = data.requirement_status === 'Complete' ? 'COMPLETE' : (data.requirement_status || 'PENDING');
        if(data.status === 'Pending' || data.status === 'Approved' || data.status === 'Rejected') {
            html += `
                <span class="badge-custom ${reqClass}">
                    <i class="fas ${reqIcon}"></i> REQUIREMENTS: ${reqText}
                </span>
            `;
        }

        // Determine the scholarship renewal status
        var renewalStatusHTML = '';
        if(data.renewal_status && data.renewal_status !== 'Pending') {
            if(data.renewal_status === 'Approved') {
                renewalStatusHTML += `
                    <span class="badge-custom badge-success">
                        <i class="fas fa-check-circle"></i> RENEWAL APPROVED
                    </span>
                    <span class="badge-custom badge-primary">
                        <i class="fas fa-calendar-alt"></i> Recent Renewal Semester and School Year: ${escapeHtml(data.renewal_semester || 'N/A')} ${escapeHtml(data.renewal_school_year || 'N/A')}
                    </span>
                `;
            } else if(data.renewal_status === 'Rejected') {
                renewalStatusHTML += `
                    <span class="badge-custom badge-danger">
                        <i class="fas fa-times-circle"></i> RENEWAL REJECTED
                    </span>
                    <span class="badge-custom badge-primary">
                        <i class="fas fa-calendar-alt"></i> Recent Renewal Semester and School Year: ${escapeHtml(data.renewal_semester || 'N/A')} ${escapeHtml(data.renewal_school_year || 'N/A')}
                    </span>
                `;
            }
        }

        html += renewalStatusHTML;
        html += `</div>`;
        
        // Add requirements progress bar (Applicable for Pending, Approved, or Rejected status only)
        if ((data.status === 'Pending' || data.status === 'Approved' || data.status === 'Rejected') && total > 0) {
            html += `
                <div style="margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px;">
                        <span style="color: #555; font-weight: 500;">
                            <i class="fas fa-check-circle"></i> Requirements Progress
                        </span>
                        <span style="color: #2c3e50; font-weight: 600;">${verified}/${total} verified</span>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: ${reqPercentage}%;"></div>
                    </div>
                </div>
            `;
        }
        
        // Add message if any
        if (data.message) {
            html += `
                <div class="message-box">
                    <i class="fas fa-info-circle" style="color: #27ae60;"></i> ${escapeHtml(data.message)}
                </div>
            `;
        }
        
        resultCard.html(html);
        resultCard.removeClass().addClass('result-card ' + statusInfo.class).show();
        
        // Scroll to result smoothly
        $('html, body').animate({
            scrollTop: resultCard.offset().top - 100
        }, 500);
    }

    function getStatusInfo(status) {
        var statusMap = {
            'Scholar': {
                class: 'success',
                icon: 'fa-graduation-cap',
                badgeClass: 'badge-success',
                displayText: 'ACTIVE SCHOLAR',
                borderColor: '#27ae60'
            },
            'Qualified': {
                class: 'info',
                icon: 'fa-star',
                badgeClass: 'badge-info',
                displayText: 'QUALIFIED',
                borderColor: '#3498db'
            },
            'For Interview': {
                class: 'warning',
                icon: 'fa-users',
                badgeClass: 'badge-warning',
                displayText: 'FOR INTERVIEW',
                borderColor: '#f39c12'
            },
            'For Exam': {
                class: 'warning',
                icon: 'fa-clipboard-list',
                badgeClass: 'badge-warning',
                displayText: 'FOR EXAM',
                borderColor: '#f39c12'
            },
            'Pending': {
                class: 'warning',
                icon: 'fa-clock',
                badgeClass: 'badge-warning',
                displayText: 'PENDING',
                borderColor: '#f39c12'
            },
            'Rejected': {
                class: 'danger',
                icon: 'fa-times-circle',
                badgeClass: 'badge-danger',
                displayText: 'NOT APPROVED',
                borderColor: '#e74c3c'
            },
            'Graduated': {
                class: 'success',
                icon: 'fa-trophy',
                badgeClass: 'badge-success',
                displayText: 'GRADUATED',
                borderColor: '#27ae60'
            }
        };
        
        return statusMap[status] || {
            class: 'info',
            icon: 'fa-info-circle',
            badgeClass: 'badge-info',
            displayText: (status || 'IN REVIEW').toUpperCase(),
            borderColor: '#3498db'
        };
    }

    function showError(message) {
        var resultCard = $('#resultCard');
        resultCard.html(`
            <div class="result-header">
                <div class="result-icon danger" style="background: white;">
                    <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                </div>
                <div>
                    <div class="result-name" style="color: #2c3e50;">Not Found</div>
                    <div class="result-lrn" style="color: #555;">No record found</div>
                </div>
            </div>
            <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 12px; color: #333;">
                <i class="fas fa-exclamation-circle" style="color: #e74c3c;"></i> ${escapeHtml(message)}
            </div>
            <div class="text-muted small" style="margin-top: 15px; color: #666;">
                <i class="fas fa-info-circle"></i> Please check your LRN and try again. If you believe this is an error, contact the ISEASP office.
            </div>
        `);
        resultCard.removeClass().addClass('result-card danger').show();
        
        // Scroll to result
        $('html, body').animate({
            scrollTop: resultCard.offset().top - 100
        }, 500);
    }

    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    </script>
</body>
</html>