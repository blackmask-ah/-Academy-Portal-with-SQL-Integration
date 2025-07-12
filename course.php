<?php
session_start();

$host = "localhost";
$user = "root";
$password = "as29";
$dbname = "BlackMask";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$latest_user_id = "";

// Get latest user_id
$result = $conn->query("SELECT user_id FROM users ORDER BY user_id DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $latest_user_id = $row['user_id'];
}
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_price = $_POST['course_price'];
    $user_id = $latest_user_id; // use the latest user_id directly

    // Check for duplicate course_id
    $check = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ?");
    $check->bind_param("s", $course_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>
                alert('❌ This Course ID already exists. Please use a different one.');
                window.location.href = 'course.php';
              </script>";
        $check->close();
        $conn->close();
        exit();
    }
    $check->close();

    // ✅ Store course info in session
    $_SESSION['course'] = [
        'course_id' => $course_id,
        'course_name' => $course_name,
        'price' => $course_price
    ];

    // Redirect to payment
    header("Location: payment.html?user_id=$user_id");
    exit();
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Course Selection</title>
  <link rel="stylesheet" href="course.css" />
</head>
<body>
  <div class="form-container">
    <h2>Select a Course</h2>
    <form action="course.php" method="post">
      <label for="course_id">Course ID
        <span style="font-size: 14px; color: red;">(e.g: 24***) Note it:</span>
      </label>
      <input type="text" id="course_id" name="course_id" required />

      <label for="course_name">Course Name:</label>
      <input type="text" id="course_name" name="course_name" required />

      <label for="course_price">Course Price:</label>
      <select id="course_price" name="course_price" required>
        <option value="">Select</option>
        <option value="50">$50</option>
        <option value="75">$75</option>
        <option value="100">$100</option>
        <option value="120">$120</option>
      </select>

      <div style="margin-top: 10px; font-size: 14px; color: green;">
        ✅ Latest User ID: <strong><?php echo htmlspecialchars($latest_user_id); ?></strong> will be used.
      </div>

      <!-- Hidden input for user_id -->
      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($latest_user_id); ?>" />

      <button type="submit">Submit</button>
    </form>
  </div>
</body>
</html>
