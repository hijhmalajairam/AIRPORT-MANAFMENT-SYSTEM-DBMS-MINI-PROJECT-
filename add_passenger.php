<?php
$page = 'passengers'; // Keep "Passengers" active
include 'header.php'; 
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $nationality = $_POST['nationality'];
    $passport = $_POST['passport'];

    $sql = "INSERT INTO Passengers (FirstName, LastName, Email, Phone, DateOfBirth, Nationality, PassportNumber)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $dob, $nationality, $passport);

    if ($stmt->execute() === TRUE) {
        $message = "<div class='message success'>Successfully added passenger $first_name $last_name!</div>";
    } else {
        $message = "<div class='message error'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>

<h3>Add a New Passenger</h3>
<?php echo $message; ?>

<form action="add_passenger.php" method="post">
    <div class="form-grid">
        <div>
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>
        <div>
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>
        <div>
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Phone Number</label>
            <input type="text" name="phone">
        </div>
        <div>
            <label>Date of Birth</label>
            <input type="date" name="dob" required>
        </div>
        <div>
            <label>Nationality</label>
            <input type="text" name="nationality" required>
        </div>
        <div style="grid-column: 1 / -1;">
            <label>Passport Number</label>
            <input type="text" name="passport" required>
        </div>
    </div>
    <input type="submit" value="Add Passenger">
</form>

<?php
include 'footer.php'; 
?>