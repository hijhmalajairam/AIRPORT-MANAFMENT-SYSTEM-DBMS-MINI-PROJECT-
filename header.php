<?php
// header.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_name = $_SESSION['user_name'];

// Utility function for sanitizing output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airport Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<nav class="navbar">
    <a href="dashboard.php" class="logo"><i class="fas fa-plane-departure"></i> Airport System</a>
    <div class="nav-links">
        <a href="dashboard.php" class="<?php echo ($page == 'dashboard' ? 'active' : ''); ?>">Status Board</a>
        <a href="view_flights.php" class="<?php echo ($page == 'view_flights' ? 'active' : ''); ?>">Flight Ops</a>
        <a href="manage_passengers.php" class="<?php echo ($page == 'passengers' ? 'active' : ''); ?>">Passengers</a>
        <a href="assign_crew.php" class="<?php echo ($page == 'assign_crew' ? 'active' : ''); ?>">Assign Crew</a>
        
        <a href="manage_aircraft.php" class="<?php echo ($page == 'aircraft' ? 'active' : ''); ?>">Aircraft</a>
        <a href="manage_airlines.php" class="<?php echo ($page == 'manage_airlines' ? 'active' : ''); ?>">Airlines</a> 
        
        <a href="manage_gates.php" class="<?php echo ($page == 'gates' ? 'active' : ''); ?>">Gates</a>
        <a href="view_delays.php" class="<?php echo ($page == 'view_delays' ? 'active' : ''); ?>">Delays</a>
        <a href="analytics.php" class="<?php echo ($page == 'analytics' ? 'active' : ''); ?>">Analytics</a>
        <a href="profile.php" class="<?php echo ($page == 'profile' ? 'active-profile' : 'nav-profile'); ?>">
            <i class="fas fa-user"></i> Profile
        </a>
    </div>
    <div class="user-buttons">
        <span class="welcome-text">Welcome, <strong><?php echo e($user_name); ?>!</strong></span>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</nav>
<div class="content-container">