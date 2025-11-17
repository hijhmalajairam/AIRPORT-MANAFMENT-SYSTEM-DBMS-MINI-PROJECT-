<?php
$page = 'view_flights'; // Keep "Manage Flights" active
include 'header.php'; 

$flight_id = $_GET['flight_id'];
if (!$flight_id) {
    echo "<h3>Error: No Flight ID specified.</h3>";
    include 'footer.php';
    exit();
}

// Get Flight Info
$flight_sql = "SELECT FlightNumber, DepartureAirport, ArrivalAirport FROM Flights WHERE FlightID = $flight_id";
$flight_result = $conn->query($flight_sql);
$flight = $flight_result->fetch_assoc();
?>

<h3>Crew Assignments for Flight <?php echo htmlspecialchars($flight['FlightNumber']); ?></h3>
<p style="font-size: 1.2rem; margin-top: -20px; margin-bottom: 20px;">
    (<?php echo htmlspecialchars($flight['DepartureAirport']); ?> &rarr; <?php echo htmlspecialchars($flight['ArrivalAirport']); ?>)
</p>

<table class="clean-table">
    <thead>
        <tr>
            <th>Staff Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Phone</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT S.FirstName, S.LastName, S.Email, S.Phone, FC.AssignmentRole
                FROM Flight_Crew FC
                JOIN Airport_Staff S ON FC.StaffID = S.StaffID
                WHERE FC.FlightID = $flight_id";
        
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["FirstName"] . " " . $row["LastName"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["AssignmentRole"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["Email"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["Phone"]) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No crew assigned to this flight yet.</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php'; 
?>