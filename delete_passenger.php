<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "<div class='message error'>Invalid Passenger ID.</div>";
    header("Location: manage_passengers.php");
    exit;
}

$passenger_id = intval($_GET['id']);

// --- Foreign Key Constraint Check ---
// Check for associated Bookings
$stmt_bk = $conn->prepare("SELECT COUNT(*) FROM Bookings WHERE PassengerID = ?");
$stmt_bk->bind_param("i", $passenger_id);
$stmt_bk->execute();
$bookings_count = $stmt_bk->get_result()->fetch_row()[0];
$stmt_bk->close();

if ($bookings_count > 0) {
    // Cannot delete
    $_SESSION['message'] = "<div class='message error'>Cannot delete passenger. They are associated with $bookings_count bookings. Please cancel their bookings first.</div>";
} else {
    // Safe to delete
    $stmt_del = $conn->prepare("DELETE FROM Passengers WHERE PassengerID = ?");
    $stmt_del->bind_param("i", $passenger_id);
    if ($stmt_del->execute()) {
        $_SESSION['message'] = "<div class='message success'>Passenger deleted successfully.</div>";
    } else {
        $_SESSION['message'] = "<div class='message error'>Error deleting passenger: " . $stmt_del->error . "</div>";
    }
    $stmt_del->close();
}

$conn->close();
header("Location: manage_passengers.php");
exit();
?>