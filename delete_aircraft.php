<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "<div class='message error'>Invalid Aircraft ID.</div>";
    header("Location: manage_aircraft.php");
    exit;
}

$aircraft_id = intval($_GET['id']);

// --- Foreign Key Constraint Check ---
// Check for associated Flights
$stmt_fl = $conn->prepare("SELECT COUNT(*) FROM Flights WHERE AircraftID = ?");
$stmt_fl->bind_param("i", $aircraft_id);
$stmt_fl->execute();
$flights_count = $stmt_fl->get_result()->fetch_row()[0];
$stmt_fl->close();

if ($flights_count > 0) {
    // Cannot delete
    $_SESSION['message'] = "<div class='message error'>Cannot delete aircraft. It is associated with $flights_count flights. Please re-assign those flights first.</div>";
} else {
    // Safe to delete
    $stmt_del = $conn->prepare("DELETE FROM Aircraft WHERE AircraftID = ?");
    $stmt_del->bind_param("i", $aircraft_id);
    if ($stmt_del->execute()) {
        $_SESSION['message'] = "<div class='message success'>Aircraft deleted successfully.</div>";
    } else {
        $_SESSION['message'] = "<div class='message error'>Error deleting aircraft: " . $stmt_del->error . "</div>";
    }
    $stmt_del->close();
}

$conn->close();
header("Location: manage_aircraft.php");
exit();
?>