<?php
$page = 'assign_crew';
include 'header.php';

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flight_id = $_POST['flight_id'];
    $staff_id = $_POST['staff_id'];
    $assignment_role = $_POST['assignment_role'];

    // Check if the staff member is already assigned to this flight
    $check_sql = "SELECT COUNT(*) FROM Flight_Crew WHERE FlightID = ? AND StaffID = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ii", $flight_id, $staff_id);
    $stmt_check->execute();
    $already_assigned = $stmt_check->get_result()->fetch_row()[0];
    $stmt_check->close();

    if ($already_assigned > 0) {
        $message = "<div class='message error'>This staff member is already assigned to this flight.</div>";
    } else {
        $sql = "INSERT INTO Flight_Crew (FlightID, StaffID, AssignmentRole, CheckInStatus) VALUES (?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $flight_id, $staff_id, $assignment_role);

        if ($stmt->execute()) {
            $message = "<div class='message success'>Crew assigned to flight successfully!</div>";
        } else {
            $message = "<div class='message error'>Error assigning crew: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Fetch all active flights for the dropdown
$flights_sql = "SELECT FlightID, FlightNumber, DepartureAirport, ArrivalAirport, ScheduledDeparture 
                FROM Flights 
                WHERE FlightStatus IN ('Scheduled', 'Delayed') 
                ORDER BY ScheduledDeparture ASC";
$flights_result = $conn->query($flights_sql);

// Fetch all active staff members for the dropdown
$staff_sql = "SELECT StaffID, FirstName, LastName, StaffRole, LicenseNumber 
              FROM Airport_Staff 
              WHERE Status = 'Active' 
              ORDER BY StaffRole, FirstName, LastName";
$staff_result = $conn->query($staff_sql);

// Fetch current assignments to display
$assignments_sql = "SELECT FC.CrewAssignmentID, F.FlightNumber, A.AirlineName, S.FirstName, S.LastName, S.StaffRole, FC.AssignmentRole, FC.CheckInStatus
                    FROM Flight_Crew FC
                    JOIN Flights F ON FC.FlightID = F.FlightID
                    JOIN Airport_Staff S ON FC.StaffID = S.StaffID
                    JOIN Airlines A ON F.AirlineID = A.AirlineID
                    ORDER BY F.ScheduledDeparture DESC";
$assignments_result = $conn->query($assignments_sql);
?>

<div class="page-header">
    <h3><i class="fas fa-users-cog"></i> Staff Portal: Assign Crew to Flight</h3>
</div>

<?php echo $message; ?>

<div class="form-section">
    <h4>Assign New Crew Member</h4>
    <form action="assign_crew.php" method="post">
        <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
            <div>
                <label for="flight_id">Select Flight *</label>
                <select id="flight_id" name="flight_id" required>
                    <option value="">-- Select a Scheduled Flight --</option>
                    <?php while ($flight = $flights_result->fetch_assoc()): ?>
                        <option value="<?php echo e($flight['FlightID']); ?>">
                            <?php echo e($flight['FlightNumber'] . ' - ' . $flight['DepartureAirport'] . ' to ' . $flight['ArrivalAirport'] . ' (' . date('M d, H:i', strtotime($flight['ScheduledDeparture'])) . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="staff_id">Select Crew Member *</label>
                <select id="staff_id" name="staff_id" required>
                    <option value="">-- Select an Active Crew Member --</option>
                    <?php while ($staff = $staff_result->fetch_assoc()): ?>
                        <option value="<?php echo e($staff['StaffID']); ?>">
                            <?php echo e($staff['FirstName'] . ' ' . $staff['LastName'] . ' (' . $staff['StaffRole'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="assignment_role">Assignment Role *</label>
                <select id="assignment_role" name="assignment_role" required>
                    <option value="Captain">Captain</option>
                    <option value="First Officer">First Officer</option>
                    <option value="Senior Flight Attendant">Senior Flight Attendant</option>
                    <option value="Flight Attendant">Flight Attendant</option>
                    <option value="Ground Staff">Ground Staff</option>
                </select>
            </div>
        </div>
        <input type="submit" value="Assign Crew">
    </form>
</div>

<h3 style="margin-top: 40px;">Current Crew Assignments</h3>
<table class="clean-table">
    <thead>
        <tr>
            <th>Flight No.</th>
            <th>Airline</th>
            <th>Staff Name</th>
            <th>Staff Role</th>
            <th>Assigned Role</th>
            <th>Check-In Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($assignments_result->num_rows > 0): ?>
            <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo e($assignment['FlightNumber']); ?></td>
                    <td><?php echo e($assignment['AirlineName']); ?></td>
                    <td><?php echo e($assignment['FirstName'] . ' ' . $assignment['LastName']); ?></td>
                    <td><?php echo e($assignment['StaffRole']); ?></td>
                    <td><?php echo e($assignment['AssignmentRole']); ?></td>
                    <td>
                        <span class="fc-status <?php echo e(str_replace(' ', '.', $assignment['CheckInStatus'])); ?>">
                            <?php echo e($assignment['CheckInStatus']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="action-btn btn-danger btn-sm" onclick="if(confirm('Are you sure you want to remove this assignment?')) { window.location.href='remove_crew_assignment.php?id=<?php echo $assignment['CrewAssignmentID']; ?>'; }">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No crew assignments found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>