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

    $result = mysqli_query($conn, "DELETE FROM consultations WHERE consultation_id = $id");

    if ($result) {
        header("Location: consultations.php");
        exit;
    } else {
        echo "<script>
                alert('Cannot delete this consultation record due to database constraints.');
                window.location.href = 'consultations.php';
              </script>";
        exit;
    }
}

// ==========================================
// 3. HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $ticket_id = (int) $_POST['ticket_id'];
    $staff_id  = (int) $_POST['staff_id'];

    $consult_time = str_replace('T', ' ', $_POST['consult_time']);
    $consult_time = mysqli_real_escape_string($conn, $consult_time);

    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $notes     = mysqli_real_escape_string($conn, $_POST['notes']);

    if (!empty($_POST['consultation_id'])) {

        $id = (int) $_POST['consultation_id'];

        $sql = "UPDATE consultations SET
                    ticket_id    = $ticket_id,
                    staff_id     = $staff_id,
                    consult_time = '$consult_time',
                    diagnosis    = '$diagnosis',
                    notes        = '$notes'
                WHERE consultation_id = $id";
        mysqli_query($conn, $sql);

    } else {

        $sql = "INSERT INTO consultations (ticket_id, staff_id, consult_time, diagnosis, notes)
                VALUES ($ticket_id, $staff_id, '$consult_time', '$diagnosis', '$notes')";
        mysqli_query($conn, $sql);
    }

    header("Location: consultations.php");
    exit;
}

// ==========================================
// 4. PREPARE THE FORM
// ==========================================
$action = "view";
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

$edit_id = "";
$edit_ticket = "";
$edit_staff = "";
$edit_time = "";
$edit_diagnosis = "";
$edit_notes = "";

if ($action == 'edit' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $result = mysqli_query($conn, "SELECT * FROM consultations WHERE consultation_id = $id");
    $record = mysqli_fetch_assoc($result);

    if ($record) {
        $edit_id        = $record['consultation_id'];
        $edit_ticket    = $record['ticket_id'];
        $edit_staff     = $record['staff_id'];
        $edit_time      = date('Y-m-d\TH:i', strtotime($record['consult_time']));
        $edit_diagnosis = $record['diagnosis'];
        $edit_notes     = $record['notes'];
    }
}

if ($edit_time == "") {
    $edit_time = date('Y-m-d\TH:i');
}

$form_title = "Record New Consultation";
$button_text = "Save Consultation";
if ($action == 'edit') {
    $form_title = "Edit Consultation Record";
    $button_text = "Update Record";
}

// ==========================================
// 5. GET DATA FOR THE DROPDOWNS
// ==========================================
$tickets = mysqli_query($conn, "SELECT q.ticket_id, q.queue_number, p.first_name, p.last_name
                                FROM queue_tickets q
                                JOIN patients p ON q.patient_id = p.patient_id
                                ORDER BY q.queue_date DESC, q.queue_number ASC");

$staff_list = mysqli_query($conn, "SELECT staff_id, first_name, last_name, role FROM staff ORDER BY last_name ASC");

// ==========================================
// 6. GET ALL CONSULTATIONS FOR THE LIST
// ==========================================
$sql = "SELECT c.consultation_id, c.consult_time, c.diagnosis,
               p.first_name AS p_first, p.last_name AS p_last,
               s.first_name AS s_first, s.last_name AS s_last
        FROM consultations c
        JOIN queue_tickets q ON c.ticket_id = q.ticket_id
        JOIN patients p ON q.patient_id = p.patient_id
        JOIN staff s ON c.staff_id = s.staff_id
        ORDER BY c.consult_time DESC";

$consults = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultations - Panacea Health Center</title>
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
    <a href="patients.php" class="nav-link d-flex align-items-center">
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
    <a href="consultations.php" class="nav-link active d-flex align-items-center">
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
                    <div class="active-tab">/ Medical Records</div>
                    <div>/ Patient Consultations</div>
                </div>

                <div class="content-pad">

                    <?php if ($action == 'add' || $action == 'edit') { ?>

                        <!-- ADD / EDIT FORM -->
                        <div class="content-card mx-auto" style="max-width: 700px;">
                            <h4 class="fw-bold mb-4"><?php echo $form_title; ?></h4>

                            <form method="POST" action="consultations.php">
                                <input type="hidden" name="consultation_id" value="<?php echo $edit_id; ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Active Queue Ticket</label>
                                        <select name="ticket_id" class="form-select" required>
                                            <option value="">-- Choose Queue Ticket --</option>

                                            <?php while ($t = mysqli_fetch_assoc($tickets)) { ?>

                                                <?php
                                                $selected = "";
                                                if ($edit_ticket == $t['ticket_id']) {
                                                    $selected = "selected";
                                                }
                                                ?>

                                                <option value="<?php echo $t['ticket_id']; ?>" <?php echo $selected; ?>>
                                                    Ticket #<?php echo $t['queue_number']; ?> - <?php echo htmlspecialchars($t['last_name'] . ', ' . $t['first_name']); ?>
                                                </option>

                                            <?php } ?>

                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Attending Staff / Doctor</label>
                                        <select name="staff_id" class="form-select" required>
                                            <option value="">-- Choose Staff --</option>

                                            <?php while ($s = mysqli_fetch_assoc($staff_list)) { ?>

                                                <?php
                                                $selected = "";
                                                if ($edit_staff == $s['staff_id']) {
                                                    $selected = "selected";
                                                }
                                                ?>

                                                <option value="<?php echo $s['staff_id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($s['role'] . ': ' . $s['last_name'] . ', ' . $s['first_name']); ?>
                                                </option>

                                            <?php } ?>

                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Consultation Date & Time</label>
                                    <input type="datetime-local" name="consult_time" class="form-control" value="<?php echo $edit_time; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Primary Diagnosis</label>
                                    <input type="text" name="diagnosis" class="form-control" value="<?php echo htmlspecialchars($edit_diagnosis); ?>" required placeholder="e.g. Viral Fever, Hypertension">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Clinical Notes</label>
                                    <textarea name="notes" class="form-control" rows="4" placeholder="Enter detailed medical notes or prescriptions..."><?php echo htmlspecialchars($edit_notes); ?></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark w-100"><?php echo $button_text; ?></button>
                                    <a href="consultations.php" class="btn btn-light w-100" style="border: 1px solid #edf2f9;">Cancel</a>
                                </div>
                            </form>
                        </div>

                    <?php } else { ?>

                        <!-- CONSULTATIONS LIST -->
                        <div class="content-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="m-0 fw-bold">Consultation Records</h4>
                                <a href="consultations.php?action=add" class="btn btn-dark rounded-pill px-4">+ Add Record</a>
                            </div>

                            <table class="table table-borderless table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Patient Name</th>
                                        <th>Attending Staff</th>
                                        <th>Diagnosis</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (mysqli_num_rows($consults) == 0) { ?>

                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No consultations recorded yet.</td>
                                        </tr>

                                    <?php } else { ?>

                                        <?php while ($c = mysqli_fetch_assoc($consults)) { ?>
                                            <tr>
                                                <td class="text-muted"><?php echo date('M d, Y h:i A', strtotime($c['consult_time'])); ?></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($c['p_first'] . ' ' . $c['p_last']); ?></td>
                                                <td><?php echo htmlspecialchars($c['s_first'] . ' ' . $c['s_last']); ?></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['diagnosis']); ?></span></td>
                                                <td>
                                                    <a href="consultations.php?action=edit&id=<?php echo $c['consultation_id']; ?>" class="btn btn-sm btn-light border text-primary">Edit</a>
                                                    <a href="consultations.php?delete=<?php echo $c['consultation_id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this consultation record?')">Delete</a>
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