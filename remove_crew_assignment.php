<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $crew_assignment_id = $_GET['id'];

    $sql = "DELETE FROM Flight_Crew WHERE CrewAssignmentID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $crew_assignment_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "<div class='message success'>Crew assignment removed successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='message error'>Error removing assignment: " . $stmt->error . "</div>";
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "<div class='message error'>Invalid request: No assignment ID provided.</div>";
}

header("Location: assign_crew.php");
exit();
?>