<?php  
if (!isset($_SESSION['ADMIN_USERID'])){
    redirect(web_root."admin/index.php");
}

if(!isset($_SESSION['ADMIN_ROLE']) || !in_array($_SESSION['ADMIN_ROLE'], ['Super Admin', 'Admin', 'Evaluator', 'Staff'])){
    redirect(web_root."admin/index.php");
}

@$USERID = $_SESSION['ADMIN_USERID'];
if($USERID==''){
    redirect("index.php");
}

$user = New User();
$singleuser = $user->single_user($USERID);

// If user not found
if(!$singleuser){
    message("User not found!", "error");
    redirect(web_root."admin/index.php");
}
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-user"></i> User Profile</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <!-- Profile Image Section -->
                <div class="col-md-4 text-center">
                    <div class="thumbnail" style="border: none; background: none;">
                        <a data-target="#myModal" data-toggle="modal" href="#" title="Click here to Change Image">
                            <div style="width: 250px; height: 250px; margin: 0 auto; overflow: hidden; border-radius: 50%; border: 5px solid #27ae60; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                                <img alt="Profile Picture" 
                                     style="width: 100%; height: 100%; object-fit: cover;" 
                                     class="img-responsive" 
                                     src="<?php echo web_root . 'admin/user/photos/' . $singleuser->PICLOCATION; ?>" 
                                     onerror="this.src='<?php echo web_root;?>admin/user/default-profile.png'">
                            </div>
                        </a>
                        <h3><strong><?php echo $singleuser->FULLNAME; ?></strong></h3>
                        <p>
                            <span class="label label-<?php 
                                echo ($singleuser->ROLE == 'Super Admin') ? 'danger' : 
                                    (($singleuser->ROLE == 'Admin') ? 'warning' : 
                                    (($singleuser->ROLE == 'Evaluator') ? 'info' : 'success')); 
                            ?>" style="font-size: 14px; padding: 5px 15px;">
                                <i class="fa fa-<?php 
                                    echo ($singleuser->ROLE == 'Super Admin') ? 'shield' : 
                                        (($singleuser->ROLE == 'Admin') ? 'user-md' : 
                                        (($singleuser->ROLE == 'Evaluator') ? 'check-square' : 'user')); 
                                ?>"></i> 
                                <?php echo $singleuser->ROLE; ?>
                            </span>
                        </p>
                        <p><small class="text-muted">Member since: <?php echo date('F d, Y', strtotime($singleuser->DATECREATED)); ?></small></p>
                    </div>
                </div>
                
                <!-- Profile Edit Form Section -->
                <div class="col-md-8">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <i class="fa fa-edit"></i> Edit Profile Information
                        </div>
                        <div class="panel-body">
                            <form id="profileForm" class="form-horizontal" action="controller.php?action=edit" method="POST">
                                
                                <input id="USERID" name="USERID" type="hidden" value="<?php echo $singleuser->USERID; ?>">
                                
                                <!-- Full Name -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="U_NAME">Full Name:</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input class="form-control input-sm" id="U_NAME" name="FULLNAME" 
                                                   placeholder="Full Name" type="text" 
                                                   value="<?php echo htmlspecialchars($singleuser->FULLNAME); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Username -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="U_USERNAME">Username:</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input class="form-control input-sm" id="U_USERNAME" name="USERNAME" 
                                                   placeholder="Username" type="text" 
                                                   value="<?php echo htmlspecialchars($singleuser->USERNAME); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- New Password (Optional) -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="U_PASS">New Password:</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                            <input class="form-control input-sm" id="U_PASS" name="PASS" 
                                                   placeholder="Leave blank to keep current password" 
                                                   type="password">
                                        </div>
                                        <small class="text-muted">Minimum 8 characters</small>
                                    </div>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="CONFIRM_PASS">Confirm Password:</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                            <input class="form-control input-sm" id="CONFIRM_PASS" name="CONFIRM_PASS" 
                                                   placeholder="Confirm new password" 
                                                   type="password">
                                        </div>
                                    </div>
                                </div>

                                <!-- Role (Read-only for non-Super Admin) -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="U_ROLE">Role:</label>
                                    <div class="col-md-8">
                                        <?php if($_SESSION['ADMIN_ROLE'] == 'Super Admin'): ?>
                                            <select class="form-control input-sm" name="ROLE" id="U_ROLE">
                                                <option value="Super Admin" <?php echo ($singleuser->ROLE=='Super Admin') ? 'selected' : ''; ?>>Super Admin</option>
                                                <option value="Admin" <?php echo ($singleuser->ROLE=='Admin') ? 'selected' : ''; ?>>Admin</option>
                                                <option value="Evaluator" <?php echo ($singleuser->ROLE=='Evaluator') ? 'selected' : ''; ?>>Evaluator</option>
                                                <option value="Staff" <?php echo ($singleuser->ROLE=='Staff') ? 'selected' : ''; ?>>Staff</option>
                                            </select>
                                        <?php else: ?>
                                            <input class="form-control input-sm" type="text" 
                                                   value="<?php echo $singleuser->ROLE; ?>" readonly disabled>
                                            <small class="text-muted">Only Super Admin can change roles</small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Account Status (Only for Super Admin viewing others) -->
                                <?php if($_SESSION['ADMIN_ROLE'] == 'Super Admin' && $singleuser->USERID != $_SESSION['ADMIN_USERID']): ?>
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="STATUS">Account Status:</label>
                                    <div class="col-md-8">
                                        <?php
                                        // Get status from tbl_admin
                                        global $mydb;
                                        $mydb->setQuery("SELECT STATUS FROM tbl_admin WHERE USERID = ".$singleuser->USERID);
                                        $mydb->executeQuery();
                                        $status_result = $mydb->loadSingleResult();
                                        $current_status = $status_result ? $status_result->STATUS : 'Active';
                                        ?>
                                        <select class="form-control input-sm" name="STATUS" id="STATUS">
                                            <option value="Active" <?php echo ($current_status=='Active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="Inactive" <?php echo ($current_status=='Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Email (Optional - Add if you have email field) -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="EMAIL">Email:</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                            <input class="form-control input-sm" id="EMAIL" name="EMAIL" 
                                                   placeholder="Email Address" type="email" 
                                                   value="<?php echo isset($singleuser->EMAIL) ? htmlspecialchars($singleuser->EMAIL) : ''; ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Last Login Info (Read-only) -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Last Login:</label>
                                    <div class="col-md-8">
                                        <p class="form-control-static">
                                            <?php echo isset($singleuser->LAST_LOGIN) ? date('F d, Y h:i A', strtotime($singleuser->LAST_LOGIN)) : 'First time login'; ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Save Button -->
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-3">
                                        <button class="btn btn-primary" name="save" type="submit">
                                            <i class="fa fa-save"></i> Update Profile
                                        </button>
                                        <!-- <a href="index.php" class="btn btn-default">
                                            <i class="fa fa-arrow-left"></i> All User
                                        </a> -->
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Image Upload -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #27ae60; color: white;">
                <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-image"></i> Change Profile Picture</h4>
            </div>

            <form action="controller.php?action=photos" enctype="multipart/form-data" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="photo">Choose Image:</label>
                        <input type="file" id="photo" name="photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Allowed formats: JPG, PNG, GIF, WEBP. Max size: 2MB</small>
                        
                        <!-- Preview -->
                        <div id="imagePreview" style="margin-top: 10px; display: none;">
                            <p>Preview:</p>
                            <img src="#" alt="Preview" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                        </div>
                    </div>
                    
                    <!-- Current Image -->
                    <div class="form-group">
                        <label>Current Image:</label>
                        <div>
                            <img src="<?php echo web_root.'admin/user/photos/'. $singleuser->PICLOCATION;?>" 
                                 alt="Current" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px;"
                                 onerror="this.src='<?php echo web_root;?>admin/user/photos/default-profile.png'">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" name="savephoto" class="btn btn-primary">
                        <i class="fa fa-upload"></i> Upload Photo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Image preview before upload
document.getElementById('photo').addEventListener('change', function(e) {
    var preview = document.getElementById('imagePreview');
    var file = e.target.files[0];
    var reader = new FileReader();
    
    reader.onload = function(e) {
        preview.style.display = 'block';
        preview.querySelector('img').src = e.target.result;
    }
    
    if (file) {
        reader.readAsDataURL(file);
    }
});

// Password match validation (optional)
document.getElementById('profileForm').addEventListener('submit', function(e) {
    var pass = document.getElementById('U_PASS').value;
    var confirm = document.getElementById('CONFIRM_PASS').value;

    if (pass !== '' && pass !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
    } else if (pass !== '' && pass.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long!');
    }
});
</script>

<style>
/* Additional styling */
.thumbnail {
    background: transparent;
    border: none;
}
.form-control-static {
    padding-top: 7px;
    margin-bottom: 0;
}
.input-group-addon {
    background-color: #27ae60;
    color: black;
    border: 1px solid #27ae60;
}
.btn-primary {
    background-color: #27ae60;
    border-color: #229954;
}
.btn-primary:hover {
    background-color: #229954;
    border-color: #1e8449;
}
.panel-primary > .panel-heading {
    background-color: #27ae60;
    border-color: #27ae60;
}
.panel-info > .panel-heading {
    background-color: #d9edf7;
    border-color: #bce8f1;
    color: #31708f;
}
</style>