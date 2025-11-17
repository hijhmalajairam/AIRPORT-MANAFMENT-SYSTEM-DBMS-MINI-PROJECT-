<?php
$page = 'view_flights'; // Keep the "View Flights" link active
include 'header.php';
$message = "";

// Get the Flight ID from the URL
$flight_id = $_GET['id'];
if (!$flight_id) {
    echo "Invalid Flight ID.";
    exit;
}

// --- Handle Form Submission (UPDATE logic) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flight_num = $_POST['flight_num'];
    $airline_id = $_POST['airline_id'];
    $aircraft_id = $_POST['aircraft_id'];
    $dep_time = $_POST['dep_time'];
    $arr_time = $_POST['arr_time'];
    $gate_id = $_POST['gate_id'];
    $status = $_POST['status'];

    $sql = "UPDATE Flights SET 
                FlightNumber = ?, AirlineID = ?, AircraftID = ?, 
                ScheduledDeparture = ?, ScheduledArrival = ?, 
                DepartureGateID = ?, FlightStatus = ?
            WHERE FlightID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssisi", $flight_num, $airline_id, $aircraft_id, $dep_time, $arr_time, $gate_id, $status, $flight_id);

    if ($stmt->execute() === TRUE) {
        $message = "<div class='message success'>Flight updated successfully!</div>";
    } else {
        $message = "<div class='message error'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- Fetch existing data for the form ---
$sql = "SELECT * FROM Flights WHERE FlightID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $flight_id);
$stmt->execute();
$flight = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- Get Dropdown Data ---
$airlines_result = $conn->query("SELECT AirlineID, AirlineName FROM Airlines");
$aircraft_result = $conn->query("SELECT AircraftID, RegistrationNumber, AircraftModel FROM Aircraft");
$gates_result = $conn->query("SELECT GateID, GateNumber, Terminal FROM Gates");
?>

<h3>Edit Flight: <?php echo htmlspecialchars($flight['FlightNumber']); ?></h3>
<?php echo $message; ?>

<form action="edit_flight.php?id=<?php echo $flight_id; ?>" method="post">
    <input type="text" name="flight_num" value="<?php echo htmlspecialchars($flight['FlightNumber']); ?>" required>
    
    <select name="airline_id" required>
        <?php while($row = $airlines_result->fetch_assoc()) {
            $selected = ($row['AirlineID'] == $flight['AirlineID']) ? 'selected' : '';
            echo "<option value='" . $row['AirlineID'] . "' $selected>" . htmlspecialchars($row['AirlineName']) . "</option>";
        } ?>
    </select>
    
    <select name="aircraft_id" required>
        <?php while($row = $aircraft_result->fetch_assoc()) {
            $selected = ($row['AircraftID'] == $flight['AircraftID']) ? 'selected' : '';
            echo "<option value='" . $row['AircraftID'] . "' $selected>" . htmlspecialchars($row['RegistrationNumber']) . "</option>";
        } ?>
    </select>
    
    <label>Scheduled Departure Time:</label>
    <input type="datetime-local" name="dep_time" value="<?php echo date('Y-m-d\TH:i', strtotime($flight['ScheduledDeparture'])); ?>" required>
    
    <label>Scheduled Arrival Time:</label>
    <input type="datetime-local" name="arr_time" value="<?php echo date('Y-m-d\TH:i', strtotime($flight['ScheduledArrival'])); ?>" required>
    
    <select name="gate_id" required>
        <?php while($row = $gates_result->fetch_assoc()) {
            $selected = ($row['GateID'] == $flight['DepartureGateID']) ? 'selected' : '';
            echo "<option value='" . $row['GateID'] . "' $selected>" . htmlspecialchars($row['GateNumber']) . "</option>";
        } ?>
    </select>

    <label>Flight Status:</label>
    <select name="status" required>
        <?php 
        $statuses = ['Scheduled', 'Boarding', 'Departed', 'In Air', 'Landed', 'Arrived', 'Cancelled', 'Delayed'];
        foreach ($statuses as $status) {
            $selected = ($status == $flight['FlightStatus']) ? 'selected' : '';
            echo "<option value='$status' $selected>$status</option>";
        }
        ?>
    </select>
    
    <input type="submit" value="Update Flight">
</form>

<?php
include 'footer.php';
?>