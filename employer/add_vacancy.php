<?php
require_once("./include/initialize.php");

if (!isset($_SESSION['EMPLOYER_ID'])) {
  redirect(web_root . 'employer/login.php');
}

$employerID = $_SESSION['EMPLOYER_ID'];
?>

<div class="container py-4">
  <h3 class="mb-4 text-primary"><i class="bi bi-plus-circle me-2"></i>Add New Vacancy</h3>

  <form action="process_add_vacancy.php" method="POST">
    <input type="hidden" name="company_id" value="<?php echo $employerID; ?>">

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Occupation Title</label>
        <input type="text" name="occupation_title" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" required>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Required No. of Employees</label>
        <input type="number" name="required_employees" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Salary (₱)</label>
        <input type="number" name="salaries" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Duration of Employment</label>
        <input type="text" name="duration_employment" class="form-control" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Qualification / Work Experience</label>
      <textarea name="qualification" rows="2" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Job Description</label>
      <textarea name="job_description" rows="3" class="form-control" required></textarea>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Preferred Gender</label>
        <select name="preferred_sex" class="form-select">
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Any" selected>Any</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Sector of Vacancy</label>
        <input type="text" name="sector" class="form-control" required>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Date Posted</label>
        <!-- <input type="date" name="date_posted" class="form-control" value="<?php echo date('Y-m-d'); ?>" required> -->
        <input type="date" name="date_posted" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>

      </div>
      <div class="col-md-6">
        <label class="form-label">Closing Date</label>
        <input type="date" name="closing_date" class="form-control" required>
      </div>
    </div>

    <input type="hidden" name="job_status" value="Open">

    <button type="submit" name="saveVacancy" class="btn btn-success mt-3">
      <i class="bi bi-save2 me-1"></i> Save Vacancy
    </button>
  </form>
</div>

<script>
  document.querySelector("form").addEventListener("submit", function (e) {
    const datePosted = new Date(document.querySelector('input[name="date_posted"]').value);
    const closingDate = new Date(document.querySelector('input[name="closing_date"]').value);

    if (closingDate <= datePosted) {
      alert("Closing date must be later than the date posted.");
      e.preventDefault(); // Prevent form submission
    }
  });
</script>

