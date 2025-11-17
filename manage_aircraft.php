<?php
$page = 'aircraft';
include 'header.php';
$message = "";

// Check for session messages from delete.php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (isset($_POST['add_aircraft'])) {
    $reg = $_POST['reg_num'];
    $airline_id = $_POST['airline_id'];
    $model = $_POST['model'];
    $manufacturer = $_POST['manufacturer'];
    $seats = $_POST['seats'];

    $sql = "INSERT INTO Aircraft (RegistrationNumber, AirlineID, AircraftModel, Manufacturer, TotalSeats, Status) 
            VALUES (?, ?, ?, ?, ?, 'Active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissi", $reg, $airline_id, $model, $manufacturer, $seats);
    
    if ($stmt->execute()) {
        $message = "<div class='message success'>Aircraft $reg added successfully!</div>";
    } else {
        $message = "<div class='message error'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

$airlines_result = $conn->query("SELECT AirlineID, AirlineName FROM Airlines");
?>

<div class="page-header">
    <h3><i class="fas fa-plane"></i> Manage Aircraft Fleet</h3>
</div>

<div class="form-section">
    <h4>Add New Aircraft</h4>
    <?php echo $message; ?>
    <form action="manage_aircraft.php" method="post">
        <input type="hidden" name="add_aircraft" value="1">
        <div class="form-grid">
            <div>
                <label>Registration Number (e.g., VT-ANL)</label>
                <input type="text" name="reg_num" required>
            </div>
            <div>
                <label>Aircraft Model (e.g., Boeing 787-8)</label>
                <input type="text" name="model" required>
            </div>
            <div>
                <label>Manufacturer (e.g., Boeing)</label>
                <input type="text" name="manufacturer" required>
            </div>
            <div>
                <label>Total Seats</label>
                <input type="number" name="seats" required>
            </div>
            <div style="grid-column: 1 / -1;">
                <label>Airline</label>
                <select name="airline_id" required>
                    <option value="">-- Select Airline --</option>
                    <?php while($row = $airlines_result->fetch_assoc()) { echo "<option value='" . $row['AirlineID'] . "'>" . htmlspecialchars($row['AirlineName']) . "</option>"; } ?>
                </select>
            </div>
        </div>
        <input type="submit" value="Add Aircraft">
    </form>
</div>

<h3 style="margin-top: 40px;">Current Fleet</h3>
<table class="clean-table">
    <thead>
        <tr>
            <th>Registration</th>
            <th>Airline</th>
            <th>Model</th>
            <th>Manufacturer</th>
            <th>Seats</th>
            <th>Status</th>
            <th>Actions</th> </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT A.*, AL.AirlineName FROM Aircraft A JOIN Airlines AL ON A.AirlineID = AL.AirlineID ORDER BY A.RegistrationNumber";
        $result = $conn->query($sql);
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . e($row['RegistrationNumber']) . "</td>";
            echo "<td>" . e($row['AirlineName']) . "</td>";
            echo "<td>" . e($row['AircraftModel']) . "</td>";
            echo "<td>" . e($row['Manufacturer']) . "</td>";
            echo "<td>" . e($row['TotalSeats']) . "</td>";
            echo "<td>
                    <span class='fc-status " . e($row['Status']) . " Active'>" . e($row['Status']) . "</span>
                  </td>";
            // NEW ACTIONS CELL
            echo "<td>
                    <div class='action-buttons' style='gap: 5px;'>
                        <a href='edit_aircraft.php?id=" . $row['AircraftID'] . "' class='action-btn btn-edit'>
                            <i class='fas fa-edit'></i> Edit
                        </a>
                        <a href='delete_aircraft.php?id=" . $row['AircraftID'] . "' 
                           class='action-btn btn-delete' 
                           onclick=\"return confirm('Are you sure you want to delete aircraft " . e($row['RegistrationNumber']) . "? This will fail if it is assigned to any flights.');\">
                            <i class='fas fa-trash'></i> Delete
                        </a>
                    </div>
                  </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>