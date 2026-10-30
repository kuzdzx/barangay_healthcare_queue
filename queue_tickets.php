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

    $result = mysqli_query($conn, "DELETE FROM queue_tickets WHERE ticket_id = $id");

    if ($result) {
        header("Location: queue_tickets.php");
        exit;
    } else {
        echo "<script>
                alert('Cannot delete this ticket because it is linked to a consultation record.');
                window.location.href = 'queue_tickets.php';
              </script>";
        exit;
    }
}

// ==========================================
// 3. HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $patient_id   = (int) $_POST['patient_id'];
    $service_id   = (int) $_POST['service_id'];
    $queue_number = (int) $_POST['queue_number'];
    $queue_date   = mysqli_real_escape_string($conn, $_POST['queue_date']);
    $status       = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($_POST['ticket_id'])) {

        $id = (int) $_POST['ticket_id'];

        $sql = "UPDATE queue_tickets SET
                    patient_id   = $patient_id,
                    service_id   = $service_id,
                    queue_number = $queue_number,
                    queue_date   = '$queue_date',
                    status       = '$status'
                WHERE ticket_id = $id";
        mysqli_query($conn, $sql);

    } else {

        $sql = "INSERT INTO queue_tickets (patient_id, service_id, queue_number, queue_date, status)
                VALUES ($patient_id, $service_id, $queue_number, '$queue_date', '$status')";
        mysqli_query($conn, $sql);
    }

    header("Location: queue_tickets.php");
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
$edit_patient = "";
$edit_service = "";
$edit_number = "";
$edit_date = "";
$edit_status = "";

if ($action == 'edit' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $result = mysqli_query($conn, "SELECT * FROM queue_tickets WHERE ticket_id = $id");
    $record = mysqli_fetch_assoc($result);

    if ($record) {
        $edit_id      = $record['ticket_id'];
        $edit_patient = $record['patient_id'];
        $edit_service = $record['service_id'];
        $edit_number  = $record['queue_number'];
        $edit_date    = $record['queue_date'];
        $edit_status  = $record['status'];
    }
}

if ($edit_date == "") {
    $edit_date = date('Y-m-d');
}

$form_title = "Issue New Ticket";
$button_text = "Save & Issue Ticket";
if ($action == 'edit') {
    $form_title = "Edit Queue Ticket";
    $button_text = "Update Ticket";
}

$select_waiting = "";
$select_serving = "";
$select_done = "";
$select_cancelled = "";

if ($edit_status == "Waiting") {
    $select_waiting = "selected";
} elseif ($edit_status == "Serving") {
    $select_serving = "selected";
} elseif ($edit_status == "Done") {
    $select_done = "selected";
} elseif ($edit_status == "Cancelled") {
    $select_cancelled = "selected";
}

// ==========================================
// 5. GET DATA FOR THE DROPDOWNS
// ==========================================
$patients = mysqli_query($conn, "SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name ASC");

$services = mysqli_query($conn, "SELECT service_id, service_name FROM services ORDER BY service_name ASC");

// ==========================================
// 6. GET ALL QUEUE TICKETS FOR THE LIST
// ==========================================
$sql = "SELECT q.ticket_id, q.queue_number, q.queue_date, q.status,
               p.first_name, p.last_name,
               s.service_name
        FROM queue_tickets q
        JOIN patients p ON q.patient_id = p.patient_id
        JOIN services s ON q.service_id = s.service_id
        ORDER BY q.queue_date DESC, q.queue_number ASC";

$tickets = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Queue Tickets - Panacea Health Center</title>
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
    <a href="queue_tickets.php" class="nav-link active d-flex align-items-center">
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
                    <div class="active-tab">/ Queue Management</div>
                    <div>/ Issue & Track Tickets</div>
                </div>

                <div class="content-pad">

                    <?php if ($action == 'add' || $action == 'edit') { ?>

                        <!-- ADD / EDIT FORM -->
                        <div class="content-card mx-auto" style="max-width: 600px;">
                            <h4 class="fw-bold mb-4"><?php echo $form_title; ?></h4>

                            <form method="POST" action="queue_tickets.php">
                                <input type="hidden" name="ticket_id" value="<?php echo $edit_id; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Select Patient (Foreign Key)</label>
                                    <select name="patient_id" class="form-select" required>
                                        <option value="">-- Choose a Registered Patient --</option>

                                        <?php while ($p = mysqli_fetch_assoc($patients)) { ?>

                                            <?php
                                            $selected = "";
                                            if ($edit_patient == $p['patient_id']) {
                                                $selected = "selected";
                                            }
                                            ?>

                                            <option value="<?php echo $p['patient_id']; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($p['last_name'] . ', ' . $p['first_name']); ?>
                                            </option>

                                        <?php } ?>

                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Service (Foreign Key)</label>
                                    <select name="service_id" class="form-select" required>
                                        <option value="">-- Choose a Service --</option>

                                        <?php while ($s = mysqli_fetch_assoc($services)) { ?>

                                            <?php
                                            $selected = "";
                                            if ($edit_service == $s['service_id']) {
                                                $selected = "selected";
                                            }
                                            ?>

                                            <option value="<?php echo $s['service_id']; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($s['service_name']); ?>
                                            </option>

                                        <?php } ?>

                                    </select>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Queue Number</label>
                                        <input type="number" name="queue_number" class="form-control" value="<?php echo $edit_number; ?>" min="1" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Queue Date</label>
                                        <input type="date" name="queue_date" class="form-control" value="<?php echo $edit_date; ?>" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="Waiting" <?php echo $select_waiting; ?>>Waiting</option>
                                        <option value="Serving" <?php echo $select_serving; ?>>Serving</option>
                                        <option value="Done" <?php echo $select_done; ?>>Done</option>
                                        <option value="Cancelled" <?php echo $select_cancelled; ?>>Cancelled</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark w-100"><?php echo $button_text; ?></button>
                                    <a href="queue_tickets.php" class="btn btn-light w-100" style="border: 1px solid #edf2f9;">Cancel</a>
                                </div>
                            </form>
                        </div>

                    <?php } else { ?>

                        <!-- QUEUE TICKETS LIST -->
                        <div class="content-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="m-0 fw-bold">Daily Queue Monitor</h4>
                                <a href="queue_tickets.php?action=add" class="btn btn-dark rounded-pill px-4">+ Issue Ticket</a>
                            </div>

                            <table class="table table-borderless table-hover">
                                <thead>
                                    <tr>
                                        <th>Q-No.</th>
                                        <th>Patient Name</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (mysqli_num_rows($tickets) == 0) { ?>

                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No queue tickets issued yet.</td>
                                        </tr>

                                    <?php } else { ?>

                                        <?php while ($t = mysqli_fetch_assoc($tickets)) { ?>

                                            <?php
                                            $badge_class = "badge-soft-primary";
                                            if ($t['status'] == "Waiting") {
                                                $badge_class = "badge-soft-warning";
                                            } elseif ($t['status'] == "Serving") {
                                                $badge_class = "badge-soft-primary";
                                            } elseif ($t['status'] == "Done") {
                                                $badge_class = "badge-soft-success";
                                            } elseif ($t['status'] == "Cancelled") {
                                                $badge_class = "badge-soft-danger";
                                            }

                                            $queue_label = str_pad($t['queue_number'], 3, "0", STR_PAD_LEFT);
                                            ?>

                                            <tr>
                                                <td><span class="badge bg-dark fs-6" style="border-radius: 8px;">#<?php echo $queue_label; ?></span></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></td>
                                                <td class="text-muted"><?php echo htmlspecialchars($t['service_name']); ?></td>
                                                <td><?php echo $t['queue_date']; ?></td>
                                                <td><span class="<?php echo $badge_class; ?>"><?php echo $t['status']; ?></span></td>
                                                <td>
                                                    <a href="queue_tickets.php?action=edit&id=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-light border text-primary">Edit</a>
                                                    <a href="queue_tickets.php?delete=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this ticket?')">Delete</a>
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