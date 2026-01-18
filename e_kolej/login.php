<?php
session_start();
include 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST['user_id']);
    $password = trim($_POST['password']);

    if (empty($user_id) || empty($password)) {
        echo "<script>alert('Please enter both User ID and Password.'); window.location.href='login.php';</script>";
        exit();
    }

    // Check if user is a student
    $stmt = $conn->prepare("SELECT * FROM student WHERE matrix_no = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result_student = $stmt->get_result();

    if ($result_student->num_rows === 1) {
        $student = $result_student->fetch_assoc();
        if ($password === $student['password']) {
            $_SESSION['role'] = 'student';
            $_SESSION['user_id'] = $student['matrix_no'];
            $_SESSION['user_name'] = $student['student_name'];
            header("Location: student_home.php");
            exit();
        }
    }

    // Check if user is a staff
    $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result_staff = $stmt->get_result();

    if ($result_staff->num_rows === 1) {
        $staff = $result_staff->fetch_assoc();
        if ($password === $staff['password']) {

            // Assign session khusus staff
            $_SESSION['role'] = 'staff';
            $_SESSION['staff_id'] = $staff['staff_id']; // FK untuk hostel_application / college_assignment
            $_SESSION['user_id'] = $staff['staff_id'];  // Untuk page navigation / common usage
            $_SESSION['user_name'] = $staff['staff_name'];

            header("Location: staff_home.php");
            exit();
        }
    }

    // Login gagal
    echo "<script>alert('Invalid ID or Password'); window.location.href='login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>E-Kolej UTeM Login</title>
  <link rel="stylesheet" href="style.css">
  <style>
  

body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  background: url('kolej.jpg') no-repeat center center fixed;
  background-size: cover;
  background-position: center;
  min-height: 100vh;
  color: #fff;
}

.logo-bar {
  background-color: #003366;
  color: white;
  display: flex;
  align-items: center;
  padding: 10px 40px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.logo-bar img {
  height: 60px;
  margin-right: 20px;
}

.logo-bar h1 {
  font-size: 28px;
  margin: 0;
  color: #fff;

}

main {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6); 
}

.description {
  flex: 1;
  color: white;
  max-width: 600px;
  padding: 30px;
}

.description h2 {
  font-size: 32px;
  margin-bottom: 50px;

}

.description p {
  font-size: 20px;
  line-height: 1.5;
  margin-bottom: 20px;
}

.login-box {
  background: rgba(255, 255, 255, 0.85);
  padding: 40px;
  border-radius: 20px;
  width: 380px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.login-box h3 {
  text-align: center;
  font-size: 24px;
  color: #003366;
  margin-bottom: 5px;
}

.subtitle {
  text-align: center;
  font-size: 18px;
  color: #555;
  margin-bottom: 20px;
}

.login-box input {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.login-box button {
  width: 100%;
  background-color: #003366;
  color: white;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
}

.login-box button:hover {
  background-color: #002244;
}

.login-links {
  text-align: center;
  margin-top: 10px;
}

.login-links a {
  color: #003366;
  text-decoration: none;
}

</style>
</head>

<body>
  <header>
    <div class="logo-bar">
      <img src="LogoJawiUTeM_white-01.png" alt="UTeM Logo">
      <h1>E-Kolej UTeM Management System</h1>
    </div>
  </header>

  <main>
    <div class="description">
      <h2>Welcome to e-Kolej UTeM</h2>
      <p>
        Residential college applications are based on availability and decisions will be subject to the college’s permitted intake of students.
      </p>
      <p><strong>Contact us on:</strong><br>Telegram : HEPA@UTeM</p>
    </div>
    <div class="login-box">
      <h3>e-Kolej UTeM</h3>
      <p class="subtitle">User Login</p>

      <form action="login.php" method="POST">
      <input type="text" name="user_id" placeholder="User ID">
      <input type="password" name="password" placeholder="Enter your Password">
        <button type="submit">Login</button>
        
        <div class="login-links">
          <a href="#.html">Forgot Password?<br><br></a> |
          <a href="registration.php">Register here</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
