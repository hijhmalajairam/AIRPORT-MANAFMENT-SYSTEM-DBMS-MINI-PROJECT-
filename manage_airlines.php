<?php
$page = 'manage_airlines'; // Set the active page
include 'header.php'; // Include the header

$message = "";
// Check for messages from delete_airline.php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle form submission for adding a new airline
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_airline'])) {
    $airline_code = $_POST['airline_code'];
    $airline_name = $_POST['airline_name'];
    $country = $_POST['country'];
    $hq_city = $_POST['hq_city'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $founded_year = $_POST['founded_year'];

    $sql = "INSERT INTO Airlines (AirlineCode, AirlineName, Country, HeadquartersCity, ContactEmail, ContactPhone, FoundedYear, Status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $airline_code, $airline_name, $country, $hq_city, $contact_email, $contact_phone, $founded_year);

    if ($stmt->execute()) {
        $message = "<div class='message success'>Airline added successfully!</div>";
    } else {
        $message = "<div class='message error'>Error adding airline: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch all airlines to display
$airlines_sql = "SELECT * FROM Airlines ORDER BY AirlineName";
$airlines_result = $conn->query($airlines_sql);
?>

<div class="page-header">
    <h3><i class="fas fa-building"></i> Manage Airlines</h3>
</div>

<?php echo $message; ?>

<div class="form-section">
    <h4>Add New Airline</h4>
    <form action="manage_airlines.php" method="post">
        <input type="hidden" name="add_airline" value="1">
        <div class="form-grid">
            <div>
                <label for="airline_name">Airline Name *</label>
                <input type="text" id="airline_name" name="airline_name" required>
            </div>
            <div>
                <label for="airline_code">Airline Code (e.g., AI, 6E) *</label>
                <input type="text" id="airline_code" name="airline_code" maxlength="10" required>
            </div>
            <div>
                <label for="country">Country</label>
                <input type="text" id="country" name="country">
            </div>
            <div>
                <label for="hq_city">Headquarters City</label>
                <input type="text" id="hq_city" name="hq_city">
            </div>
            <div>
                <label for="contact_email">Contact Email</label>
                <input type="email" id="contact_email" name="contact_email">
            </div>
            <div>
                <label for="contact_phone">Contact Phone</label>
                <input type="text" id="contact_phone" name="contact_phone">
            </div>
            <div>
                <label for="founded_year">Founded Year</label>
                <input type="number" id="founded_year" name="founded_year" min="1900" max="<?php echo date('Y'); ?>">
            </div>
        </div>
        <input type="submit" value="Add Airline">
    </form>
</div>

<h3 style="margin-top: 40px;">Existing Airlines</h3>
<table class="clean-table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Country</th>
            <th>HQ City</th>
            <th>Founded</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($airlines_result->num_rows > 0): ?>
            <?php while ($airline = $airlines_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo e($airline['AirlineCode']); ?></td>
                    <td><?php echo e($airline['AirlineName']); ?></td>
                    <td><?php echo e($airline['Country']); ?></td>
                    <td><?php echo e($airline['HeadquartersCity']); ?></td>
                    <td><?php echo e($airline['FoundedYear']); ?></td>
                    <td>
                        <span class="fc-status <?php echo e(str_replace(' ', '.', $airline['Status'])); ?> Active">
                            <?php echo e($airline['Status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="gap: 5px;">
                            <a href="edit_airline.php?id=<?php echo $airline['AirlineID']; ?>" class="action-btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_airline.php?id=<?php echo $airline['AirlineID']; ?>" 
                               class="action-btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this airline? This will only work if no flights or aircraft are assigned to it.');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No airlines found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>