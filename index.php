<?php
// index.php
session_start();
include 'db_connect.php';
$message = "";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- SIGN UP LOGIC ---
    if (isset($_POST['signup'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // HASH the password
        $role = $_POST['role'];
        
        $sql = "INSERT INTO Airport_Staff (FirstName, LastName, Email, Password, StaffRole, Status, HireDate) 
                VALUES (?, ?, ?, ?, ?, 'Active', CURDATE())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);
        
        if ($stmt->execute()) {
            $message = "<div class='message success'>Account created! Please log in.</div>";
        } else {
            $message = "<div class='message error'>Error: Email may already be in use.</div>";
        }
        $stmt->close();
    }
    
    // --- LOGIN LOGIC ---
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT StaffID, FirstName, Password FROM Airport_Staff WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // VERIFY the hash
            if (password_verify($password, $row['Password'])) {
                $_SESSION['user_id'] = $row['StaffID'];
                $_SESSION['user_name'] = $row['FirstName'];
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "<div class='message error'>Invalid email or password.</div>";
            }
        } else {
            $message = "<div class='message error'>Invalid email or password.</div>";
        }
        $stmt->close();
    }
}
$conn->close();
$page = isset($_GET['page']) ? $_GET['page'] : 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airport Management Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="login-page-container">
    <div class="login-container">
        <?php echo $message; ?>
        
        <?php if ($page == 'signup'): ?>
        <form action="index.php?page=signup" method="post">
            <h2><i class="fas fa-user-plus"></i> Create Account</h2>
            <input type="hidden" name="signup" value="1">
            <div class="form-grid">
                <div><label>First Name</label><input type="text" name="first_name" required></div>
                <div><label>Last Name</label><input type="text" name="last_name" required></div>
            </div>
            <label>Email</label><input type="email" name="email" required>
            <label>Password</label><input type="password" name="password" required>
            <label>Your Role</label>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="Pilot">Pilot</option>
                <option value="Co-Pilot">Co-Pilot</option>
                <option value="Flight Attendant">Flight Attendant</option>
                <option value="Ground Staff">Ground Staff</option>
                <option value="Air Traffic Controller">Air Traffic Controller</option>
                <option value="Security Officer">Security Officer</option>
                <option value="Baggage Handler">Baggage Handler</option>
                <option value="Maintenance Engineer">Maintenance Engineer</option>
                <option value="Manager">Manager</option>
            </select>
            <input type="submit" value="Create Account">
        </form>
        <div class="toggle-link">
            <a href="index.php?page=login">Already have an account? Log In</a>
        </div>
        
        <?php else: // Default to login page ?>
        <form action="index.php?page=login" method="post">
            <h2><i class="fas fa-plane-departure"></i> Airport System</h2>
            <input type="hidden" name="login" value="1">
            <label>Email</label><input type="email" name="email" required>
            <label>Password</label><input type="password" name="password" required>
            <input type="submit" value="Log In">
        </form>
        <div class="toggle-link">
            <a href="index.php?page=signup">Don't have an account? Sign Up</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>