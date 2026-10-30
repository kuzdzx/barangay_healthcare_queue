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

    $sql = "DELETE FROM patients WHERE patient_id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: index.php");
        exit;
    } else {
        echo "<script>
                alert('Cannot delete this patient because they are linked to a queue ticket or consultation.');
                window.location.href = 'index.php';
              </script>";
        exit;
    }
}

// ==========================================
// 3. GET STATS FOR THE DASHBOARD CARDS
// ==========================================

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM patients");
$row = mysqli_fetch_assoc($result);
$total_patients = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM services");
$row = mysqli_fetch_assoc($result);
$total_services = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM queue_tickets");
$row = mysqli_fetch_assoc($result);
$total_tickets = $row['total'];

// ==========================================
// 4. GET THE 10 NEWEST PATIENTS
// ==========================================
$patients = mysqli_query($conn, "SELECT * FROM patients ORDER BY patient_id DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panacea - Health Center Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font: Inter -->
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
    <a href="index.php" class="nav-link active d-flex align-items-center">
        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
        Dashboard
    </a>
    <!-- Patients -->
    <a href="patients.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5-4-8-4z"/></svg>
        Patients
    </a>
    <!-- Clinical Staff -->
    <a href="staff.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm6 12H6v-1c0-2 4-3.1 6-3.1s6 1.1 6 3.1v1z"/></svg>
        Clinical Staff
    </a>
    <!-- Services -->
    <a href="services.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        Services
    </a>
    <!-- Queue Tickets -->
    <a href="queue_tickets.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.1 0-1.99.89-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-1.99 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-9 7.5h-2v-2h2v2zm0-4.5h-2v-2h2v2zm0-4.5h-2v-2h2v2z"/></svg>
        Queue Tickets
    </a>
    <!-- Consultations -->
    <a href="consultations.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
        Consultations
    </a>
    <!-- Medicines -->
    <a href="medicines.php" class="nav-link d-flex align-items-center">
        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 3h12v2H6zm11.5 5c.83 0 1.5.67 1.5 1.5v10c0 .83-.67 1.5-1.5 1.5h-11C5.67 21 5 20.33 5 19.5v-10C5 8.67 5.67 8 6.5 8h11zm-4 3h-3v2.5H7v3h3.5V19h3v-2.5H17v-3h-3.5V11z"/></svg>
        Medicines
    </a>
</div>

                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-10 offset-md-2 main-content">

                <div class="topbar">
                    <div class="active-tab">/ System Overview</div>
                    <div>/ Quick Stats</div>
                </div>

                <div class="content-pad">

                    <!-- STAT CARDS -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon icon-green">📅</div>
                                <div class="stat-info">
                                    <h6>Total Patients</h6>
                                    <p>Registered in system</p>
                                    <div class="stat-number"><?php echo $total_patients; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon icon-blue">👥</div>
                                <div class="stat-info">
                                    <h6>Total Services</h6>
                                    <p>Available at Center</p>
                                    <div class="stat-number"><?php echo $total_services; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon icon-orange">🎫</div>
                                <div class="stat-info">
                                    <h6>Queue Tickets</h6>
                                    <p>All Time</p>
                                    <div class="stat-number"><?php echo $total_tickets; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RECENT PATIENTS TABLE -->
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="m-0 fw-bold">Recent Patients</h5>
                            <a href="patients.php?action=add" class="btn btn-dark btn-sm rounded-pill px-3">+ Add Patient</a>
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
                                        <td colspan="6" class="text-center py-4">No patients found.</td>
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
                                                <a href="index.php?delete=<?php echo $p['patient_id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this patient?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>