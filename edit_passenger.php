<?php
$page = 'passengers'; // Keep 'Passengers' nav link active
include 'header.php';

$message = "";
$passenger_id = $_GET['id'] ?? null;

if (!$passenger_id) {
    echo "<div class='message error'>Invalid Passenger ID.</div>";
    include 'footer.php';
    exit;
}

// Handle form submission for UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $nationality = $_POST['nationality'];
    $passport = $_POST['passport'];

    $sql = "UPDATE Passengers SET 
                FirstName = ?, 
                LastName = ?, 
                Email = ?, 
                Phone = ?, 
                Nationality = ?, 
                PassportNumber = ? 
            WHERE PassengerID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $nationality, $passport, $passenger_id);

    if ($stmt->execute()) {
        $message = "<div class='message success'>Passenger updated successfully!</div>";
    } else {
        $message = "<div class='message error'>Error updating passenger: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch existing passenger data
$sql_fetch = "SELECT * FROM Passengers WHERE PassengerID = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $passenger_id);
$stmt_fetch->execute();
$passenger = $stmt_fetch->get_result()->fetch_assoc();
$stmt_fetch->close();

if (!$passenger) {
    echo "<div class='message error'>Passenger not found.</div>";
    include 'footer.php';
    exit;
}
?>

<div class="page-header">
    <h3><i class="fas fa-edit"></i> Edit Passenger: <?php echo e($passenger['FirstName']); ?> <?php echo e($passenger['LastName']); ?></h3>
    <a href="manage_passengers.php" class="btn-primary" style="background-color: #64748b;"><i class="fas fa-arrow-left"></i> Back to Passengers</a>
</div>

<?php echo $message; ?>

<form action="edit_passenger.php?id=<?php echo $passenger_id; ?>" method="post">
    <div class="form-grid">
        <div>
            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo e($passenger['FirstName']); ?>" required>
        </div>
        <div>
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo e($passenger['LastName']); ?>" required>
        </div>
        <div>
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo e($passenger['Email']); ?>" required>
        </div>
        <div>
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo e($passenger['Phone']); ?>">
        </div>
        <div>
            <label>Nationality</label>
            <input type="text" name="nationality" value="<?php echo e($passenger['Nationality']); ?>" required>
        </div>
        <div>
            <label>Passport Number</label>
            <input type="text" name="passport" value="<?php echo e($passenger['PassportNumber']); ?>" required>
        </div>
    </div>
    <input type="submit" value="Update Passenger">
</form>

<?php
include 'footer.php';
?>