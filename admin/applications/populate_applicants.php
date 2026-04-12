<?php
require_once("../../include/initialize.php");

if (!isset($_SESSION['ADMIN_USERID'])) {
    echo "Not logged in. <a href='../../admin/login.php'>Login here</a>";
    exit;
}

global $mydb;

$admin_id = $_SESSION['ADMIN_USERID'];
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

$municipalities = [
    'Vigan City', 'Caoayan', 'Candon City', 'Santa Lucia', 'Santo Domingo', 'Bantay',
    'Bauan', 'Cabugao', 'Catubig', 'Guiguinto', 'Guimbal', 'Imus', 'Kalibo'
];

$municipalities2 = [
    'Kawit', 'Legaspi City', 'Marikina', 'Nagtanyan', 'Pateros', 'Quezon City', 'San Juan',
    'Santa Ana', 'Sapang Palay', 'Silang', 'Solis', 'Subic', 'Tagaytay'
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
    'UNP (University of Northern Philippines)',
    'PUP (Polytechnic University of the Philippines)',
    'UP (University of the Philippines)',
    'DLSU (De La Salle University)',
    'USLS (University of Santo Tomas)',
    'TUAT (Technological University of the Philippines)',
    'CLSU (Central Luzon State University)'
];

$yearlevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

$districts = ['1st District', '2nd District', '3rd District', '4th District'];

$genders = ['Male', 'Female'];

$civil_statuses = ['Single', 'Married', 'Separated', 'Widowed'];

$application_types = ['First Time Applicant', 'Returning Scholar'];

echo "<h2>Populating 30 New Applicants</h2>";
echo "<p>Processing...</p>";

$count = 0;
$inserted_ids = [];

for ($i = 0; $i < 30; $i++) {
    $firstname = $firstnames[array_rand($firstnames)];
    $lastname = $lastnames[array_rand($lastnames)];
    $middlename = $middlenames[array_rand($middlenames)];
    $contact = '09' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
    $email = strtolower($firstname . '.' . $lastname . '@email.com');
    $course = $courses[array_rand($courses)];
    $school = $schools[array_rand($schools)];
    $yearlevel = $yearlevels[array_rand($yearlevels)];
    $municipality = $municipalities[array_rand(array_merge($municipalities, $municipalities2))];
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
                '$firstname', '$middlename', '$lastname', '', '', '',
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
            $is_submitted = rand(0, 1);
            
            $check_sql = "INSERT INTO tbl_applicant_requirement_checklist
                            (APPLICANTID, REQUIREMENT_ID, IS_SUBMITTED, IS_VERIFIED, REMARKS)
                          VALUES
                            ($applicant_id, $req_id, $is_submitted, 0, NULL)";
            $mydb->setQuery($check_sql);
            @$mydb->executeQuery();
        }
    } else {
        echo "<span style='color:red'>✗ Failed to insert applicant " . ($i + 1) . "</span><br>";
    }
}

echo "<div style='margin-top: 20px; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;'>";
echo "<h3 style='color: #155724; margin-top: 0;'>✓ Success!</h3>";
echo "<p><strong>$count applicants have been successfully created.</strong></p>";
echo "<p><strong>Inserted Applicant IDs:</strong><br>";
echo implode(", ", array_slice($inserted_ids, 0, 10));
if (count($inserted_ids) > 10) {
    echo "<br>... and " . (count($inserted_ids) - 10) . " more";
}
echo "</p>";
echo "<p><a href='list.php' class='btn btn-primary'>View All Applicants</a></p>";
echo "</div>";
?>
