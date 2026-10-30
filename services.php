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

    $result = mysqli_query($conn, "DELETE FROM services WHERE service_id = $id");

    if ($result) {
        header("Location: services.php");
        exit;
    } else {
        echo "<script>
                alert('Cannot delete this service because it is actively linked to a Queue Ticket.');
                window.location.href = 'services.php';
              </script>";
        exit;
    }
}

// ==========================================
// 3. HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $service_name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $description  = mysqli_real_escape_string($conn, $_POST['description']);

    if (!empty($_POST['service_id'])) {

        $id = (int) $_POST['service_id'];

        $sql = "UPDATE services SET
                    service_name = '$service_name',
                    description  = '$description'
                WHERE service_id = $id";
        mysqli_query($conn, $sql);

    } else {

        $sql = "INSERT INTO services (service_name, description)
                VALUES ('$service_name', '$description')";
        mysqli_query($conn, $sql);
    }

    header("Location: services.php");
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
$edit_name = "";
$edit_description = "";

if ($action == 'edit' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $result = mysqli_query($conn, "SELECT * FROM services WHERE service_id = $id");
    $record = mysqli_fetch_assoc($result);

    if ($record) {
        $edit_id          = $record['service_id'];
        $edit_name        = $record['service_name'];
        $edit_description = $record['description'];
    }
}

$form_title = "Add New Service";
$button_text = "Save Service";
if ($action == 'edit') {
    $form_title = "Edit Service";
    $button_text = "Update Service";
}

// ==========================================
// 5. GET ALL SERVICES FOR THE LIST
// ==========================================
$services = mysqli_query($conn, "SELECT * FROM services ORDER BY service_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Services - Panacea Health Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR -->
            <div class="col-md-2 sidebar position-fixed d-flex flex-column justify-content-between">
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
    <a href="services.php" class="nav-link active d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        Services
    </a>
    <!-- Queue Tickets -->
    <a href="queue_tickets.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.1 0-1.99.89-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-1.99 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-9 7.5h-2v-2h2v2zm0-4.5h-2v-2h2v2zm0-4.5h-2v-2h2v2z"/></svg>
        Queue Tickets
    </a>
    <!-- Consultations (Active here!) -->
    <a href="consultations.php" class="nav-link  d-flex align-items-center">
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
                    <div class="active-tab">/ Services Directory</div>
                    <div>/ Manage Clinic Services</div>
                </div>

                <div class="content-pad">

                    <?php if ($action == 'add' || $action == 'edit') { ?>

                        <!-- ADD / EDIT FORM -->
                        <div class="content-card mx-auto" style="max-width: 600px;">
                            <h4 class="fw-bold mb-4"><?php echo $form_title; ?></h4>

                            <form method="POST" action="services.php">
                                <input type="hidden" name="service_id" value="<?php echo $edit_id; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Service Name</label>
                                    <input type="text" name="service_name" class="form-control" value="<?php echo htmlspecialchars($edit_name); ?>" required placeholder="e.g. General Checkup, Vaccination">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Description (Optional)</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the service..."><?php echo htmlspecialchars($edit_description); ?></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark w-100"><?php echo $button_text; ?></button>
                                    <a href="services.php" class="btn btn-light w-100" style="border: 1px solid #edf2f9;">Cancel</a>
                                </div>
                            </form>
                        </div>

                    <?php } else { ?>

                        <!-- SERVICES LIST -->
                        <div class="content-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="m-0 fw-bold">Services Directory</h4>
                                <a href="services.php?action=add" class="btn btn-dark rounded-pill px-4">+ Add Service</a>
                            </div>

                            <table class="table table-borderless table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (mysqli_num_rows($services) == 0) { ?>

                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No services found in the system.</td>
                                        </tr>

                                    <?php } else { ?>

                                        <?php while ($s = mysqli_fetch_assoc($services)) { ?>
                                            <tr>
                                                <td class="text-muted">#<?php echo $s['service_id']; ?></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($s['service_name']); ?></td>
                                                <td><?php echo htmlspecialchars($s['description']); ?></td>
                                                <td>
                                                    <a href="services.php?action=edit&id=<?php echo $s['service_id']; ?>" class="btn btn-sm btn-light border text-primary">Edit</a>
                                                    <a href="services.php?delete=<?php echo $s['service_id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this service?')">Delete</a>
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