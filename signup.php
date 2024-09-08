<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5; /* Light grey background similar to Facebook */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-card {
            width: 360px;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .register-card h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .register-card .form-control {
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: none;
            margin-bottom: 15px;
        }
        .register-card .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
        }
        .register-card .btn-primary {
            width: 100%;
            border-radius: 5px;
            padding: 10px;
            background-color: #1877f2;
            border: none;
            color: #ffffff;
            font-size: 16px;
        }
        .register-card .btn-primary:hover {
            background-color: #165eab;
        }
        .register-card .file-input {
            margin-bottom: 15px;
        }
        .register-card .signup-link {
            text-align: center;
            display: block;
            margin-top: 15px;
            color: #1877f2;
            text-decoration: none;
        }
        .register-card .signup-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    include 'db.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $full_name = $_POST['full_name'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Handle file upload
        $profile_photo = null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png'];
            $file_type = $_FILES['profile_photo']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'uploads/';
                $file_name = basename($_FILES['profile_photo']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Move the uploaded file to the server directory
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                    $profile_photo = $file_name;
                } else {
                    echo "<div class='alert alert-danger'>Error uploading the file.</div>";
                    exit();
                }
            } else {
                echo "<div class='alert alert-danger'>Invalid file type. Only JPG and PNG are allowed.</div>";
                exit();
            }
        }

        $sql = "INSERT INTO users (full_name, username, password, profile_photo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $full_name, $username, $password, $profile_photo);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Registration successful!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
    ?>
    <div class="register-card">
        <h2>Register</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
            <input type="text" class="form-control" name="username" placeholder="Username" required>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <input type="file" class="form-control file-input" name="profile_photo" accept="image/jpeg, image/png">
             <button class="button">Submit</button>
            <a href="login.php" class="signup-link">Already have an account?</a>
        </form>
    </div>
</body>
</html>
