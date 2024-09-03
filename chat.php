<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Display Friends with profile photos
$sql = "SELECT users.id, users.full_name, users.profile_photo 
        FROM friends 
        JOIN users ON friends.friend_id = users.id 
        WHERE friends.user_id = ? AND friends.status = 'accept'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$friends_list = "";
while ($row = $result->fetch_assoc()) {
    $photo = $row['profile_photo'] ? $row['profile_photo'] : 'default.jpg'; // Default photo if none
    $friends_list .= "<a href='chat.php?friend_id=" . $row['id'] . "' class='friend-item'>
                        <img src='uploads/$photo' alt='Profile Photo' class='friend-photo'>
                        <span>" . $row['full_name'] . "</span>
                      </a>";
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .chat-wrapper {
            display: flex;
            height: 90vh;
        }
        .friends-list {
            width: 25%;
            padding: 10px;
            border-right: 1px solid #ddd;
            overflow-y: auto;
            position: relative;
        }
        .chat-area {
            width: 75%;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }
        .friend-item {
            display: flex;
            align-items: center;
            padding: 10px;
            text-decoration: none;
            color: black;
        }
        .friend-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-header {
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .chat-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-header h4 {
            margin: 0;
        }
        .message {
            max-width: 70%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
            position: relative;
            word-wrap: break-word;
            display: flex;
            flex-direction: column;
        }
        .message.sent {
            background-color: #d1ffd1;
            align-self: flex-end;
        }
        .message.received {
            background-color: skyblue;
            align-self: flex-start;
        }
        .message img.profile-photo {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }
        .timestamp {
            font-size: 0.75em;
            color: #666;
            margin-top: 5px;
            align-self: flex-end;
        }
        .message.received .timestamp {
            align-self: flex-start;
        }
        .form-container {
            margin-top: auto;
        }
        .input-group input[type="text"] {
            border-radius: 20px 0 0 20px;
        }
        .input-group button {
            border-radius: 0 20px 20px 0;
        }
        .icon {
            cursor: pointer;
            font-size: 1.5em;
            margin-left: 10px;
        }
        .icon.notification-icon {
            margin-left: 20px;
        }
    </style>
</head>
<body>
<div class="chat-wrapper">
    <div class="friends-list">
        <div class="d-flex justify-content-between align-items-center">
        <a href="chat.php" class="icon"><i class="fas fa-arrow-left"></i></a>
            <h4>Friends List</h4>
            <div>
            <a href="aa.php" class="icon me-3"><i class="fas fa-search"></i></a>
            <a href="ss.php" class="icon"><i class="fas fa-bell"></i></a>
            </div>
        </div>
        <?php echo $friends_list; ?>
    </div>
    <div class="chat-area">
        <?php
        if (isset($_GET['friend_id'])) {
            $friend_id = $_GET['friend_id'];

            // Get friend's details
            $sql = "SELECT full_name, profile_photo FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $friend_id);
            $stmt->execute();
            $friend_result = $stmt->get_result();
            $friend = $friend_result->fetch_assoc();
            $friend_photo = $friend['profile_photo'] ? $friend['profile_photo'] : 'default.jpg';

            // Display friend's chat header
            echo "<div class='chat-header'>
                    <img src='uploads/$friend_photo' alt='Profile Photo'>
                    <h4>" . $friend['full_name'] . "</h4>
                  </div>";

            // Display Chat Messages
            $sql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                // Get profile photos
                $sender_sql = "SELECT profile_photo FROM users WHERE id = ?";
                $sender_stmt = $conn->prepare($sender_sql);
                $sender_stmt->bind_param("i", $row['sender_id']);
                $sender_stmt->execute();
                $sender_result = $sender_stmt->get_result();
                $sender_photo = $sender_result->fetch_assoc()['profile_photo'];

                $message_class = $row['sender_id'] == $user_id ? 'sent' : 'received';
                $timestamp = date('h:i A', strtotime($row['timestamp'])); // Format timestamp

                echo "<div class='message $message_class'>";
                if ($row['sender_id'] != $user_id) {
                    echo "<img src='uploads/$sender_photo' class='profile-photo' alt='Profile Photo'>";
                }
                echo "<span>" . $row['message'] . "</span>";
                echo "<div class='timestamp'>$timestamp</div>";
                echo "</div>";
            }
            $stmt->close();
        }
        ?>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="receiver_id" value="<?php echo isset($friend_id) ? $friend_id : ''; ?>">
                <div class="input-group">
                    <input type="text" class="form-control" name="message" placeholder="Type your message..." required>
                    <button class="btn btn-primary" type="submit">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
