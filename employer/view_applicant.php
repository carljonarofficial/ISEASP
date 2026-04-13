<?php
require_once("./include/initialize.php");

if (!isset($_SESSION['EMPLOYER_ID'])) {
    redirect(web_root . "employer/login.php");
}

if (!isset($_GET['id'])) {
    redirect("index.php"); // or show an error
}

$applicantId = $_GET['id'];


$sql = "SELECT a.*, f.*, jr.* 
        FROM tblapplicants a
        JOIN tblattachmentfile f ON a.APPLICANTID = f.USERATTACHMENTID
        JOIN tbljobregistration jr ON a.APPLICANTID = jr.APPLICANTID
        WHERE a.APPLICANTID = '$applicantId'";


$result = $conn->query($sql);

if ($result->num_rows != 1) {
    echo "<div class='container mt-5 alert alert-danger'>Applicant not found.</div>";
    exit;
}


$row = $result->fetch_assoc();
?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">📄 Applicant Profile</h2>
    <?php if (!empty($row['FILE_LOCATION'])): ?>
      <a href="<?php echo '../applicant/' . $row['FILE_LOCATION']; ?>" target="_blank" class="btn btn-outline-primary mt-3" download>
        Download Resume
      </a>
    <?php else: ?>
      <div class="mt-3 text-muted">No resume uploaded.</div>
    <?php endif; ?>
  </div>

  <!-- Profile Card -->
  <div class="card shadow-lg border-0 rounded-4 mb-4 p-4" id="profile">
    <div class="row g-4">
      <div class="col-md-3 text-center">
        <img src="<?php echo '../applicant/' . $row['APPLICANTPHOTO']; ?>" class="rounded-circle shadow" alt="Applicant Photo" width="160" height="160">
      </div>
      <div class="col-md-9">
        <h4 class="mb-3"><?php echo $row['FNAME'] . ' ' . $row['MNAME'] . ' ' . $row['LNAME']; ?></h4>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Degree:</strong> <?php echo $row['DEGREE']; ?></p>
            <p><strong>National ID:</strong> <?php echo $row['NATIONALID']; ?></p>
            <p><strong>Email:</strong> <?php echo $row['EMAILADDRESS']; ?></p>
            <p><strong>Contact:</strong> <?php echo $row['CONTACTNO']; ?></p>
            <p><strong>Address:</strong> <?php echo $row['ADDRESS']; ?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Gender:</strong> <?php echo $row['SEX']; ?></p>
            <p><strong>Civil Status:</strong> <?php echo $row['CIVILSTATUS']; ?></p>
            <p><strong>Birthdate:</strong> <?php echo $row['BIRTHDATE']; ?></p>
            <p><strong>Birthplace:</strong> <?php echo $row['BIRTHPLACE']; ?></p>
            <p><strong>Age:</strong> <?php echo $row['AGE']; ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Gmail Button -->
  <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
    <h5>Contact Applicant via Gmail</h5>
    <p>Click the button below to compose an email to the applicant using your Gmail account.</p>

    <?php
      $to = $row['EMAILADDRESS'];
      $subject = "Regarding Your Job Application";
      $companyName = isset($_SESSION['EMPLOYER_NAME']) ? $_SESSION['EMPLOYER_NAME'] : "Our Company";

      $body = "Hi " . $row['FNAME'] . ",%0D%0A%0D%0A"
            . "We have reviewed your application and would like to get in touch with you.%0D%0A%0D%0A"
            . "Best regards,%0D%0A" . $companyName;

      $gmailUrl = "https://mail.google.com/mail/?view=cm&fs=1&to={$to}&su=" . urlencode($subject) . "&body=" . $body;
    ?>

    <a href="#" 
      onclick="window.open('<?php echo $gmailUrl; ?>', '_blank'); return false;" 
      class="btn btn-danger mt-2">
      ✉ Compose in Gmail
    </a>
  </div>

  <button onclick="history.back()" class="btn btn-outline-secondary">← Go Back</button>
</div>

<script>
  // PDF Download
  document.getElementById("downloadPdf").addEventListener("click", function () {
    const element = document.getElementById("profile");
    html2pdf().from(element).set({
      margin: 0.5,
      filename: 'Applicant_Profile_<?php echo $applicantId; ?>.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    }).save();
  });

  // Submit Feedback via AJAX
  $('#submitFeedback').on('click', function () {
    const feedback = $('#feedback').val().trim();
    if (feedback === '') {
      $('#feedbackStatus').html('<div class="text-danger">Feedback cannot be empty.</div>');
      return;
    }

    $.ajax({
      url: 'submit_feedback.php',
      method: 'POST',
      data: {
        applicant_id: '<?php echo $applicantId; ?>',
        employer_id: '<?php echo $_SESSION['EMPLOYER_ID']; ?>',
        feedback: feedback
      },
      success: function () {
        $('#feedbackStatus').html('<div class="text-success">Feedback submitted!</div>');
        $('#feedback').val('');
        loadFeedback();
      },
      error: function () {
        $('#feedbackStatus').html('<div class="text-danger">Submission failed. Try again.</div>');
      }
    });
  });

  // Load feedback entries
  function loadFeedback() {
    $.get('fetch_feedback.php', { applicant_id: '<?php echo $applicantId; ?>' }, function (data) {
      $('#feedbackItems').html(data);
    });
  }

  // Initial load
  loadFeedback();
</script>

