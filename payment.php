<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "as29";
$dbname = "BlackMask";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Fetch latest user_id and corresponding course_id
    $latest = [];

    $userResult = $conn->query("SELECT user_id FROM users ORDER BY user_id DESC LIMIT 1");
    if ($userResult && $row = $userResult->fetch_assoc()) {
        $latest['user_id'] = $row['user_id'];

        $uid = $latest['user_id'];
        $courseResult = $conn->query("SELECT course_id FROM courses WHERE user_id = $uid ORDER BY course_id DESC LIMIT 1");
        if ($courseResult && $crow = $courseResult->fetch_assoc()) {
            $latest['course_id'] = $crow['course_id'];
        } else {
            $latest['course_id'] = "";
        }
    } else {
        $latest['user_id'] = "0";
        $latest['course_id'] = "";
    }

    echo json_encode($latest);
    $conn->close();
    exit();
}

// POST: Handle Payment Form
$payment_id = $conn->real_escape_string($_POST['payment_id']);
$payment_method = $conn->real_escape_string($_POST['payment_method']);
$account_type = $conn->real_escape_string($_POST['account_type']);

// ✅ Get user and course info from session
$user = $_SESSION['user'] ?? null;
$course = $_SESSION['course'] ?? null;

if (!$user || !$course) {
    echo "❌ Session expired. Please start from registration.";
    exit();
}

$conn->begin_transaction();

try {
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, cnic, phone, password, ip_address, os, browser, screen_size, timezone)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $user['name'], $user['email'], $user['cnic'], $user['phone'], $user['password'], $user['ip_address'], $user['os'], $user['browser'], $user['screen_size'], $user['timezone']);
    $stmt->execute();
    $user_id = $conn->insert_id;
    $stmt->close();

    // Insert course
    $stmt = $conn->prepare("INSERT INTO courses (course_id, course_name, price, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $course['course_id'], $course['course_name'], $course['price'], $user_id);
    $stmt->execute();
    $stmt->close();

    // Insert payment
    $stmt = $conn->prepare("INSERT INTO payments (payment_id, course_id, payment_method, account_type, user_id) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $payment_id, $course['course_id'], $payment_method, $account_type, $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo "✅ Payment recorded successfully.";

    session_destroy(); // clear stored session data
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Transaction failed: " . $e->getMessage();
}

$conn->close();
?>
