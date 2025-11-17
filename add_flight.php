<?php
$page = 'view_flights'; // Keep "Manage Flights" active
include 'header.php'; 
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flight_num = $_POST['flight_num'];
    $airline_id = $_POST['airline_id'];
    $aircraft_id = $_POST['aircraft_id'];
    $dep_airport = $_POST['dep_airport'];
    $arr_airport = $_POST['arr_airport'];
    $dep_time = $_POST['dep_time'];
    $arr_time = $_POST['arr_time'];
    $gate_id = $_POST['gate_id'];
    $flight_type = $_POST['flight_type'];
    $distance = $_POST['distance'];
    $duration = $_POST['duration'];
    $ticket_price = $_POST['ticket_price'];

    $sql = "INSERT INTO Flights (FlightNumber, AirlineID, AircraftID, DepartureAirport, ArrivalAirport, 
            ScheduledDeparture, ScheduledArrival, FlightType, DepartureGateID, Distance, Duration, TicketPrice, FlightStatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled')";
    
    $stmt = $conn->prepare($sql);
    // s(tring), i(nteger), d(ouble)
    $stmt->bind_param("siisssssiiid", $flight_num, $airline_id, $aircraft_id, $dep_airport, $arr_airport, 
                      $dep_time, $arr_time, $flight_type, $gate_id, $distance, $duration, $ticket_price);

    if ($stmt->execute()) {
        $message = "<div class='message success'><i class='fas fa-check-circle'></i> Successfully added flight $flight_num!</div>";
    } else {
        $message = "<div class='message error'><i class='fas fa-exclamation-circle'></i> Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Get Dropdown Data
$airlines_result = $conn->query("SELECT AirlineID, AirlineName FROM Airlines ORDER BY AirlineName");
$aircraft_result = $conn->query("SELECT AircraftID, RegistrationNumber, AircraftModel FROM Aircraft WHERE Status = 'Active' ORDER BY RegistrationNumber");
$gates_result = $conn->query("SELECT GateID, GateNumber, Terminal FROM Gates WHERE Status = 'Available' ORDER BY Terminal, GateNumber");
?>

<div class="page-header">
    <h3><i class="fas fa-plane-departure"></i> Add New Flight</h3>
    <a href="view_flights.php" class="btn-primary" style="background-color: #64748b;"><i class="fas fa-arrow-left"></i> Back to Flights</a>
</div>

<?php echo $message; ?>

<form action="add_flight.php" method="post">
    <div class="form-grid">
        <div>
            <label>Flight Number *</label>
            <input type="text" name="flight_num" placeholder="e.g., AI 202" required>
        </div>
        
        <div>
            <label>Select Airline *</label>
            <select name="airline_id" required>
                <option value="">-- Choose Airline --</option>
                <?php while($row = $airlines_result->fetch_assoc()): ?>
                    <option value="<?php echo $row['AirlineID']; ?>">
                        <?php echo htmlspecialchars($row['AirlineName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label>Select Aircraft *</label>
            <select name="aircraft_id" required>
                <option value="">-- Choose Aircraft --</option>
                <?php while($row = $aircraft_result->fetch_assoc()): ?>
                    <option value="<?php echo $row['AircraftID']; ?>">
                        <?php echo htmlspecialchars($row['RegistrationNumber'] . ' - ' . $row['AircraftModel']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label>Departure Airport *</label>
            <input type="text" name="dep_airport" placeholder="e.g., Delhi (DEL)" required>
        </div>
        
        <div>
            <label>Arrival Airport *</label>
            <input type="text" name="arr_airport" placeholder="e.g., Mumbai (BOM)" required>
        </div>
        
        <div>
            <label>Scheduled Departure *</label>
            <input type="datetime-local" name="dep_time" required>
        </div>
        
        <div>
            <label>Scheduled Arrival *</label>
            <input type="datetime-local" name="arr_time" required>
        </div>
        
        <div>
            <label>Select Gate *</label>
            <select name="gate_id" required>
                <option value="">-- Choose Gate --</option>
                <?php while($row = $gates_result->fetch_assoc()): ?>
                    <option value="<?php echo $row['GateID']; ?>">
                        <?php echo htmlspecialchars('Gate ' . $row['GateNumber'] . ' - Terminal ' . $row['Terminal']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label>Flight Type *</label>
            <select name="flight_type" required>
                <option value="">-- Choose Type --</option>
                <option value="Domestic">Domestic</option>
                <option value="International">International</option>
            </select>
        </div>
        
        <div>
            <label>Distance (km) *</label>
            <input type="number" name="distance" placeholder="e.g., 1400" required>
        </div>
        
        <div>
            <label>Duration (minutes) *</label>
            <input type="number" name="duration" placeholder="e.g., 135" required>
        </div>
        
        <div style="grid-column: 1 / -1;">
            <label>Ticket Price (₹) *</label>
            <input type="number" step="0.01" name="ticket_price" placeholder="e.g., 5500.00" required>
        </div>
    </div>
    
    <input type="submit" value="Add Flight to Database">
</form>

<?php include 'footer.php'; ?>