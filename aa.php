<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .friend_id{
            margin-left:50px;
        }
    </style>
</head>
<body>
    
<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Send Friend Request
if (isset($_POST['send_request'])) {
    $friend_username = $_POST['friend_username'];
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $friend_username);
    $stmt->execute();
    $stmt->bind_result($friend_id);
    $stmt->fetch();
    $stmt->close();

    if ($friend_id) {
        $sql = "INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $friend_id);
        if ($stmt->execute()) {
            header("Location: chat.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "User not found.";
    }
}

// Display Friends List
$sql = "SELECT users.username FROM friends JOIN users ON friends.friend_id = users.id WHERE friends.user_id = ? AND friends.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "Friends List:<br>";
while ($row = $result->fetch_assoc()) {
    echo $row['username'] . "<br>";
}
$stmt->close();
?>
    Send Friend Request to: <input type="text" name="friend_username" required><br>
    <input type="submit" name="send_request" value="Send Request">
</form>
    



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Friends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f5f6f7;
        }
        .container {
            max-width: 600px;
            margin-top: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            flex-grow: 1;
            text-align: center;
            margin: 0;
        }
        .header .icon {
            cursor: pointer;
            font-size: 1.5rem;
            color: #007bff;
        }
        .header .icon:hover {
            color: #0056b3;
        }
        .request-item {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .request-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <a href="chat.php" class="icon"><i class="fas fa-arrow-left"></i></a>
        <h2>Search</h2>
        <div>
            <a href="aa.php" class="icon me-3"><i class="fas fa-search"></i></a>
            <a href="ss.php" class="icon"><i class="fas fa-bell"></i></a>
        </div>
    </div>
    
<form method="POST">

    <div class="Search">
    <input type="text" name="friend_username" placeholder="search" required>
    <input type="submit" name="send_request " class="btn btn-primary btn-sm me-2" value="Send Request">
    </div>
</form>
</div>
</body>
</html>
