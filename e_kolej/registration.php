<?php
include 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

   // --- PASSWORD CHECK ---
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    $plain_password = $password; // ⚠️ you should use password_hash for real system

    if ($role === 'student') {
        $matrix_no = $_POST['matrix_no'];
        $student_name = $_POST['student_name'];
        $ic_no = $_POST['ic_no'];
        $email = $_POST['email'];
        $faculty = $_POST['faculty'];
        $program = $_POST['program'];
        $cohort = $_POST['cohort'];
        $academic_year = $_POST['academic_year'];
        $student_phone = $_POST['student_phone'];
        $gender = $_POST['gender'];
        $race = $_POST['race'];
        $religion = $_POST['religion'];
        
        // Check if student exists
        $check = $conn->prepare("SELECT * FROM student WHERE matrix_no = ?");
        $check->bind_param("s", $matrix_no);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo "<script>alert('Matric number already registered. Please login.'); window.location.href='login.php';</script>";
            exit();
        }

       // Insert student
        $stmt = $conn->prepare("INSERT INTO student 
            (matrix_no, student_name, ic_no, email, faculty, program, cohort, academic_year, student_phone, gender, race, religion, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", 
            $matrix_no, $student_name, $ic_no, $email, $faculty, $program, $cohort, $academic_year, $student_phone, $gender, $race, $religion, $plain_password);

    } elseif ($role === 'staff') {
        $staff_id = $_POST['staff_id'];
        $staff_name = $_POST['staff_name'];
        $position = $_POST['position'];
        $staff_email = $_POST['staff_email'];
        $staff_phone = $_POST['staff_phone'];
        
        // Check if staff exists
        $check = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
        $check->bind_param("s", $staff_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo "<script>alert('Staff ID already registered. Please login.'); window.location.href='login.php';</script>";
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO staff (staff_id, staff_name, position, staff_email, staff_phone, password)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $staff_id, $staff_name, $position, $staff_email, $staff_phone, $plain_password);
    } else {
        echo "<script>alert('Invalid role.'); window.history.back();</script>";
        exit();
    }

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Registration failed: " . addslashes($conn->error) . "'); window.history.back();</script>";
    }

    // Execute insert
    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Registration failed: " . addslashes($conn->error) . "'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>e-Kolej UTeM Registration</title>
  <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0; padding: 0;
        display: flex; flex-direction: column;
        background: url('kolej.jpg') no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh; color: #fff;
    }
    main {
  flex: 1;
  justify-content: center;
  align-items: center;
  padding: 50px;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6); 
}

    .logo-bar {
        background-color: #003366;
        color: white;
        display: flex;
        align-items: center;
        padding: 10px 40px;
    }
    .logo-bar img { height: 60px; margin-right: 20px; }
    .container { display: flex; justify-content: center; padding: 50px; }
    .login-box {
        background-color: white; color: black;
        padding: 35px; border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 650px;
    }
    h3 { text-align: center; color: #003366; margin-bottom: 20px; }
    .subtitle { text-align: center; font-size: 16px; margin-bottom: 20px; }
    .form-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .form-row input, .form-row select {flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;}
  
    .form-group {
      display: flex;
      flex-direction: column;}
    .form-group.full {
      grid-column: span 2;
      margin-bottom: 15px;
    }
    .register-btn {
        background-color: #003366; color: white;
        padding: 12px; border: none; border-radius: 8px;
        cursor: pointer; width: 100%; font-size: 16px;
    }
    .register-btn:hover { background-color: #002244;}
    .hidden { display: none; }
    .login-links { text-align: center; margin-top: 20px;}
    .login-links a { text-decoration: none; color: #003366;}

      /* === RESPONSIVE === */
    @media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: span 2;
        margin-bottom: 15px;
        border-radius: 8px;
    }
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
  <div class="container">
    <div class="login-box">
      <h3>User Registration</h3>
      <p class="subtitle">Please select your role and fill in the details</p>

      <form method="POST">
        <div class="form-row">
          <select name="role" required onchange="toggleRoleFields(this.value)">
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="staff">Staff</option>
          </select>
        </div>

        <!-- Student Form -->
        <div id="student-fields" class="role-section hidden">
          <div class="form-row">
            <input type="text" name="matrix_no" placeholder="Matric Number" required>
            <input type="text" name="ic_no" placeholder="IC Number (Ex: XXXXXX-XX-XXXX)">
          </div>
          <div class="form-row">
             <input type="text" name="student_name" placeholder="Full Name">
          </div>

          <div class="form-row">
             <input type="email" name="email" placeholder="Student Email (Ex: matric_no@student.utem.edu.my">
          </div>

          <div class="form-group full">
            <select name="faculty">
              <option value="">-- Select Faculty --</option>
              <option value="FACULTY OF ELECTRICAL TECHNOLOGY AND ENGINEERING (FTKE)">FACULTY OF ELECTRICAL TECHNOLOGY AND ENGINEERING (FTKE)</option>
              <option value="FACULTY OF ELECTRONICS AND COMPUTER TECHNOLOGY AND ENGINEERING (FTKEK)">FACULTY OF ELECTRONICS AND COMPUTER TECHNOLOGY AND ENGINEERING (FTKEK)</option>
              <option value="FACULTY OF INFORMATION AND COMMUNICATIONS TECHNOLOGY (FTMK)">FACULTY OF INFORMATION AND COMMUNICATIONS TECHNOLOGY (FTMK)</option>
              <option value="FACULTY OF TECHNOLOGY MANAGEMENT AND TECHNOPRENEURSHIP (FPTT)">FACULTY OF TECHNOLOGY MANAGEMENT AND TECHNOPRENEURSHIP (FPTT)</option>
              <option value="FACULTY OF MECHANICAL TECHNOLOGY AND ENGINEERING (FTKM)">FACULTY OF MECHANICAL TECHNOLOGY AND ENGINEERING (FTKM)</option>
              <option value="FACULTY OF INDUSTRIAL AND MANUFACTURING TECHNOLOGY AND ENGINEERING (FTKIP)">FACULTY OF INDUSTRIAL AND MANUFACTURING TECHNOLOGY AND ENGINEERING (FTKIP)</option>
              <option value="FACULTY OF ARTIFICIAL INTELLIGENCE AND CYBER SECURITY (FAIX)">FACULTY OF ARTIFICIAL INTELLIGENCE AND CYBER SECURITY (FAIX)</option>
            </select>
          </div>

         <div class="form-group full">
            <select name="program">
              <option value="">-- Select Program --</option>
              <option value="DEN">DIPLOMA IN ELECTRICAL ENGINEERING </option> 
              <option value="BENG">BACHELOR OF ELECTRICAL ENGINEERING WITH HONOURS</option> 
              <option value="BEMG">BACHELOR OF MECHATRONIC ENGINEERING  WITH HONOURS</option>
              <option value="BELT">BACHELOR OF ELECTRICAL ENGINEERING TECHNOLOGY WITH HONOURS</option> 
              <option value="BELR">BACHELOR OF ELECTRICAL ENGINEERING TECHNOLOGY (INDUSTRIAL AUTOMATION) WITH HONOURS</option> 

              <option value="DER">DIPLOMA IN ELECTRONIC ENGINEERING</option>  
              <option value="BERG">BACHELOR OF ELECTRONICS ENGINEERING WITH HONOURS</option> 
              <option value="BERR">BACHELOR OF COMPUTER ENGINEERING WITH HONOURS</option> 
              <option value="BERE">BACHELOR OF ELECTRONICS ENGINEERING TECHNOLOGY (INDUSTRIAL ELECTRONICS) WITH HONOURS</option> 
              <option value="BERC">BACHELOR OF COMPUTER ENGINEERING TECHNOLOGY (COMPUTER SYSTEMS) WITH HONOURS</option> 
              <option value="BERZ">BACHELOR OF ELECTRONICS ENGINEERING TECHNOLOGY (TELECOMMUNICATIONS) WITH HONOURS</option> 
              <option value="BERT">BACHELOR OF ELECTRONICS ENGINEERING TECHNOLOGY WITH HONOURS</option> 
              <option value="BERL">BACHELOR OF TECHNOLOGY IN INDUSTRIAL ELECTRONIC AUTOMATION WITH HONOURS</option> 
              <option value="BERV">BACHELOR OF TECHNOLOGY IN INTERNET OF THINGS (IOT) WITH HONOURS</option>
              <option value="BERW">BACHELOR OF TECHNOLOGY IN TELECOMMUNICATIONS WITH HONOURS</option> 

              <option value="DCS">DIPLOMA IN COMPUTER SCIENCE</option>
              <option value="BITC">BACHELOR OF COMPUTER SCIENCE (COMPUTER NETWORKING) WITH HONOURS </option>
              <option value="BITD">BACHELOR OF COMPUTER SCIENCE (DATABASE MANAGEMENT) WITH HONOURS </option>
              <option value="BITM">BACHELOR OF COMPUTER SCIENCE (INTERACTIVE MEDIA) WITH HONOURS </option>
              <option value="BITS">BACHELOR OF COMPUTER SCIENCE (SOFTWARE DEVELOPMENT) WITH HONOURS </option>
              <option value="BITE">BACHELOR OF INFORMATION TECHNOLOGY (GAMES TECHNOLOGY) WITH HONOURS </option>
              <option value="BITA">BACHELOR OF TECHNOLOGY IN CLOUD COMPUTING AND APPLICATION WITH HONOURS</option>

              <option value="DMK">DIPLOMA IN MECHANICAL ENGINEERING </option> 
              <option value="BMKU">BACHELOR OF MECHANICAL ENGINEERING WITH HONOURS</option> 
              <option value="BMKF">BACHELOR OF AUTOMOTIVE TECHNOLOGY WITH HONOURS</option>
              <option value="BMKF">BACHELOR OF MECHANICAL ENGINEERING TECHNOLOGY WITH HONOURS</option>
              <option value="BMKK">BACHELOR OF AUTOMOTIVE ENGINEERING WITH HONOURS</option>
              <option value="BMKA">BACHELOR OF  MECHANICAL ENGINEERING TECHNOLOGY (AUTOMOTIVE TECHNOLOGY) WITH HONOURS</option>

              <option value="DMI">DIPLOMA IN MANUFACTURING ENGINEERING</option>
              <option value="BMIG">BACHELOR OF MANUFACTURING ENGINEERING  WITH HONOURS </option>
              <option value="BMIF">BACHELOR OF INDUSTRIAL ENGINEERING WITH HONOURS</option>
              <option value="BMIP">BACHELOR OF MANUFACTURING ENGINEERING TECHNOLOGY (PROCESS AND TECHNOLOGY) WITH HONOURS</option>
              <option value="BMID">BACHELOR OF MANUFACTURING ENGINEERING TECHNOLOGY (PRODUCT DESIGN) WITH HONOURS</option>
              <option value="BMIW">BACHELOR OF MANUFACTURING ENGINEERING TECHNOLOGY WITH HONOURS</option>
              <option value="BMIM">BACHELOR OF TECHNOLOGY IN INDUSTRIAL MACHINING WITH HONOURS</option>
              <option value="BMIK">BACHELOR OF TECHNOLOGY IN WELDING WITH HONOURS</option>
            
              <option value="BTEC">BACHELOR OF TECHNOPRENEURSHIP WITH HONOURS </option>
              <option value="BTMI">BACHELOR OF TECHNOLOGY MANAGEMENT WITH HONOURS (TECHNOLOGY INNOVATION) </option>
              <option value="BTMM">BACHELOR OF TECHNOLOGY MANAGEMENT WITH HONOURS (HIGH TECHNOLOGY MARKETING)</option>  
              <option value="BTMS">BACHELOR OF TECHNOLOGY MANAGEMENT (SUPPLY CHAIN MANAGEMENT AND LOGISTICS) WITH HONOURS</option>

              <option value="BAXI">BACHELOR OF COMPUTER SCIENCE (ARTIFICIAL INTELLIGENCE) WITH HONOURS </option>
              <option value="BAXZ">BACHELOR OF COMPUTER SCIENCE (COMPUTER SECURITY) WITH HONOURS </option>
            </select>
          </div>

          <div class="form-row">
            <select name="cohort" required>
              <option value="">-- Select Cohort --</option>
              <option value="2022/2023">2022/2023</option>
              <option value="2023/2024">2023/2024</option>
              <option value="2024/2025">2024/2025</option>
            </select>
            <select name="gender">
              <option value="">-- Select Gender --</option>
              <option value="MALE">MALE</option>
              <option value="FEMALE">FEMALE</option>
            </select>
          </div>
          <div class="form-row">
            <select name="race" required>
              <option value="">-- Select Race --</option>
              <option value="MALAY">MALAY</option>
              <option value="CHINESE">CHINESE</option>
              <option value="INDIAN">INDIAN</option>
              <option value="BUMIPUTERA SABAH">BUMIPUTERA SABAH</option>
              <option value="BUMIPUTERA SARAWAK">BUMIPUTERA SARAWAK</option>
              <option value="OTHERS">OTHERS</option>
            </select>
            <select name="religion" required>
              <option value="">-- Select Religion --</option>
              <option value="ISLAM">ISLAM</option>
              <option value="CHRISTIANITY">CHRISTIANITY</option>
              <option value="BUDDHISM">BUDDHISM</option>
              <option value="HINDUISM">HINDUISM</option>
              <option value="SIKHISM">SIKHISM</option>
              <option value="OTHERS">OTHERS</option>
            </select>
          </div>
          
          <div class="form-row">
            <input type="text" name="student_phone" placeholder="Phone Number">
             <select name="academic_year" required>
              <option value="">-- Year/Sem --</option>
              <option value="2/1">2/1</option>
              <option value="2/2">2/2</option>
              <option value="3/1">3/1</option>
              <option value="3/2">3/2</option>
            </select>
          </div>
          <div class="form-row">
            <input type="password" name="password" placeholder="Create Password">
            <input type="password" name="confirm_password" placeholder="Confirm Password">
          </div>
        </div>

        <!-- Staff Form -->
        <div id="staff-fields" class="role-section hidden">
          <div class="form-row">
            <input type="text" name="staff_id" placeholder="Staff ID">
            <input type="text" name="staff_name" placeholder="Full Name">
          </div>
          <div class="form-row">
            <input type="text" name="position" placeholder="Position">
            <input type="email" name="staff_email" placeholder="Email">
          </div>
          <div class="form-row">
            <input type="text" name="staff_phone" placeholder="Phone Number">
          </div>
          <div class="form-row">
            <input type="password" name="password" placeholder="Create Password">
            <input type="password" name="confirm_password" placeholder="Confirm Password">
          </div>
        </div>

        <button type="submit" class="register-btn">Register</button>
      </form>

      <div class="login-links">
        <p>Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>
  </div>
</main>

<script>
function toggleRoleFields(role) {
  document.getElementById('student-fields').classList.toggle('hidden', role !== 'student');
  document.getElementById('staff-fields').classList.toggle('hidden', role !== 'staff');
}
</script>

</body>
</html>
