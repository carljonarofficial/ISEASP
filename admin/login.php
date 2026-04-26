<?php
require_once("../include/initialize.php");

if(isset($_SESSION['ADMIN_USERID'])){
    redirect(web_root."admin/index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>ISEASP | Admin Login</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo web_root; ?>uploads/iseasp_logo.ico" type="image/png">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?php echo web_root;?>bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/font-awesome/css/font-awesome.min.css"> 
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo web_root;?>dist/css/AdminLTE.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?php echo web_root;?>plugins/iCheck/square/blue.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
            pointer-events: none;
        }
        
        .login-page {
            background: transparent;
        }
        
        .login-box {
            width: 450px;
            margin: 2% auto;
            animation: fadeInUp 0.8s ease;
        }
        
        .login-logo {
            margin-bottom: 30px;
        }
        
        .login-logo a {
            font-size: 32px;
            font-weight: 800;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            letter-spacing: -0.5px;
        }
        
        .login-logo a i {
            margin-right: 10px;
            color: #fff;
        }
        
        .login-logo p {
            font-size: 14px;
            font-weight: 300;
            margin-top: 10px;
            opacity: 0.9;
        }
        
        .login-box-body {
            background: white;
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .login-box-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background-color: #2d4281;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h4 {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .login-header p {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 0;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2d4281;
            font-size: 16px;
            z-index: 10;
        }
        
        .form-control {
            height: 50px;
            padding: 10px 15px 10px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            border-color: #2d4281;
            box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1);
            outline: none;
        }
        
        .checkbox {
            margin-top: 15px;
            margin-bottom: 20px;
        }
        
        .checkbox label {
            color: #555;
            font-weight: 500;
            cursor: pointer;
        }
        
        .checkbox .icheckbox_square-blue {
            margin-right: 8px;
        }
        
        .btn-login {
            background-color: #2d4281;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
            background-color: #7381AB;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        
        .login-footer p {
            margin: 0;
            color: #95a5a6;
            font-size: 12px;
        }
        
        .login-footer a {
            color: #2d4281;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 12px 15px;
            margin-bottom: 20px;
            animation: shake 0.5s ease;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Loading Spinner */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn-login.loading .btn-text {
            display: none;
        }
        
        .btn-login.loading .spinner {
            display: inline-block;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .login-box {
            animation: fadeInUp 0.8s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-box {
                width: 90%;
                margin: 10% auto;
            }
            
            .login-box-body {
                padding: 30px 25px;
            }
            
            .login-header h4 {
                font-size: 20px;
            }
        }
        
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo text-center">
            <a href="#" style="color: #2d4281;">
                <img src="../uploads/iseasp_logo.png" alt="ISEASP Logo" width="40" height="40" class="d-inline-block align-text-top"> ISEASP
            </a>
            <p>Ilocos Sur Educational Assistance & Scholarship Program</p>
        </div>
        
        <div class="login-box-body">
            <div class="login-header">
                <h4>Welcome!</h4>
                <p>Login to access the scholarship management system</p>
            </div>
            
            <?php check_message(); ?>
            
            <form action="" method="post" id="loginForm">
                <div class="form-group">
                    <i class="fa fa-user input-icon"></i>
                    <input type="text" class="form-control" placeholder="Username" name="user_email" required autofocus>
                </div>
                
                <div class="form-group">
                    <i class="fa fa-lock input-icon"></i>
                    <input type="password" class="form-control" placeholder="Password" name="user_pass" id="password" required>
                </div>
                
                <div class="row">
                    <div class="col-xs-7">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox" name="remember" id="remember"> Remember Me
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-5 text-right">
                        <a href="#" style="color: #2d4281; font-size: 12px;">Forgot Password?</a>
                    </div>
                </div>
                
                <button type="submit" name="btnLogin" class="btn btn-login" id="loginBtn">
                    <span class="spinner"></span>
                    <span class="btn-text"><i class="fa fa-sign-in"></i> Sign In</span>
                </button>
            </form>
            
            <div class="login-footer">
                <p>© <?php echo date('Y'); ?> Provincial Government of Ilocos Sur</p>
                <p><a href="<?php echo web_root; ?>">← Back to ISEASP Website</a></p>
            </div>
        </div>

    </div>

    <?php 
    if(isset($_POST['btnLogin'])){
        $username = trim($_POST['user_email']);
        $password = trim($_POST['user_pass']);
        $hashed_password = sha1($password);
        $remember = isset($_POST['remember']) ? true : false;
        
        if ($username == '' OR $password == '') {
            message("Username and Password are required!", "error");
            redirect("login.php");
        } else {  
            $user = new User();
            $res = $user->userAuthentication($username, $hashed_password);
            
            if ($res == true) { 
                // Check if user is active
                global $mydb;
                $mydb->setQuery("SELECT STATUS FROM tbl_admin WHERE USERID = " . $_SESSION['USERID']);
                $mydb->executeQuery();
                $status = $mydb->loadSingleResult();
                
                if ($status && $status->STATUS == 'Inactive') {
                    message("Your account is inactive. Please contact Super Admin.", "error");
                    session_destroy();
                    redirect(web_root."admin/login.php");
                } else {
                    $_SESSION['ADMIN_USERID'] = $_SESSION['USERID'];
                    $_SESSION['ADMIN_FULLNAME'] = $_SESSION['FULLNAME'];
                    $_SESSION['ADMIN_USERNAME'] = $_SESSION['USERNAME'];
                    $_SESSION['ADMIN_ROLE'] = $_SESSION['ROLE'];
                    $_SESSION['ADMIN_PICLOCATION'] = $_SESSION['PICLOCATION'];
                    
                    // Remember me functionality (30 days)
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (86400 * 30); // 30 days
                        setcookie('iseasp_remember', $token, $expires, '/');
                        // Store token in database (optional)
                    }
                    
                    // Unset temporary session variables
                    unset($_SESSION['USERID']);
                    unset($_SESSION['FULLNAME']);
                    unset($_SESSION['USERNAME']);
                    unset($_SESSION['PASS']);
                    unset($_SESSION['ROLE']);
                    unset($_SESSION['PICLOCATION']);
                    
                    // Log the login action
                    $log_sql = "INSERT INTO tbl_application_log (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS) 
                                VALUES (NULL, " . $_SESSION['ADMIN_USERID'] . ", 
                                        '" . $_SESSION['ADMIN_USERNAME'] . "', 
                                        '" . $_SESSION['ADMIN_ROLE'] . "', 
                                        'Logged in', 'LOGIN', 'User logged in successfully')";
                    $mydb->setQuery($log_sql);
                    $mydb->executeQuery();
                    
                    // Update last login
                    $mydb->setQuery("UPDATE tblusers SET LAST_LOGIN = NOW(), IP_ADDRESS = '" . $_SERVER['REMOTE_ADDR'] . "' WHERE USERID = " . $_SESSION['ADMIN_USERID']);
                    $mydb->executeQuery();
                    
                    // message("Welcome back, " . $_SESSION['ADMIN_FULLNAME'] . "! You are logged in as " . $_SESSION['ADMIN_ROLE'], "success");
                    redirect(web_root."admin/index.php");
                }
            } else {
                message("Invalid username or password! Please try again.", "error");
                redirect(web_root."admin/login.php");
            }
        }
    } 
    ?>

    <!-- jQuery 2.1.4 -->
    <script src="<?php echo web_root;?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="<?php echo web_root;?>bootstrap/js/bootstrap.min.js"></script>
    <!-- iCheck -->
    <script src="<?php echo web_root;?>plugins/iCheck/icheck.min.js"></script>
    
    <script>
        $(function () {
            // iCheck for checkbox
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            
            // Password visibility toggle
            $('.password-toggle').on('click', function() {
                var passwordField = $('#password');
                var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });
            
            // Add password toggle icon
            $('.password-toggle').addClass('fa-eye');
            
            // Form submission loading state
            $('#loginForm').on('submit', function() {
                var btn = $('#loginBtn');
                btn.addClass('loading');
                setTimeout(function() {
                    // Prevent getting stuck if something goes wrong
                    if(btn.hasClass('loading')) {
                        btn.removeClass('loading');
                    }
                }, 5000);
            });
            
            // Animated placeholder effect
            $('.form-control').each(function() {
                $(this).on('focus', function() {
                    $(this).closest('.form-group').addClass('focused');
                }).on('blur', function() {
                    if(!$(this).val()) {
                        $(this).closest('.form-group').removeClass('focused');
                    }
                });
            });
            
            // Add floating label effect
            $('.form-control').each(function() {
                if($(this).val()) {
                    $(this).closest('.form-group').addClass('focused');
                }
            });
            
            // Pre-fill remembered username (optional)
            var rememberedUser = getCookie('iseasp_user');
            if(rememberedUser) {
                $('input[name="user_email"]').val(rememberedUser);
                $('#remember').iCheck('check');
            }
        });
        
        function getCookie(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        }
    </script>
    
    <style>
        /* Additional styles for floating effect */
        .form-group {
            position: relative;
        }
        
        .form-group.focused .input-icon {
            color: #2d4281;
        }
        
        .form-group .form-control:focus + .input-icon {
            color: #2d4281;
        }
        
        .password-toggle {
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #2d4281 !important;
        }
        
        /* Remove number input arrows */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</body>
</html>