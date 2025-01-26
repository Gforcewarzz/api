<?php
// Set security headers
header('Content-Type: text/plain');

// Database connection parameters
$servername = "sql303.infinityfree.com";
$username = "if0_38106551";
$password = "U6EUggvdO0Z";
$dbname = "if0_38106551_db_agenda";

// Validate bypass key
$expectedBypassKey = "your_secret_key_here"; // Match this with the ESP32 code
$providedBypassKey = isset($_GET['bypass']) ? $_GET['bypass'] : '';

if ($providedBypassKey !== $expectedBypassKey) {
    die("unauthorized");
}

// Sanitize UID input
$uid = isset($_GET['uid']) ? strtoupper(trim($_GET['uid'])) : '';
if (empty($uid)) {
    die("no_uid");
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("database_error");
}

// Check if UID exists
$sqlCheckUID = "SELECT siswaID FROM siswa WHERE uid = ?";
$stmt = $conn->prepare($sqlCheckUID);
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "kartuTidakTerdaftar";
} else {
    $row = $result->fetch_assoc();
    $siswaID = $row['siswaID'];

    // Check attendance for today
    $currentDate = date("Y-m-d");
    $sqlCheckAttendance = "SELECT * FROM kehadiran WHERE siswaID = ? AND DATE(jamHadir) = ?";
    $stmt2 = $conn->prepare($sqlCheckAttendance);
    $stmt2->bind_param("is", $siswaID, $currentDate);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($result2->num_rows > 0) {
        // Attendance already recorded
        echo "sudahAbsenLengkap";
    } else {
        // Insert new attendance record
        $sqlInsertAttendance = "INSERT INTO kehadiran (siswaID, jamHadir, keterangan, ketPulang) VALUES (?, NOW(), 'Hadir', 'Belum')";
        $stmt3 = $conn->prepare($sqlInsertAttendance);
        $stmt3->bind_param("i", $siswaID);

        if ($stmt3->execute()) {
            echo "inserted";
        } else {
            echo "error";
        }
        $stmt3->close();
    }

    $stmt2->close();
}

$stmt->close();
$conn->close();
?>
