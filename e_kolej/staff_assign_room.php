<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}
$selected_block = $_POST['block_code'] ?? '';
$selected_residence = $_POST['residence_no'] ?? '';


?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dormitory Registration</title>
    <link rel="stylesheet" href="style.css"/>
    <style>
   
        .search-box{background:#e9ecef;padding:15px;border-radius:20px;display:flex;gap:20px;align-items:center;
        margin-bottom:10px}
        .search-box input{padding:8px;width:220px}
        .search-box button{padding:8px 15px}
        .table-container { flex: 1; max-height: 250px; overflow-y: auto; border-radius: 6px; background: white; color: black; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        
        }
        table th, table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        table th {
            background: #003366;
            color: white;
            text-align: center;
        }
        table tr:hover { background: #eef3ff; cursor: pointer; }

        .flex { display: flex; gap: 20px; }
        .w-50 { width: 50%; }
        .btn {
            background: #003366;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover { background: #0055aa; }
  
        label { font-weight: bold; color: black; font-weight: bold; }
        input, select {
            width: 100%;
            padding: 6px;
            margin-top: 3px;
            margin-bottom: 10px;
        }
        h2 { color: black; margin-bottom: 15px; }
        .sidebar h3 {
        color: white !important;
    }
    
    .dashboard-container h3 {color: black;}
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            gap: 10px;
            margin-bottom: 8px;
            align-items: center;
        }

          .box {
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
     .form-table input[readonly] {
      width: 100%;
      padding: 8px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      background-color: #f5f5f5;
    }
    tr.selected {
    background-color: #cce5ff !important;
    
    
}

</style>
</head>

<body>
<div class="container">

<div class="sidebar">
    <img src="LogoJawiUTeM_white-01.png" alt="Logo" />
    <div style="text-align: center;">
        <h3><?php echo $_SESSION['user_name']; ?></h3>
        <p><?php echo $_SESSION['user_id']; ?></p>
    </div>

    <nav class="nav-links">
        <br><a href="staff_home.php">🏠 Dashboard<br><br></a>
        <a href="staff_view_app.php">📝 Student Application<br><br></a>
        <a href="staff_reg_college.php">🏢 Register College & Block<br><br></a>
        <a href="staff_reg_room.php">🚪 Register Residence/Rooms<br><br></a>
        <a href="staff_assign_room.php"style="font-weight:bold;">🔑 Room Assignment<br><br></a>
        <a href="staff_report.php">📊 Report<br><br></a>
    </nav>

    <form action="logout.php" method="post" style="margin-top: auto;">
        <button type="submit" class="signout-btn">Sign Out</button>
    </form>
</div>

<div class="dashboard-container">

    <h2>Student Dormitory Registration</h2>

    <!-- ======================= SEARCH STUDENT ======================= -->

    <form method="post" class="search-box">
        <label>Matric No</label>
        <input type="text" id="matrix_input" name="matrix_no" placeholder="Enter Matric No">
        <button type="button" class="btn" onclick="searchStudent()">Search</button>
    </form>

    <!-- ======================= STUDENT DETAILS ======================= -->
    <div class="form-table">
    <h3>Student Details</h3>
    <div class="grid-2">
      <div>
            <label>Matric No</label>
            <input type="text" id="sd_matrix" readonly>

            <label>Current Session</label>
            <select>
                <option>1-2025/2026</option>
            </select>

            <label>Cohort</label>
            <input type="text" id="cohort" readonly>

            <label>Year / Sem</label>
            <input type="text" id="academic_year" readonly>
        </div>
        <div>
            <label>Name</label>
            <input type="text" id="student_name" readonly>

            <label>Faculty</label>
            <input type="text" id="faculty" readonly>

            <label>Program</label>
            <input type="text" id="program" readonly>

            <!--<label>Last Sem Residence</label>-->
            <!--<input type="text" id="last_residence" readonly>-->
            
        </div>
    </div>


<!-- ======================= CHOOSE BLOK ======================= -->
 <div class="box">
      <h3>Residential Details</h3>
      <input type="hidden" id="block_id">
      
      <div class="grid-2">
          <div>
              <label>College</label>
              <input type="text" id="college_name" readonly>
              <label>Block Number</label>
              <input type="text" id="block_no" readonly>
             
              <button type="button" class="btn" onclick="loadResidence()">Display</button>
          </div>
          <div>
              <label>Residence List for Block</label>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Residence Number</th>
                <th>Load</th>
                <th>Current</th>
                <th>Availability</th>
            </tr>
        </thead>
        <tbody id="residence_list"></tbody>
    </table>
</div>
</div>

      </div>
  </div>

    <!-- ======================= STUDENT COLLEGE REGISTRATION & LIST OF ROOM ======================= -->
<div class="box">
      <h3>Student College Registration</h3>
      <div class="grid-2">
         <div>
            <label>Room No</label>
<input type="text" id="selected_room_no">
<input type="hidden" id="selected_room_id">
<input type="hidden" id="selected_house">
            <!--<label>Matric No</label>-->
            <!--<input type="text" id="student_matrix" readonly>-->
            <label>Date Check In</label>
            <input type="date" id="checkin_date" value="<?= date('Y-m-d') ?>" readonly>
            <button type="button" class="btn" onclick="registerRoom()">Register</button>
        </div>
        <div>
            <label>Room List for Selected Residence</label>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Load</th>
                <th>Current</th>
                <th>Availability</th>
            </tr>
        </thead>
        <tbody id="room_list"></tbody>
    </table>
</div>
</div>
</div>
</div>
    <!-- ======================= DAFTAR MASUK BILIK ======================= -->
<div class="box">
      <h3>List of Students for Room</h3>
      <table>
          <thead>
              <tr>
                  <th>Matric No</th>
                  <th>Name</th>
                  <th>Check In</th>
              </tr>
          </thead>
          <tbody id="room_student_list"></tbody>
      </table>
  </div>


<!-- ======================= AJAX SECTION ======================= -->
<script>
function searchStudent() {
    let m = document.getElementById("matrix_input").value;
    if (m === "") return;

    fetch("staff_search_student.php?matrix_no=" + m)
  .then(r => r.json())
  .then(data => {
      if (data.error) {
          alert(data.message);
          return;
      }
      document.getElementById("sd_matrix").value = data.matrix_no;
      document.getElementById("student_name").value = data.student_name;
      document.getElementById("faculty").value = data.faculty;
      document.getElementById("program").value = data.program;
      document.getElementById("cohort").value = data.cohort;
      document.getElementById("academic_year").value = data.academic_year;
      document.getElementById("college_name").value = data.college_name ?? '';
      document.getElementById("block_no").value = data.block_no ?? '';
      document.getElementById("block_id").value = data.block_id;
      
  });
}

// Load blocks berdasarkan college

// Load residence berdasarkan block
function loadResidence() {
    let blockId = document.getElementById("block_id").value;
    if (!blockId) {
        alert("Block not assigned for this student");
        return;
    }

    fetch("load_house.php?block_id=" + blockId)
        .then(r => r.text())
        .then(data => {
            document.getElementById("residence_list").innerHTML = data;
        });
}
// Load rooms berdasarkan residence
function loadRooms(row, house_id) {

    // highlight selected house
    document.querySelectorAll("#residence_list tr")
        .forEach(r => r.classList.remove("selected"));
    row.classList.add("selected");

    // load rooms
    fetch("load_rooms.php?house_id=" + house_id)
        .then(r => r.text())
        .then(data => {
            document.getElementById("room_list").innerHTML = data;
            document.getElementById("room_student_list").innerHTML = ""; // clear students
            document.getElementById("selected_room_no").value = "";
            document.getElementById("selected_room_id").value = "";
        });
}
// Choose room
function chooseRoom(room_id, room_no, house_id) {
    document.getElementById("selected_room_no").value = room_no;
    document.getElementById("selected_room_id").value = room_id;
    document.getElementById("selected_house").value = house_id;

    fetch("load_room_students.php?room_id=" + room_id)
        .then(r => r.text())
        .then(data => {
            document.getElementById("room_student_list").innerHTML = data;
        });
}

// Register student  room
function registerRoom() {
    let room_id = document.getElementById("selected_room_id").value;
    let house_id = document.getElementById("selected_house").value;
    let student = document.getElementById("sd_matrix").value;
    let date = document.getElementById("checkin_date").value;

    if (!room_id || !student) {
        alert("Select student and room first");
        return;
    }

    fetch("staff_assign_room_process.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `room_id=${room_id}&house=${house_id}&student=${student}&date=${date}`
    })
    .then(r => r.text())
    .then(msg => {
        alert(msg);
        loadResidence();
    });
}

</script>

</body>
</html>
