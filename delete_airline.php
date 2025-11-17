<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "<div class='message error'>Invalid Airline ID.</div>";
    header("Location: manage_airlines.php");
    exit;
}

$airline_id = intval($_GET['id']);

// --- Foreign Key Constraint Check ---
// 1. Check for associated Aircraft
$stmt_ac = $conn->prepare("SELECT COUNT(*) FROM Aircraft WHERE AirlineID = ?");
$stmt_ac->bind_param("i", $airline_id);
$stmt_ac->execute();
$aircraft_count = $stmt_ac->get_result()->fetch_row()[0];
$stmt_ac->close();

// 2. Check for associated Flights
$stmt_fl = $conn->prepare("SELECT COUNT(*) FROM Flights WHERE AirlineID = ?");
$stmt_fl->bind_param("i", $airline_id);
$stmt_fl->execute();
$flights_count = $stmt_fl->get_result()->fetch_row()[0];
$stmt_fl->close();

if ($aircraft_count > 0 || $flights_count > 0) {
    // Cannot delete
    $_SESSION['message'] = "<div class='message error'>Cannot delete airline. It is associated with $aircraft_count aircraft and $flights_count flights. Please delete them first.</div>";
} else {
    // Safe to delete
    $stmt_del = $conn->prepare("DELETE FROM Airlines WHERE AirlineID = ?");
    $stmt_del->bind_param("i", $airline_id);
    if ($stmt_del->execute()) {
        $_SESSION['message'] = "<div class='message success'>Airline deleted successfully.</div>";
    } else {
        $_SESSION['message'] = "<div class='message error'>Error deleting airline: " . $stmt_del->error . "</div>";
    }
    $stmt_del->close();
}

$conn->close();
header("Location: manage_airlines.php");
exit();
?>