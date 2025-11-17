<?php
$page = 'passengers';
include 'header.php';

$message = "";
// Check for messages from delete_passenger.php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<div class="page-header">
    <h3><i class="fas fa-users"></i> Manage All Passengers</h3>
    <a href="add_passenger.php" class="btn-primary"><i class="fas fa-user-plus"></i> Add New Passenger</a>
</div>

<?php echo $message; // Show success/error messages ?>

<table class="clean-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Nationality</th>
            <th>Passport No.</th>
            <th>Actions</th> </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT PassengerID, FirstName, LastName, Email, Phone, Nationality, PassportNumber FROM Passengers ORDER BY PassengerID";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["PassengerID"] . "</td>";
                echo "<td>" . e($row["FirstName"] . " " . $row["LastName"]) . "</td>";
                echo "<td>" . e($row["Email"]) . "</td>";
                echo "<td>" . e($row["Phone"]) . "</td>";
                echo "<td>" . e($row["Nationality"]) . "</td>";
                echo "<td>" . e($row["PassportNumber"]) . "</td>";
                // NEW ACTIONS CELL
                echo "<td>
                        <div class='action-buttons' style='gap: 5px;'>
                            <a href='edit_passenger.php?id=" . $row['PassengerID'] . "' class='action-btn btn-edit'>
                                <i class='fas fa-edit'></i> Edit
                            </a>
                            <a href='delete_passenger.php?id=" . $row['PassengerID'] . "' 
                               class='action-btn btn-delete' 
                               onclick=\"return confirm('Are you sure you want to delete this passenger? This will fail if they have any active bookings.');\">
                                <i class='fas fa-trash'></i> Delete
                            </a>
                        </div>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No passengers found</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>