<?php
$page = 'view_delays';
include 'header.php';
?>

<div class="page-header">
    <h3><i class="fas fa-clock"></i> All Reported Flight Delays</h3>
</div>

<table class="clean-table">
    <thead>
        <tr>
            <th>Flight No.</th>
            <th>Airline</th>
            <th>Reason</th>
            <th>Delay (Mins)</th>
            <th>New Departure</th>
            <th>New Arrival</th>
            <th>Actions</th> </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT FD.DelayID, F.FlightNumber, A.AirlineName, FD.DelayReason, FD.DelayDuration, FD.NewDepartureTime, FD.NewArrivalTime
                FROM Flight_Delays FD
                JOIN Flights F ON FD.FlightID = F.FlightID
                JOIN Airlines A ON F.AirlineID = A.AirlineID
                ORDER BY FD.NewDepartureTime DESC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . e($row["FlightNumber"]) . "</td>";
                echo "<td>" . e($row["AirlineName"]) . "</td>";
                echo "<td>" . e($row["DelayReason"]) . "</td>";
                echo "<td>" . e($row["DelayDuration"]) . "</td>";
                echo "<td>" . e($row["NewDepartureTime"]) . "</td>";
                echo "<td>" . e($row["NewArrivalTime"]) . "</td>";
                // New Edit Button
                echo "<td>
                        <a href='edit_delay.php?id=" . $row['DelayID'] . "' class='action-btn btn-edit'>
                            <i class='fas fa-edit'></i> Edit
                        </a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No delays found</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>