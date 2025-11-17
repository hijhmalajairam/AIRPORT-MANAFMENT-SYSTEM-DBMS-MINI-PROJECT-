<?php
// db_connect.php - IMPROVED VERSION
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL password (default is blank for XAMPP)
$dbname = "airportflightdb";

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Display a user-friendly error
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>
            <h2>❌ Database Connection Failed!</h2>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
            <p><strong>Please check:</strong></p>
            <ol>
                <li>Is your XAMPP Apache & MySQL server running?</li>
                <li>Does the database '<strong>" . $dbname . "</strong>' exist in phpMyAdmin?</li>
                <li>Is the password '<strong>" . $password . "</strong>' correct for your 'root' user?</li>
            </ol>
         </div>");
}
?>