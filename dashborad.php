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
            echo "Friend request sent.";
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

<form method="POST">
    Send Friend Request to: <input type="text" name="friend_username" required><br>
    <input type="submit" name="send_request" value="Send Request">
</form>
    