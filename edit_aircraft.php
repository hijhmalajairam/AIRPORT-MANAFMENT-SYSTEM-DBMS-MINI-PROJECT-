<?php
$page = 'aircraft'; // Keep 'Aircraft' nav link active
include 'header.php';

$message = "";
$aircraft_id = $_GET['id'] ?? null;

if (!$aircraft_id) {
    echo "<div class='message error'>Invalid Aircraft ID.</div>";
    include 'footer.php';
    exit;
}

// Handle form submission for UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_num = $_POST['reg_num'];
    $airline_id = $_POST['airline_id'];
    $model = $_POST['model'];
    $manufacturer = $_POST['manufacturer'];
    $seats = $_POST['seats'];
    $status = $_POST['status'];

    $sql = "UPDATE Aircraft SET 
                RegistrationNumber = ?, 
                AirlineID = ?, 
                AircraftModel = ?, 
                Manufacturer = ?, 
                TotalSeats = ?, 
                Status = ? 
            WHERE AircraftID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissisi", $reg_num, $airline_id, $model, $manufacturer, $seats, $status, $aircraft_id);

    if ($stmt->execute()) {
        $message = "<div class='message success'>Aircraft updated successfully!</div>";
    } else {
        $message = "<div class='message error'>Error updating aircraft: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch existing aircraft data for the form
$sql_fetch = "SELECT * FROM Aircraft WHERE AircraftID = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $aircraft_id);
$stmt_fetch->execute();
$aircraft = $stmt_fetch->get_result()->fetch_assoc();
$stmt_fetch->close();

if (!$aircraft) {
    echo "<div class='message error'>Aircraft not found.</div>";
    include 'footer.php';
    exit;
}

// Get airlines for the dropdown
$airlines_result = $conn->query("SELECT AirlineID, AirlineName FROM Airlines ORDER BY AirlineName");
?>

<div class="page-header">
    <h3><i class="fas fa-edit"></i> Edit Aircraft: <?php echo e($aircraft['RegistrationNumber']); ?></h3>
    <a href="manage_aircraft.php" class="btn-primary" style="background-color: #64748b;"><i class="fas fa-arrow-left"></i> Back to Aircraft</a>
</div>

<?php echo $message; ?>

<form action="edit_aircraft.php?id=<?php echo $aircraft_id; ?>" method="post">
    <div class="form-grid">
        <div>
            <label>Registration Number</label>
            <input type="text" name="reg_num" value="<?php echo e($aircraft['RegistrationNumber']); ?>" required>
        </div>
        <div>
            <label>Aircraft Model</label>
            <input type="text" name="model" value="<?php echo e($aircraft['AircraftModel']); ?>" required>
        </div>
        <div>
            <label>Manufacturer</label>
            <input type="text" name="manufacturer" value="<?php echo e($aircraft['Manufacturer']); ?>" required>
        </div>
        <div>
            <label>Total Seats</label>
            <input type="number" name="seats" value="<?php echo e($aircraft['TotalSeats']); ?>" required>
        </div>
        <div>
            <label>Airline</label>
            <select name="airline_id" required>
                <option value="">-- Select Airline --</option>
                <?php while($row = $airlines_result->fetch_assoc()): ?>
                    <?php $selected = ($row['AirlineID'] == $aircraft['AirlineID']) ? 'selected' : ''; ?>
                    <option value="<?php echo $row['AirlineID']; ?>" <?php echo $selected; ?>>
                        <?php echo e($row['AirlineName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Status</label>
            <select name="status" required>
                <?php
                $statuses = ['Active', 'Maintenance', 'Retired'];
                foreach ($statuses as $status) {
                    $selected = ($aircraft['Status'] == $status) ? 'selected' : '';
                    echo "<option value='$status' $selected>$status</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <input type="submit" value="Update Aircraft">
</form>

<?php
include 'footer.php';
?>