<?php
$page = 'profile'; // Set the active page
include 'header.php'; // Include the header

$staff_id = $_SESSION['user_id'];
$message = "";

// --- Handle Form Submission (UPDATE logic) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get all form data
    $first_name = $_POST['first_name']; // Editable
    $last_name = $_POST['last_name'];   // Editable
    $phone = $_POST['phone'];
    $license = $_POST['license'];
    $experience_years = $_POST['experience_years']; 
    $total_flights = $_POST['total_flights']; 
    $average_rating = $_POST['average_rating']; 

    // Start the SQL query
    $update_sql = "UPDATE Airport_Staff SET FirstName = ?, LastName = ?, Phone = ?, LicenseNumber = ?, ExperienceYears = ?, TotalFlights = ?, AverageRating = ?";
    $params = [$first_name, $last_name, $phone, $license, $experience_years, $total_flights, $average_rating];
    $types = "ssssiid"; // s=string, s=string, s=string, s=string, i=int, i=int, d=decimal

    // --- Profile Picture Upload Logic ---
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/profile_pictures/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); 
        }
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $new_file_name = "profile_" . $staff_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_file_name;
        
        $uploadOk = 1;
        $imageFileType = strtolower($file_extension);

        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if($check === false) {
            $message = "<div class='message error'>File is not an image.</div>";
            $uploadOk = 0;
        }

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $message = "<div class='message error'>Sorry, only JPG, JPEG, & PNG files are allowed.</div>";
            $uploadOk = 0;
        }

        if ($uploadOk == 1 && move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $update_sql .= ", ProfilePicture = ?";
            $params[] = $new_file_name; 
            $types .= "s"; 
            $message = "<div class='message success'>Profile picture uploaded and profile updated!</div>";
        } elseif ($uploadOk == 1) {
            $message = "<div class='message error'>Sorry, there was an error uploading your file.</div>";
        }
    }

    // --- Execute the Database Update ---
    if ($message == "" || strpos($message, "success") !== false) { 
        $update_sql .= " WHERE StaffID = ?";
        $params[] = $staff_id;
        $types .= "i";
        
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param($types, ...$params); 
        
        if ($stmt_update->execute()) {
            // Update the session name if it was changed
            $_SESSION['user_name'] = $first_name; 
            if ($message == "") { 
                $message = "<div class='message success'>Profile updated successfully!</div>";
            }
        } else {
            $message = "<div class='message error'>Error updating profile: " . $stmt_update->error . "</div>";
        }
        $stmt_update->close();
    }
}

// Fetch the user's data
$sql_fetch = "SELECT * FROM Airport_Staff WHERE StaffID = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $staff_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$user = $result->fetch_assoc();
$stmt_fetch->close();

// Default profile picture
$profile_pic_path = "uploads/profile_pictures/" . e($user['ProfilePicture']);
if (empty($user['ProfilePicture']) || !file_exists($profile_pic_path)) {
    $profile_pic_path = "uploads/profile_pictures/default_profile.png"; 
}
?>

<div class="page-header">
    <h3><i class="fas fa-user-circle"></i> Your Profile</h3>
</div>

<?php echo $message; ?>

<form action="profile.php" method="post" enctype="multipart/form-data">
    <div class="profile-layout">
        <div class="profile-sidebar">
            <div class="profile-picture-container">
                <img src="<?php echo $profile_pic_path; ?>?v=<?php echo time(); ?>" alt="Profile Picture" class="profile-picture">
                <input type="file" name="profile_picture" id="profile_picture_upload" accept="image/*" style="display: none;">
                <label for="profile_picture_upload" class="action-btn btn-edit" style="margin-top: 10px; cursor: pointer; background: #64748b;">
                    <i class="fas fa-upload"></i> Change Picture
                </label>
            </div>
            
            <div class="profile-stats">
                <h4>Performance Overview</h4>
                <p><i class="fas fa-plane"></i> Total Flights: <strong><?php echo e($user['TotalFlights']); ?></strong></p>
                <p><i class="fas fa-star"></i> Avg. Rating: <strong><?php echo e(number_format($user['AverageRating'], 2)); ?> / 5.00</strong></p>
                <p><i class="fas fa-calendar-alt"></i> Joined: <strong><?php echo date('M d, Y', strtotime($user['HireDate'])); ?></strong></p>
                
                <h5 style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 15px;">Edit Performance</h5>
                <div>
                    <label>Experience Years</label>
                    <input type="number" name="experience_years" value="<?php echo e($user['ExperienceYears']); ?>" min="0" max="50">
                </div>
                 <div>
                    <label>Total Flights</label>
                    <input type="number" name="total_flights" value="<?php echo e($user['TotalFlights']); ?>" min="0">
                </div>
                <div>
                    <label>Average Rating</label>
                    <input type="number" name="average_rating" value="<?php echo e($user['AverageRating']); ?>" min="0" max="5" step="0.01">
                </div>
            </div>
        </div>

        <div class="profile-details">
            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                <div>
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo e($user['FirstName']); ?>" required>
                </div>
                <div>
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo e($user['LastName']); ?>" required>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label>Email (Username)</label>
                    <input type="email" value="<?php echo e($user['Email']); ?>" readonly style="background: #eee;">
                </div>
                
                <div>
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo e($user['Phone'] ?? ''); ?>" placeholder="e.g., +91-9876543210">
                </div>
                 <div>
                    <label>Staff Role</label>
                    <input type="text" value="<?php echo e($user['StaffRole']); ?>" readonly style="background: #eee;">
                </div>
                <div>
                    <label>Hire Date</label>
                    <input type="text" value="<?php echo e($user['HireDate']); ?>" readonly style="background: #eee;">
                </div>
                <div>
                    <label>License Number</label>
                    <input type="text" name="license" value="<?php echo e($user['LicenseNumber'] ?? ''); ?>" placeholder="e.g., CPL123456">
                </div>
            </div>
            
            <input type="submit" value="Update Profile">
        </div>
    </div>
</form>

<?php
include 'footer.php'; 
?>