<?php
$page = 'analytics';
include 'header.php';

// --- Query 1: Stat Card - Total Passengers ---
$result_pass = $conn->query("SELECT COUNT(*) AS total FROM Passengers");
$total_passengers = $result_pass->fetch_assoc()['total'];

// --- Query 2: Stat Card - Total Flights ---
$result_flights = $conn->query("SELECT COUNT(*) AS total FROM Flights");
$total_flights = $result_flights->fetch_assoc()['total'];

// --- Query 3: Stat Card - Total Revenue ---
$result_rev = $conn->query("SELECT SUM(FinalPrice) AS revenue FROM Bookings");
$total_revenue = $result_rev->fetch_assoc()['revenue'];

// --- Query 4: Stat Card - Airlines ---
$result_airlines = $conn->query("SELECT COUNT(*) AS total FROM Airlines");
$total_airlines = $result_airlines->fetch_assoc()['total'];
?>

<div class="page-header">
    <h3><i class="fas fa-chart-line"></i> Airport Analytics</h3>
</div>

<div class="stat-card-container">
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <div class="stat-info">
            <span class="stat-value"><?php echo $total_passengers; ?></span>
            <span class="stat-label">Total Passengers</span>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-plane-departure"></i>
        <div class="stat-info">
            <span class="stat-value"><?php echo $total_flights; ?></span>
            <span class="stat-label">Total Flights Logged</span>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-dollar-sign"></i>
        <div class="stat-info">
            <span class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></span>
            <span class="stat-label">Total Revenue</span>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-building"></i>
        <div class="stat-info">
            <span class="stat-value"><?php echo $total_airlines; ?></span>
            <span class="stat-label">Airlines Operating</span>
        </div>
    </div>
</div>

<h3 style="margin-top: 40px;">Recent Flight History</h3>
<table class="clean-table">
    <thead>
        <tr>
            <th>Flight No.</th>
            <th>Airline</th>
            <th>Route</th>
            <th>Scheduled On</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT F.FlightNumber, A.AirlineName, F.DepartureAirport, F.ArrivalAirport, F.ScheduledDeparture, F.FlightStatus
                FROM Flights F
                JOIN Airlines A ON F.AirlineID = A.AirlineID
                ORDER BY F.ScheduledDeparture DESC
                LIMIT 10"; // Get the 10 most recent flights
        
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . e($row["FlightNumber"]) . "</td>";
                echo "<td>" . e($row["AirlineName"]) . "</td>";
                echo "<td>" . e($row["DepartureAirport"]) . " &rarr; " . e($row["ArrivalAirport"]) . "</td>";
                echo "<td>" . date('M d, Y - h:i A', strtotime($row["ScheduledDeparture"])) . "</td>";
                echo "<td>" . e($row["FlightStatus"]) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No flight history found.</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>