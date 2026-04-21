<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

// Handle Actions
if (isset($_POST['save_sy'])) {
    $sy = addslashes(trim($_POST['school_year']));
    $start = addslashes($_POST['start_date']);
    $end = addslashes($_POST['end_date']);
    
    if($sy == "" || $start == "" || $end == ""){
        message("All fields are required!", "error");
    } else {
        $sql = "INSERT INTO tbl_school_years (school_year, start_date, end_date, is_active) 
                VALUES ('$sy', '$start', '$end', 0)";
        $mydb->setQuery($sql);
        if($mydb->executeQuery()){
            message("New school year added successfully!", "success");
        } else {
            message("Error adding school year.", "error");
        }
    }
    redirect("index.php?view=school_years");
}

if (isset($_GET['action'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'delete') {
        $mydb->setQuery("DELETE FROM tbl_school_years WHERE id = $id");
        $mydb->executeQuery();
        message("School year deleted.", "success");
    } elseif ($_GET['action'] == 'activate') {
        // Deactivate all first to ensure only one is active
        $mydb->setQuery("UPDATE tbl_school_years SET is_active = 0");
        $mydb->executeQuery();
        // Activate selected
        $mydb->setQuery("UPDATE tbl_school_years SET is_active = 1 WHERE id = $id");
        $mydb->executeQuery();
        message("School year set as active.", "success");
    }
    redirect("index.php?view=school_years");
}

$mydb->setQuery("SELECT id, school_year, start_date, end_date, is_active, created_at FROM tbl_school_years ORDER BY id DESC");
$cur = $mydb->loadResultList();
?>

<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Add School Year</h3>
            </div>
            <form action="" method="POST">
                <div class="box-body">
                    <div class="form-group">
                        <label>School Year (e.g., 2024-2025)</label>
                        <input type="text" name="school_year" class="form-control" placeholder="YYYY-YYYY" required>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="save_sy" class="btn btn-primary">Save School Year</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">List of School Years</h3>
            </div>
            <div class="box-body">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>School Year</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cur as $result) : ?>
                            <tr>
                                <td><?php echo $result->school_year; ?></td>
                                <td><?php echo date("M d, Y", strtotime($result->start_date)) . " - " . date("M d, Y", strtotime($result->end_date)); ?></td>
                                <td>
                                    <?php echo ($result->is_active == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($result->is_active == 0) : ?>
                                        <a title="Set as Active" href="index.php?view=school_years&action=activate&id=<?php echo $result->id; ?>" class="btn btn-xs btn-success"><i class="fa fa-check"></i></a>
                                    <?php endif; ?>
                                    <a title="Delete" href="index.php?view=school_years&action=delete&id=<?php echo $result->id; ?>" class="btn btn-xs btn-danger delete-confirm"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>