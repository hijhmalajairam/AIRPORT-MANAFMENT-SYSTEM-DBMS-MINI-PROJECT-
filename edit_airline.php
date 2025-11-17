<?php
$page = 'manage_airlines';
include 'header.php';

$message = "";
$airline_id = $_GET['id'] ?? null;

if (!$airline_id) {
    echo "<div class='message error'>Invalid Airline ID.</div>";
    include 'footer.php';
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $airline_code = $_POST['airline_code'];
    $airline_name = $_POST['airline_name'];
    $country = $_POST['country'];
    $hq_city = $_POST['hq_city'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $founded_year = $_POST['founded_year'];
    $status = $_POST['status'];

    $sql = "UPDATE Airlines SET 
                AirlineCode = ?, 
                AirlineName = ?, 
                Country = ?, 
                HeadquartersCity = ?, 
                ContactEmail = ?, 
                ContactPhone = ?, 
                FoundedYear = ?, 
                Status = ? 
            WHERE AirlineID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssisi", $airline_code, $airline_name, $country, $hq_city, $contact_email, $contact_phone, $founded_year, $status, $airline_id);

    if ($stmt->execute()) {
        $message = "<div class='message success'>Airline updated successfully!</div>";
    } else {
        $message = "<div class='message error'>Error updating airline: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch existing airline data
$sql_fetch = "SELECT * FROM Airlines WHERE AirlineID = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $airline_id);
$stmt_fetch->execute();
$airline = $stmt_fetch->get_result()->fetch_assoc();
$stmt_fetch->close();

if (!$airline) {
    echo "<div class='message error'>Airline not found.</div>";
    include 'footer.php';
    exit;
}
?>

<div class="page-header">
    <h3><i class="fas fa-edit"></i> Edit Airline: <?php echo e($airline['AirlineName']); ?></h3>
    <a href="manage_airlines.php" class="btn-primary" style="background-color: #64748b;"><i class="fas fa-arrow-left"></i> Back to Airlines</a>
</div>

<?php echo $message; ?>

<form action="edit_airline.php?id=<?php echo $airline_id; ?>" method="post">
    <div class="form-grid">
        <div>
            <label>Airline Name *</label>
            <input type="text" name="airline_name" value="<?php echo e($airline['AirlineName']); ?>" required>
        </div>
        <div>
            <label>Airline Code *</label>
            <input type="text" name="airline_code" value="<?php echo e($airline['AirlineCode']); ?>" required>
        </div>
        <div>
            <label>Country</label>
            <input type="text" name="country" value="<?php echo e($airline['Country']); ?>">
        </div>
        <div>
            <label>Headquarters City</label>
            <input type="text" name="hq_city" value="<?php echo e($airline['HeadquartersCity']); ?>">
        </div>
        <div>
            <label>Contact Email</label>
            <input type="email" name="contact_email" value="<?php echo e($airline['ContactEmail']); ?>">
        </div>
        <div>
            <label>Contact Phone</label>
            <input type="text" name="contact_phone" value="<?php echo e($airline['ContactPhone']); ?>">
        </div>
        <div>
            <label>Founded Year</label>
            <input type="number" name="founded_year" value="<?php echo e($airline['FoundedYear']); ?>" min="1900" max="<?php echo date('Y'); ?>">
        </div>
        <div>
            <label>Status</label>
            <select name="status" required>
                <?php
                $statuses = ['Active', 'Suspended', 'Inactive'];
                foreach ($statuses as $status) {
                    $selected = ($airline['Status'] == $status) ? 'selected' : '';
                    echo "<option value='$status' $selected>$status</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <input type="submit" value="Update Airline">
</form>

<?php
include 'footer.php';
?>