<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$colleges_dropdown = mysqli_query($conn, "SELECT * FROM college ORDER BY college_id ASC");
$colleges_list = mysqli_query($conn, "SELECT * FROM college ORDER BY college_id ASC");
$blocks_list = mysqli_query($conn, "
    SELECT b.block_id, c.college_code, c.college_name, b.block_no, b.gender
    FROM block b 
    JOIN college c ON b.college_id = c.college_id
    ORDER BY b.block_id ASC
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Register College & Block - e-Kolej UTeM</title>
  <link rel="stylesheet" href="style.css">
  <style>

    .dashboard-container {
      flex: 1;
      padding: 30px;
      background: #f8f8f8;
      display: flex;
      margin-left: 240px; 
      flex-direction: column;
      gap: 30px;
    }
    .sidebar {
    width: 240px;
    background: #003366;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    }
    .container {
    display: flex;      
    min-height: 100vh;   
    }

    .section {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }

    .form-table-wrapper {
      display: flex;
      gap: 30px;
      align-items: flex-start;
    }

    .form-container {
      flex: 1;
    }

    .table-container {
      flex: 1;
      max-height: 250px;
      overflow-y: auto;
      border: 1px solid #ccc;
      border-radius: 6px;
      background: white;
      color: black;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      font-size: 14px;
      text-align: left;
    }

    th {
      background-color: #003366;
      color: white;
      position: sticky;
      top: 0;
    }

    h2 {
      color: #003366;
      margin-bottom: 15px;
    }

    input, select {
      padding: 8px;
      width: 100%;
      margin-top: 8px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .btn-group {
      display: flex;
      gap: 10px;
    }

    button {
      padding: 8px 15px;
      background: #003366;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    button.cancel {
      background: #888;
    }

    label {
      color: black;
      font-weight: bold;
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
          <a href="staff_reg_college.php"style="font-weight:bold;">🏢 Register College & Block<br><br></a>
          <a href="staff_reg_room.php">🚪 Register Residence/Rooms<br><br></a>
          <a href="staff_assign_room.php">🔑 Room Assignment<br><br></a>
          <a href="staff_report.php">📊 Report<br><br></a>
      </nav>
      <form action="logout.php" method="post" style="margin-top: auto;">
        <button type="submit" class="signout-btn">Sign Out</button>
      </form>
    </div>

    <div class="dashboard-container">

      <!-- SECTION 1: COLLEGE -->
      <div class="section">
        <h2>Register College</h2>
        <div class="form-table-wrapper">
          <div class="form-container">
            <form action="staff_reg_college_process.php" method="POST">
              <label>College Code:</label>
              <input type="text" name="college_code" required>
              <label>College Name:</label>
              <input type="text" name="college_name" required>
              <div class="btn-group">
                <button type="submit">Add</button>
                <button type="reset" class="cancel">Cancel</button>
              </div>
            </form>
          </div>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Code</th>
                  <th>College Name</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($college = mysqli_fetch_assoc($colleges_list)): ?>
                <tr>
                  <td><?= htmlspecialchars($college['college_code']) ?></td>
                  <td><?= htmlspecialchars($college['college_name']) ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- SECTION 2: BLOCK -->
      <div class="section">
        <h2>Register Block</h2>
        <div class="form-table-wrapper">
          <div class="form-container">
            <form action="staff_reg_block_process.php" method="POST">
              <label>College [Code - Name]:</label>
              <select name="college_id" required>
                <option value="">--Select College--</option>
                <?php while ($c = mysqli_fetch_assoc($colleges_dropdown)): ?>
                  <option value="<?= $c['college_id'] ?>">
                    <?= htmlspecialchars($c['college_code']) ?> - <?= htmlspecialchars($c['college_name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
              <label>Block Number:</label>
              <input type="text" name="block_no" required>
              <label>Category:</label>
              <select name="gender" required>
                <option value="">--Select--</option>
                <option value="Male">MALE</option>
                <option value="Female">FEMALE</option>
              </select>
              <div class="btn-group">
                <button type="submit">Add</button>
                <button type="reset" class="cancel">Cancel</button>
              </div>
            </form>
          </div>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>College</th>
                  <th>Block Number</th>
                  <th>Category</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($block = mysqli_fetch_assoc($blocks_list)): ?>
                <tr>
                  <td><?=  htmlspecialchars($block['college_name']) ?></td>
                  <td><?= htmlspecialchars($block['block_no']) ?></td>
                  <td><?= htmlspecialchars($block['gender']) ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</body>
</html>
