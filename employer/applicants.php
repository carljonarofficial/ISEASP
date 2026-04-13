<?php
require_once("./include/initialize.php");

if(!isset($_SESSION['EMPLOYER_ID'])){
    redirect(web_root."employer/login.php");
}

$employer_id = $_SESSION['EMPLOYER_ID'];

// Fetch applicants that applied to the current employer's job postings
$sql = "SELECT DISTINCT a.`APPLICANTID`, a.`FNAME`, a.`LNAME`, a.`MNAME`, a.`ADDRESS`, a.`SEX`, a.`CIVILSTATUS`, 
               a.`BIRTHDATE`, a.`BIRTHPLACE`, a.`AGE`, a.`USERNAME`, a.`EMAILADDRESS`, a.`CONTACTNO`, 
               a.`DEGREE`, a.`APPLICANTPHOTO`, a.`NATIONALID`
        FROM tblapplicants a
        JOIN tbljobregistration jr ON a.`APPLICANTID` = jr.`APPLICANTID`
        JOIN tbljob j ON jr.`JOBID` = j.`JOBID`
        WHERE j.`COMPANYID` = '$employer_id' and jr.`IS_ACCEPTED` = 'no'";

$result = $conn->query($sql);

// Fetch degrees for filtering
$degrees = $conn->query("SELECT DISTINCT DEGREE FROM tblapplicants WHERE DEGREE != ''");

?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container">
  <h2 class="mb-4">List of Applicants</h2>

  <!-- Degree Filter -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label for="degreeFilter" class="form-label">Filter by Degree</label>
      <select id="degreeFilter" class="form-select">
        <option value="">All Degrees</option>
        <?php while($deg = $degrees->fetch_assoc()): ?>
          <option value="<?php echo htmlspecialchars($deg['DEGREE']); ?>">
            <?php echo htmlspecialchars($deg['DEGREE']); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
  </div>

  <!-- Applicants Table -->
  <div class="table-responsive">
    <table id="applicantsTable" class="table table-striped table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Photo</th>
          <th>Full Name</th>
          <th>Degree</th>
          <th>Age</th>
          <th>Gender</th>
          <th>Status</th>
          <th>Contact</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php $i = 1; while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td>
                <img src="<?php echo '../applicant/' . $row['APPLICANTPHOTO']; ?>"
                     alt="Photo" width="50" height="50" class="rounded-circle">
              </td>
              <td><?php echo $row['FNAME'] . ' ' . $row['MNAME'] . ' ' . $row['LNAME']; ?></td>
              <td><?php echo $row['DEGREE']; ?></td>
              <td><?php echo $row['AGE']; ?></td>
              <td><?php echo $row['SEX']; ?></td>
              <td><?php echo $row['CIVILSTATUS']; ?></td>
              <td><?php echo $row['CONTACTNO']; ?></td>
              <td><?php echo $row['EMAILADDRESS']; ?></td>
              <td>
				<a href="index.php?view=view_applicant&id=<?php echo $row['APPLICANTID']; ?>" class="btn btn-sm btn-info" title="View">
					<i class="bi bi-eye"></i>
				</a>
				<button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $row['APPLICANTID']; ?>, 'Accepted')" title="Accept">
					✅
				</button>
				<button class="btn btn-sm btn-danger" onclick="updateStatus(<?php echo $row['APPLICANTID']; ?>, 'Rejected')" title="Reject">
					❌
				</button>
			</td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="10" class="text-center">No applicants found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  $(document).ready(function () {
    const table = $('#applicantsTable').DataTable({
      "pageLength": 10,
      "order": [[0, "asc"]]
    });

    // Filter by Degree
    $('#degreeFilter').on('change', function () {
      const degree = $(this).val();
      table.column(3).search(degree).draw(); // Column index 3 = Degree
    });
  });

  function updateStatus(applicantId, status) {
    if (!confirm(Are you sure you want to mark this applicant as ${status}?)) return;

    $.ajax({
      url: 'update_applicant_status.php',
      method: 'POST',
      data: {
        applicant_id: applicantId,
        status: status
      },
      success: function (response) {
        alert('Status updated successfully.');
        location.reload(); // Reload to reflect changes
      },
      error: function () {
        alert('Error updating status.');
      }
    });
  }
</script>
