<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5; /* Light grey background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            width: 360px;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .login-card h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .login-card .form-control {
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: none;
            margin-bottom: 15px;
        }
        .login-card .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
        }
        .login-card .btn {
            width: 100%;
            border-radius: 5px;
            padding: 10px;
            background-color: #1877f2;
            border: none;
            color: #ffffff;
            font-size: 16px;
        }
        .login-card .btn:hover {
            background-color: #165eab;
        }
        .login-card .signup-link {
            text-align: center;
            display: block;
            margin-top: 15px;
            color: #1877f2;
            text-decoration: none;
        }
        .login-card .signup-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    include 'db.php';
    session_start();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT id, password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                header("Location: aa.php");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Invalid credentials.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>User not found.</div>";
        }

        $stmt->close();
    }
    ?>
    <div class="login-card">
        <h2>Login</h2>
        <form method="POST">
            <input type="text" class="form-control" name="username" placeholder="Username" required>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
            <a href="signup.php" class="signup-link">Sign up?</a>
        </form>
    </div>
</body>
</html>
