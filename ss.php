<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle accept or delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accept_request'])) {
        $friend_id = $_POST['friend_id'];
        // Update friend request status to 'accepted'
        $sql = "UPDATE friends SET status = 'accept' WHERE user_id = ? AND friend_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $friend_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to chat page
        header("Location: chat.php?friend_id=" . $friend_id);
        exit();
    } elseif (isset($_POST['delete_request'])) {
        $friend_id = $_POST['friend_id'];
        // Delete friend request
        $sql = "DELETE FROM friends WHERE user_id = ? AND friend_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $friend_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to friend requests page or another page if desired
        header("Location: ss.php");
        exit();
    }
}

// Get incoming friend requests
$sql = "SELECT users.id, users.full_name, users.profile_photo 
        FROM friends 
        JOIN users ON friends.user_id = users.id 
        WHERE friends.friend_id = ? AND friends.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$requests_list = "";
while ($row = $result->fetch_assoc()) {
    $photo = $row['profile_photo'] ? $row['profile_photo'] : 'default.jpg';
    $requests_list .= "<div class='request-item d-flex align-items-center mb-3 p-3 border rounded'>
                        <img src='uploads/$photo' alt='Profile Photo' class='request-photo'>
                        <div class='ms-3'>
                            <span class='fw-bold'>" . $row['full_name'] . "</span>
                            <form method='POST' class='d-inline'>
                                <input type='hidden' name='friend_id' value='" . $row['id'] . "'>
                                <button type='submit' name='accept_request' class='btn btn-primary btn-sm me-2'>Accept</button>
                                <button type='submit' name='delete_request' class='btn btn-danger btn-sm'>Delete</button>
                            </form>
                        </div>
                      </div>";
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend Requests</title>
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
        <h2>Friend Requests</h2>
        <div>
            <a href="aa.php" class="icon me-3"><i class="fas fa-search"></i></a>
            <a href="ss.php" class="icon"><i class="fas fa-bell"></i></a>
        </div>
    </div>
    <div class="requests-list">
        <?php echo $requests_list; ?>
    </div>
</div>
</body>
</html>
