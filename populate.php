<?php
// Direct database population script - no auth required for testing
define('DS', DIRECTORY_SEPARATOR);
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT'] . DS . 'ISEASP');
define('LIB_PATH', SITE_ROOT . DS . 'include');

require_once(LIB_PATH . DS . "config.php");
require_once(LIB_PATH . DS . "database.php");

$mydb = new Database();

$admin_id = 1; // Default admin ID
$school_year = date('Y') . '-' . (date('Y') + 1);
$current_semester = (date('m') >= 6) ? '1st Semester' : '2nd Semester';

// Sample Filipino names
$firstnames = [
    'Juan', 'Maria', 'Jose', 'Rosa', 'Pedro', 'Ana', 'Carlos', 'Lucia', 'Miguel', 'Elena',
    'Antonio', 'Sofia', 'Fernando', 'Isabella', 'Diego', 'Carmen', 'Ramon', 'Josephine', 'Manuel', 'Gabriela',
    'Luis', 'Angela', 'Ricardo', 'Mariana', 'Francisco', 'Victoria', 'Raul', 'Teresa', 'Roberto', 'Patricia'
];

$lastnames = [
    'Dela Cruz', 'Santos', 'Guzman', 'Martinez', 'Lopez', 'Garcia', 'Reyes', 'Fernandez', 
    'Villanueva', 'Ramos', 'Aquino', 'Mercado', 'Salazar', 'Tolentino', 'Navarro', 'Soto',
    'Morales', 'Castillo', 'Rivera', 'Montoya', 'Jimenez', 'Ortega', 'Vargas', 'Flores',
    'Cabrera', 'Sanchez', 'Medina', 'Romero', 'Espinoza', 'Gutierrez'
];

$middlenames = [
    'Abad', 'Abella', 'Aca', 'Acane', 'Acob', 'Acuña', 'Adiego', 'Adiós', 'Agosto', 'Agra',
    'Agud', 'Agusta', 'Agun', 'Aguro', 'Aguon', 'Aguste', 'Ahem', 'Ahi', 'Ahong', 'Ahunan'
];

$courses = [
    'BS INFORMATION TECHNOLOGY',
    'BS CIVIL ENGINEERING',
    'BS MECHANICAL ENGINEERING',
    'BA COMMUNICATION',
    'BS NURSING',
    'BS BUSINESS ADMINISTRATION',
    'BS EDUCATION',
    'BA CRIMINOLOGY'
];

$schools = [
    'UNIVERSITY OF NORTHERN PHILIPPINES',
    'ILOCOS SUR COMMUNITY COLLEGE',
    'ILOCOS SUR POLYTECHNIC STATE COLLEGE/UNIVERSITY OF ILOCOS PHILIPPINES',
    'ST. PAUL COLLEGE OF ILOCOS SUR',
    'DIVINE WORLD COLLEGE OF VIGAN'
];

// Load municipalities from the active tbl_municipalities table.
$mydb->setQuery("SELECT MUNICIPALITY_NAME FROM tbl_municipalities WHERE IS_ACTIVE = 'Yes' ORDER BY MUNICIPALITY_NAME");
$municipality_rows = $mydb->loadResultList();
$municipalities = [];
foreach ($municipality_rows as $row) {
    $municipalities[] = $row->MUNICIPALITY_NAME;
}

if (empty($municipalities)) {
    echo "<div class='success'><h3>✗ No active municipalities found in tbl_municipalities.</h3><p>Please add municipalities before running this script.</p></div></body></html>";
    exit;
}

$yearlevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
$districts = ['1st District', '2nd District', '3rd District', '4th District'];
$genders = ['Male', 'Female'];
$civil_statuses = ['Single', 'Married', 'Separated', 'Widowed'];
$application_types = ['First Time Applicant', 'Returning Scholar'];

echo "<!DOCTYPE html>
<html>
<head><title>Population Result</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
.success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; color: #155724; }
.info { margin: 10px 0; font-family: monospace; }
.count { font-size: 24px; font-weight: bold; color: #28a745; }
</style>
</head>
<body>
<h2>Database Population Results</h2>";

$count = 0;
$inserted_ids = [];

for ($i = 0; $i < 30; $i++) {
    $firstname = $firstnames[array_rand($firstnames)];
    $lastname = $lastnames[array_rand($lastnames)];
    $middlename = $middlenames[array_rand($middlenames)];
    $contact = '09' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
    $email = strtolower($firstname . '.' . $lastname . rand(1000, 9999) . '@email.com');
    $course = $courses[array_rand($courses)];
    $school = $schools[array_rand($schools)];
    $yearlevel = $yearlevels[array_rand($yearlevels)];
    $municipality = $municipalities[array_rand($municipalities)];
    $district = $districts[array_rand($districts)];
    $barangay = "Barangay " . rand(1, 30);
    $gpa = number_format(rand(270, 400) / 100, 2);
    $gender = $genders[array_rand($genders)];
    $civil_status = $civil_statuses[array_rand($civil_statuses)];
    $birthdate = date('Y-m-d', strtotime('-' . rand(18, 50) . ' years'));
    $birthplace = $municipalities[array_rand($municipalities)];
    $application_type = $application_types[array_rand($application_types)];
    $family_income = rand(50000, 500000);
    $is_4ps = rand(0, 1);
    $is_indigenous = rand(0, 1);
    $id_number = uniqid("APP") . rand(1000, 9999);
    $lrn = str_pad(rand(1000000000000, 9999999999999), 12, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO tbl_applicants (
                FIRSTNAME, MIDDLENAME, LASTNAME, SUFFIX, LRN, ID_NUMBER, BIRTHDATE, BIRTHPLACE,
                GENDER, CIVIL_STATUS, RELIGION, NATIONALITY,
                PERMANENT_ADDRESS, CURRENT_ADDRESS,
                PERM_STREET, PERM_BARANGAY, PERM_MUNICIPALITY, PERM_PROVINCE,
                CURR_STREET, CURR_BARANGAY, CURR_MUNICIPALITY, CURR_PROVINCE,
                DISTRICT, MUNICIPALITY, BARANGAY,
                COURSE, SCHOOL, YEARLEVEL, GPA, CONTACT, EMAIL,
                FACEBOOK_URL, EMERGENCY_CONTACT_NAME, EMERGENCY_CONTACT_NUMBER, EMERGENCY_CONTACT_RELATION,
                APPLICATION_TYPE, SCHOOL_YEAR, SEMESTER, IS_4PS_BENEFICIARY, IS_INDIGENOUS,
                FAMILY_ANNUAL_INCOME, PARENT_OCCUPATION, STATUS, CREATED_BY, DATECREATED
            ) VALUES (
                '$firstname', '$middlename', '$lastname', '', '$lrn', '$id_number',
                '$birthdate', '$birthplace', '$gender', '$civil_status', 'Catholic', 'Filipino',
                '$municipality, Ilocos Sur, Philippines', '$municipality, Ilocos Sur, Philippines',
                'Sample Street', '$barangay', '$municipality', 'Ilocos Sur',
                'Sample Street', '$barangay', '$municipality', 'Ilocos Sur',
                '$district', '$municipality', '$barangay',
                '$course', '$school', '$yearlevel', $gpa, '$contact', '$email',
                '', '$firstname $lastname', '$contact', 'Parent',
                '$application_type', '$school_year', '$current_semester', '$is_4ps', '$is_indigenous',
                $family_income, 'Self-Employed', 'Pending', $admin_id, NOW()
            )";

    $mydb->setQuery($sql);
    if ($mydb->executeQuery()) {
        $applicant_id = $mydb->insert_id();
        $inserted_ids[] = $applicant_id;
        $count++;

        // INSERT REQUIREMENTS CHECKLIST
        $mydb->setQuery("SELECT REQUIREMENT_ID FROM tbl_requirement ORDER BY REQUIREMENT_ID");
        $all_requirements = $mydb->loadResultList();

        foreach ($all_requirements as $req) {
            $req_id = (int)$req->REQUIREMENT_ID;
            $is_submitted = 1; // All requirements are complete
            
            $check_sql = "INSERT INTO tbl_applicant_requirement_checklist
                            (APPLICANTID, REQUIREMENT_ID, IS_SUBMITTED, IS_VERIFIED, REMARKS)
                          VALUES
                            ($applicant_id, $req_id, $is_submitted, 0, NULL)";
            $mydb->setQuery($check_sql);
            @$mydb->executeQuery();
        }

        // Update applicant requirement status to Complete
        $update_sql = "UPDATE tbl_applicants
                       SET REQUIREMENT_STATUS = 'Complete',
                           REQUIREMENT_DATE = NOW()
                       WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
    }
}

echo "<div class='success'>
<h3>✓ Applicants Successfully Created!</h3>
<p class='count'>$count / 30 applicants have been successfully inserted.</p>
<p class='info'>First 10 IDs: " . implode(", ", array_slice($inserted_ids, 0, 10)) . "</p>";
if (count($inserted_ids) > 10) {
    echo "<p class='info'>... and " . (count($inserted_ids) - 10) . " more applicants</p>";
}
echo "</div>
</body>
</html>";
?>
