<?php
$page = 'view_flights';
include 'header.php';

$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle "Report Delay" form submission
if (isset($_POST['report_delay'])) {
    $flight_id = $_POST['flight_id'];
    $delay_reason = $_POST['delay_reason'];
    $delay_duration = $_POST['delay_duration'];

    $sql_delay = "INSERT INTO Flight_Delays (FlightID, DelayReason, DelayDuration, NewDepartureTime, NewArrivalTime, Status)
                  SELECT ?, ?, ?, 
                         DATE_ADD(ScheduledDeparture, INTERVAL ? MINUTE), 
                         DATE_ADD(ScheduledArrival, INTERVAL ? MINUTE),
                         'Reported'
                  FROM Flights WHERE FlightID = ?";
    
    $stmt_delay = $conn->prepare($sql_delay);
    $stmt_delay->bind_param("isiiii", $flight_id, $delay_reason, $delay_duration, $delay_duration, $delay_duration, $flight_id);
    
    if ($stmt_delay->execute()) {
        $update_sql = "UPDATE Flights SET FlightStatus = 'Delayed' WHERE FlightID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $flight_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        $message = "<div class='message success'><i class='fas fa-check-circle'></i> Delay reported successfully!</div>";
    } else {
        $message = "<div class='message error'><i class='fas fa-exclamation-circle'></i> Error reporting delay.</div>";
    }
    $stmt_delay->close();
}
?>

<div class="page-header">
    <h3><i class="fas fa-plane"></i> Flight Operations (Master List)</h3>
    <a href="add_flight.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Flight</a>
</div>

<?php echo $message; ?>

<h3 style="margin-top: 30px;">All Departures</h3>
<div class="flight-card-container">
    <?php
    $sql_dep = "SELECT F.FlightID, F.FlightNumber, A.AirlineName, F.DepartureAirport, F.ArrivalAirport, 
                   F.ScheduledDeparture, F.FlightStatus
            FROM Flights F
            JOIN Airlines A ON F.AirlineID = A.AirlineID
            ORDER BY F.ScheduledDeparture DESC";
    $dep_result = $conn->query($sql_dep);
    
    if ($dep_result->num_rows > 0):
        while($row = $dep_result->fetch_assoc()):
            $status_class = str_replace(' ', '.', $row["FlightStatus"]);
    ?>
        <div class="flight-card">
            <div class="fc-airline-logo"><i class="fas fa-plane-departure"></i></div>
            <div class="fc-details">
                <div class="flight-num"><?php echo e($row["FlightNumber"]); ?></div>
                <div class="airline"><?php echo e($row["AirlineName"]); ?></div>
            </div>
            <div class="fc-route">
                <i class="fas fa-map-marker-alt" style="color: var(--success);"></i> 
                <?php echo e($row["DepartureAirport"]); ?> 
                <i class="fas fa-arrow-right" style="margin: 0 8px; color: #64748b;"></i> 
                <?php echo e($row["ArrivalAirport"]); ?>
            </div>
            <div class="fc-time">
                <i class="fas fa-clock" style="margin-right: 6px;"></i>
                <?php echo date('M d, Y - h:i A', strtotime($row["ScheduledDeparture"])); ?>
            </div>
            <div class="fc-status <?php echo e($status_class); ?>"><?php echo e($row["FlightStatus"]); ?></div>
            <div class="action-buttons">
                <a href="view_crew.php?flight_id=<?php echo $row['FlightID']; ?>" class="action-btn btn-view"><i class="fas fa-users"></i> Crew</a>
                <a href="edit_flight.php?id=<?php echo $row['FlightID']; ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                <button onclick="showDelayModal(<?php echo $row['FlightID']; ?>, '<?php echo e($row["FlightNumber"]); ?>')" class="action-btn" style="background: var(--warning);"><i class="fas fa-clock"></i> Delay</button>
                <button onclick="showDeleteModal(<?php echo $row['FlightID']; ?>)" class="action-btn btn-delete"><i class="fas fa-trash-alt"></i> Delete</button>
            </div>
        </div>
    <?php
        endwhile;
    else:
        echo "<div class='message'>No departure flights found.</div>";
    endif;
    ?>
</div>

<h3 style="margin-top: 40px; border-top: 2px solid var(--border); padding-top: 30px;">All Arrivals</h3>
<div class="flight-card-container">
    <?php
    $sql_arr = "SELECT F.FlightID, F.FlightNumber, A.AirlineName, F.DepartureAirport, F.ArrivalAirport, 
                   F.ScheduledArrival, F.FlightStatus
            FROM Flights F
            JOIN Airlines A ON F.AirlineID = A.AirlineID
            ORDER BY F.ScheduledArrival DESC";
    $arr_result = $conn->query($sql_arr);
    
    if ($arr_result->num_rows > 0):
        while($row = $arr_result->fetch_assoc()):
            $status_class = str_replace(' ', '.', $row["FlightStatus"]);
    ?>
        <div class="flight-card">
            <div class="fc-airline-logo"><i class="fas fa-plane-arrival" style="color: var(--success);"></i></div>
            <div class="fc-details">
                <div class="flight-num"><?php echo e($row["FlightNumber"]); ?></div>
                <div class="airline"><?php echo e($row["AirlineName"]); ?></div>
            </div>
            <div class="fc-route">
                <?php echo e($row["DepartureAirport"]); ?> 
                <i class="fas fa-arrow-right" style="margin: 0 8px; color: #64748b;"></i> 
                <i class="fas fa-map-marker-alt" style="color: var(--primary);"></i>
                <?php echo e($row["ArrivalAirport"]); ?>
            </div>
            <div class="fc-time">
                <i class="fas fa-clock" style="margin-right: 6px;"></i>
                <?php echo date('M d, Y - h:i A', strtotime($row["ScheduledArrival"])); ?>
            </div>
            <div class="fc-status <?php echo e($status_class); ?>"><?php echo e($row["FlightStatus"]); ?></div>
            <div class="action-buttons">
                <a href="view_crew.php?flight_id=<?php echo $row['FlightID']; ?>" class="action-btn btn-view"><i class="fas fa-users"></i> Crew</a>
                <a href="edit_flight.php?id=<?php echo $row['FlightID']; ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                <button onclick="showDelayModal(<?php echo $row['FlightID']; ?>, '<?php echo e($row["FlightNumber"]); ?>')" class="action-btn" style="background: var(--warning);"><i class="fas fa-clock"></i> Delay</button>
                <button onclick="showDeleteModal(<?php echo $row['FlightID']; ?>)" class="action-btn btn-delete"><i class="fas fa-trash-alt"></i> Delete</button>
            </div>
        </div>
    <?php
        endwhile;
    else:
        echo "<div class='message'>No arrival flights found.</div>";
    endif;
    ?>
</div>


<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i> Confirm Deletion</h3>
        <p>Are you sure you want to delete this flight? This will also delete all associated bookings, crew assignments, and delay records. This action cannot be undone.</p>
        <div class="modal-buttons">
            <button type="button" onclick="hideDeleteModal()">Cancel</button>
            <a id="confirmDeleteLink" href="#"><button>Delete Flight</button></a>
        </div>
    </div>
</div>

<div id="delayModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-clock" style="color: var(--warning);"></i> Report Delay for Flight <span id="delayFlightNum"></span></h3>
        <form action="view_flights.php" method="POST">
            <input type="hidden" id="delayFlightID" name="flight_id" value="">
            <div>
                <label>Reason for Delay *</label>
                <select name="delay_reason" required>
                    <option value="">-- Select Reason --</option>
                    <option value="Weather">Weather Conditions</option>
                    <option value="Technical Issue">Technical Issue</option>
                    <option value="Air Traffic">Air Traffic Congestion</option>
                    <option value="Crew Unavailable">Crew Unavailable</option>
                    <option value="Late Arrival">Late Arrival of Aircraft</option>
                    <option value="Security Concern">Security Concern</option>
                    <option value="Operational">Operational Issues</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label>Delay Duration (in minutes) *</label>
                <input type="number" name="delay_duration" placeholder="e.g., 60" min="1" required>
            </div>
            <div class="modal-buttons">
                <button type="button" onclick="hideDelayModal()">Cancel</button>
                <button type="submit" name="report_delay" style="background: var(--warning);"><i class="fas fa-exclamation-triangle"></i> Report Delay</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
const deleteModal = document.getElementById("deleteModal");
const delayModal = document.getElementById("delayModal");

function showDeleteModal(flightID) {
    document.getElementById("confirmDeleteLink").href = "delete_flight.php?id=" + flightID;
    deleteModal.style.display = "flex";
}
function hideDeleteModal() { deleteModal.style.display = "none"; }

function showDelayModal(flightID, flightNum) {
    document.getElementById("delayFlightID").value = flightID;
    document.getElementById("delayFlightNum").innerText = flightNum;
    delayModal.style.display = "flex";
}
function hideDelayModal() { delayModal.style.display = "none"; }

window.onclick = function(event) {
    if (event.target == deleteModal) { hideDeleteModal(); }
    if (event.target == delayModal) { hideDelayModal(); }
}
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        hideDeleteModal();
        hideDelayModal();
    }
});
</script>