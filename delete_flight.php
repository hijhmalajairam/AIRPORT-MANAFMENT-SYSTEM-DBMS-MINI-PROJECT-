<?php
// delete_flight.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Flight ID.");
}

$flight_id = intval($_GET['id']); // Sanitize input

// Use transaction for safety
$conn->begin_transaction();

try {
    // 1. Delete from Flight_Delays
    $stmt1 = $conn->prepare("DELETE FROM Flight_Delays WHERE FlightID = ?");
    $stmt1->bind_param("i", $flight_id);
    $stmt1->execute();
    $stmt1->close();

    // 2. Delete from Baggage (via Bookings)
    // We need to find all BookingIDs for this flight first
    $stmt_b = $conn->prepare("DELETE FROM Baggage WHERE BookingID IN (SELECT BookingID FROM Bookings WHERE FlightID = ?)");
    $stmt_b->bind_param("i", $flight_id);
    $stmt_b->execute();
    $stmt_b->close();

    // 3. Delete from Bookings
    $stmt2 = $conn->prepare("DELETE FROM Bookings WHERE FlightID = ?");
    $stmt2->bind_param("i", $flight_id);
    $stmt2->execute();
    $stmt2->close();

    // 4. Delete from Flight_Crew
    $stmt3 = $conn->prepare("DELETE FROM Flight_Crew WHERE FlightID = ?");
    $stmt3->bind_param("i", $flight_id);
    $stmt3->execute();
    $stmt3->close();

    // 5. Finally, delete the parent flight
    $stmt4 = $conn->prepare("DELETE FROM Flights WHERE FlightID = ?");
    $stmt4->bind_param("i", $flight_id);
    $stmt4->execute();
    $stmt4->close();

    // If all deletions were successful, commit the transaction
    $conn->commit();
    
    // Set a success message in session to show on redirect
    $_SESSION['message'] = "<div class='message success'>Flight $flight_id and all associated data deleted!</div>";
    header("Location: view_flights.php");
    exit();

} catch (Exception $e) {
    // Something went wrong, roll back
    $conn->rollback();
    die("Error deleting record: " . $e->getMessage());
}

$conn->close();
?>