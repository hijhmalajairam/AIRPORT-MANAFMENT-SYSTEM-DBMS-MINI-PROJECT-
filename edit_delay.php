<?php
$page = 'view_delays'; // Keep "Delays" link active
include 'header.php';

$message = "";
$delay_id = $_GET['id'] ?? null;

if (!$delay_id) {
    echo "<div class='message error'>Invalid Delay ID.</div>";
    include 'footer.php';
    exit;
}

// --- Handle Form Submission (UPDATE logic) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delay_reason = $_POST['delay_reason'];
    $delay_duration = $_POST['delay_duration'];
    $flight_id = $_POST['flight_id']; // Get hidden flight_id

    // SQL to update the delay and recalculate new times
    $sql_update = "UPDATE Flight_Delays FD
                   JOIN Flights F ON FD.FlightID = F.FlightID
                   SET 
                       FD.DelayReason = ?,
                       FD.DelayDuration = ?,
                       FD.NewDepartureTime = DATE_ADD(F.ScheduledDeparture, INTERVAL ? MINUTE),
                       FD.NewArrivalTime = DATE_ADD(F.ScheduledArrival, INTERVAL ? MINUTE)
                   WHERE 
                       FD.DelayID = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("siiii", $delay_reason, $delay_duration, $delay_duration, $delay_duration, $delay_id);

    if ($stmt->execute()) {
        $message = "<div class='message success'>Delay updated successfully!</div>";
    } else {
        $message = "<div class='message error'>Error updating delay: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- Fetch existing data for the form ---
$sql_fetch = "SELECT FD.*, F.FlightNumber, F.FlightID
              FROM Flight_Delays FD
              JOIN Flights F ON FD.FlightID = F.FlightID
              WHERE FD.DelayID = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $delay_id);
$stmt_fetch->execute();
$delay = $stmt_fetch->get_result()->fetch_assoc();
$stmt_fetch->close();

if (!$delay) {
    echo "<div class='message error'>Delay not found.</div>";
    include 'footer.php';
    exit;
}
?>

<div class="page-header">
    <h3><i class="fas fa-edit"></i> Edit Delay for Flight <?php echo e($delay['FlightNumber']); ?></h3>
    <a href="view_delays.php" class="btn-primary" style="background-color: #64748b;"><i class="fas fa-arrow-left"></i> Back to Delays</a>
</div>

<?php echo $message; ?>

<form action="edit_delay.php?id=<?php echo $delay_id; ?>" method="post">
    <input type="hidden" name="flight_id" value="<?php echo e($delay['FlightID']); ?>">
    
    <div class="form-grid" style="grid-template-columns: 1fr;">
        <div>
            <label>Reason for Delay *</label>
            <select name="delay_reason" required>
                <?php
                $reasons = ['Weather', 'Technical Issue', 'Air Traffic', 'Crew Unavailable', 'Late Arrival', 'Security Concern', 'Operational', 'Other'];
                foreach ($reasons as $reason) {
                    $selected = ($delay['DelayReason'] == $reason) ? 'selected' : '';
                    echo "<option value='$reason' $selected>$reason</option>";
                }
                ?>
            </select>
        </div>
        
        <div>
            <label>Delay Duration (in minutes) *</label>
            <input type="number" name="delay_duration" min="1" value="<?php echo e($delay['DelayDuration']); ?>" required>
        </div>
    </div>
    
    <input type="submit" value="Update Delay">
</form>

<?php
include 'footer.php';
?>