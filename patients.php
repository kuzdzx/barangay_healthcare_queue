<?php
// ==========================================
// 1. DATABASE CONNECTION
// ==========================================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "barangay_health_queue";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ==========================================
// 2. HANDLE DELETE
// ==========================================
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $result = mysqli_query($conn, "DELETE FROM patients WHERE patient_id = $id");

    if ($result) {
        header("Location: patients.php");
        exit;
    } else {
        echo "<script>
                alert('Cannot delete this patient because they are linked to a queue ticket or consultation.');
                window.location.href = 'patients.php';
              </script>";
        exit;
    }
}

// ==========================================
// 3. HANDLE FORM SUBMISSION (used for both ADD and EDIT)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $sex        = mysqli_real_escape_string($conn, $_POST['sex']);
    $address    = mysqli_real_escape_string($conn, $_POST['address']);
    $contact    = mysqli_real_escape_string($conn, $_POST['contact']);

    if (!empty($_POST['patient_id'])) {

        $id = (int) $_POST['patient_id'];

        $sql = "UPDATE patients SET
                    first_name = '$first_name',
                    last_name  = '$last_name',
                    birth_date = '$birth_date',
                    sex        = '$sex',
                    address    = '$address',
                    contact    = '$contact'
                WHERE patient_id = $id";
        mysqli_query($conn, $sql);

    } else {

        $sql = "INSERT INTO patients (first_name, last_name, birth_date, sex, address, contact)
                VALUES ('$first_name', '$last_name', '$birth_date', '$sex', '$address', '$contact')";
        mysqli_query($conn, $sql);
    }

    header("Location: patients.php");
    exit;
}

// ==========================================
// 4. PREPARE THE FORM (fills the boxes when editing)
// ==========================================

$action = "view";
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

$edit_id = "";
$edit_first_name = "";
$edit_last_name = "";
$edit_birth_date = "";
$edit_sex = "";
$edit_address = "";
$edit_contact = "";

if ($action == 'edit' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $result = mysqli_query($conn, "SELECT * FROM patients WHERE patient_id = $id");
    $record = mysqli_fetch_assoc($result);

    if ($record) {
        $edit_id         = $record['patient_id'];
        $edit_first_name = $record['first_name'];
        $edit_last_name  = $record['last_name'];
        $edit_birth_date = $record['birth_date'];
        $edit_sex        = $record['sex'];
        $edit_address    = $record['address'];
        $edit_contact    = $record['contact'];
    }
}

// ==========================================
// 5. GET ALL PATIENTS FOR THE LIST
// ==========================================
$patients = mysqli_query($conn, "SELECT * FROM patients ORDER BY patient_id DESC");

// ==========================================
// 6. SET UP THE SELECTED OPTIONS FOR THE SEX DROPDOWN
// ==========================================
$select_none = "";
$select_male = "";
$select_female = "";

if ($edit_sex == "Male") {
    $select_male = "selected";
} elseif ($edit_sex == "Female") {
    $select_female = "selected";
} else {
    $select_none = "selected";
}

$form_title = "Register New Patient";
$button_text = "Save Patient Data";
if ($action == 'edit') {
    $form_title = "Edit Patient Record";
    $button_text = "Update Patient Data";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patients - Panacea Health Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR -->
            <div class="col-md-2 sidebar position-fixed">
                <div>
                    <!-- Barangay Health Center Logo Header - Community Home -->
<div class="d-flex align-items-center px-2 mb-4 pb-3 border-bottom">
    <svg class="me-2" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0d6efd" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <path d="M12 9v6M9 12h6"/>
    </svg>
    <span class="fs-5 fw-bold text-dark" style="letter-spacing: -0.5px;">BHS Center</span>
</div>

                    <div class="nav flex-column nav-pills">
    <!-- Dashboard -->
    <a href="index.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/></svg>
        Dashboard
    </a>
    <!-- Patients -->
    <a href="patients.php" class="nav-link active d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5-4-8-4z"/></svg>
        Patients
    </a>
    <!-- Clinical Staff -->
    <a href="staff.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm6 12H6v-1c0-2 4-3.1 6-3.1s6 1.1 6 3.1v1z"/></svg>
        Clinical Staff
    </a>
    <!-- Services -->
    <a href="services.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        Services
    </a>
    <!-- Queue Tickets -->
    <a href="queue_tickets.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.1 0-1.99.89-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-1.99 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-9 7.5h-2v-2h2v2zm0-4.5h-2v-2h2v2zm0-4.5h-2v-2h2v2z"/></svg>
        Queue Tickets
    </a>
    <!-- Consultations (Active here!) -->
    <a href="consultations.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
        Consultations
    </a>
    <!-- Medicines -->
    <a href="medicines.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 3h12v2H6zm11.5 5c.83 0 1.5.67 1.5 1.5v10c0 .83-.67 1.5-1.5 1.5h-11C5.67 21 5 20.33 5 19.5v-10C5 8.67 5.67 8 6.5 8h11zm-4 3h-3v2.5H7v3h3.5V19h3v-2.5H17v-3h-3.5V11z"/></svg>
        Medicines
    </a>
</div>

                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-10 offset-md-2 main-content">

                <div class="topbar">
                    <div class="active-tab">/ Patient Directory</div>
                    <div>/ Add & Edit Records</div>
                </div>

                <div class="content-pad">

                    <?php if ($action == 'add' || $action == 'edit') { ?>

                        <!-- ADD / EDIT FORM -->
                        <div class="content-card mx-auto" style="max-width: 700px;">
                            <h4 class="fw-bold mb-4"><?php echo $form_title; ?></h4>

                            <form method="POST" action="patients.php">

                                <input type="hidden" name="patient_id" value="<?php echo $edit_id; ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($edit_first_name); ?>" required placeholder="e.g. Juanito">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($edit_last_name); ?>" required placeholder="e.g. Santos">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Birth Date</label>
                                        <input type="date" name="birth_date" class="form-control" value="<?php echo $edit_birth_date; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sex</label>
                                        <select name="sex" class="form-select" required>
                                            <option value="" disabled <?php echo $select_none; ?>>Select Sex</option>
                                            <option value="Male" <?php echo $select_male; ?>>Male</option>
                                            <option value="Female" <?php echo $select_female; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Home Address</label>
                                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($edit_address); ?>" required placeholder="Full address">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($edit_contact); ?>" placeholder="e.g. 09000000000">
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark w-100"><?php echo $button_text; ?></button>
                                    <a href="patients.php" class="btn btn-light w-100" style="border: 1px solid #edf2f9;">Cancel</a>
                                </div>
                            </form>
                        </div>

                    <?php } else { ?>

                        <!-- PATIENT LIST -->
                        <div class="content-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="m-0 fw-bold">Patient Directory</h4>
                                <a href="patients.php?action=add" class="btn btn-dark rounded-pill px-4">+ Add Patient</a>
                            </div>

                            <table class="table table-borderless table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient Name</th>
                                        <th>Sex</th>
                                        <th>Birth Date</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (mysqli_num_rows($patients) == 0) { ?>

                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No patients found in the system.</td>
                                        </tr>

                                    <?php } else { ?>

                                        <?php while ($p = mysqli_fetch_assoc($patients)) { ?>
                                            <tr>
                                                <td class="text-muted">#<?php echo $p['patient_id']; ?></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                                                <td><?php echo $p['sex']; ?></td>
                                                <td><?php echo $p['birth_date']; ?></td>
                                                <td><?php echo htmlspecialchars($p['contact']); ?></td>
                                                <td>
                                                    <a href="patients.php?action=edit&id=<?php echo $p['patient_id']; ?>" class="btn btn-sm btn-light border text-primary">Edit</a>
                                                    <a href="patients.php?delete=<?php echo $p['patient_id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this patient?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                    <?php } ?>

                                </tbody>
                            </table>
                        </div>

                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
</body>
</html>