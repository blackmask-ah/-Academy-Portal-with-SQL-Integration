<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "as29";
$dbname = "BlackMask";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper functions
function getBrowser() {
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($ua, 'Firefox') !== false) return "Firefox";
    elseif (strpos($ua, 'Chrome') !== false) return "Chrome";
    elseif (strpos($ua, 'Safari') !== false) return "Safari";
    elseif (strpos($ua, 'Edge') !== false) return "Edge";
    elseif (strpos($ua, 'MSIE') !== false || strpos($ua, 'Trident/7') !== false) return "Internet Explorer";
    else return "Unknown";
}

function getOS() {
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/linux/i', $ua)) return "Linux";
    elseif (preg_match('/macintosh|mac os x/i', $ua)) return "Mac";
    elseif (preg_match('/windows|win32/i', $ua)) return "Windows";
    else return "Unknown";
}

// Only run on POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $cnic = trim($_POST['cnic']);
    $phone = trim($_POST['phone']);
    $raw_password = trim($_POST['password']);
    $screen_size = $_POST['screen_size'] ?? 'Unknown';
    $timezone = $_POST['timezone'] ?? 'Unknown';

    $ip = $_SERVER['REMOTE_ADDR'];
    $os = getOS();
    $browser = getBrowser();

    if (empty($name) || empty($email) || empty($cnic) || empty($phone) || empty($raw_password)) {
        die("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    // ✅ Save user data in session instead of database
    $_SESSION['user'] = [
        'name' => $name,
        'email' => $email,
        'cnic' => $cnic,
        'phone' => $phone,
        'password' => $hashed_password,
        'ip_address' => $ip,
        'os' => $os,
        'browser' => $browser,
        'screen_size' => $screen_size,
        'timezone' => $timezone
    ];

    header("Location: course.html");
    exit();
}

$conn->close();
?>
