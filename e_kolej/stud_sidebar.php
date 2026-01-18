<?php
include 'config_session.php';
?>

<!-- student.sidebar.php -->
<div class="sidebar">
  <img src="LogoJawiUTeM_white-01.png" alt="Logo" />

  <div style="text-align: center;">
    <h3><?php echo $_SESSION['user_name']; ?></h3>
    <p><?php echo $_SESSION['user_id']; ?></p>
    
   <!-- Current Academic Session -->
    <p style="
      font-size: 14px;
      margin-top: 6px;
      background: rgba(255,255,255,0.15);
      padding: 6px;
      border-radius: 6px;">
      <strong>Academic Session</strong><br>
      <?= CURRENT_SEMESTER; ?>-<?= CURRENT_SESSION; ?>
    </p>
  </div>

  <nav class="nav-links">
    <br><a href="stud_apply_hostel.php">🏠Hostel Application<br><br></a>
    <a href="stud_check_application.php">🔍Check Application Status<br><br></a>
    <a href="stud_apply_result.php">📝Application Result<br><br></a>
    <a href="stud_reg_slip.php">📄Pre-Registration Slip<br><br></a>
  </nav>

  <form action="logout.php" method="post" style="margin-top: auto;">
    <button type="submit" class="signout-btn">Sign Out</button>
  </form>
</div>
