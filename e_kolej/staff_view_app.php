<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Search functionality
$search = $_GET['search'] ?? '';

$gender  = $_GET['gender']  ?? '';
$session = $_GET['session'] ?? '';
$faculty = $_GET['faculty'] ?? '';

$where = "WHERE (s.matrix_no LIKE '%$search%' OR s.student_name LIKE '%$search%')";

if ($gender !== '') {
    $where .= " AND s.gender = '$gender'";
}

if ($session !== '') {
    $where .= " AND h.session = '$session'";
}

if ($faculty !== '') {
    $where .= " AND s.faculty = '$faculty'";
}


$query = "
SELECT h.application_id, h.matrix_no, h.session, h.reason, h.details, 
       h.medical_doc, h.status, h.applied_date, h.reject_reason,
       s.student_name, s.faculty, s.program, s.academic_year, 
       s.cohort, s.race, s.religion, s.student_phone, s.ic_no, s.gender,
       c.college_name,
       b.block_no
FROM hostel_application h
JOIN student s ON h.matrix_no = s.matrix_no
LEFT JOIN college_assignment ca ON h.matrix_no = ca.matrix_no
LEFT JOIN college c ON ca.college_id = c.college_id
LEFT JOIN block b ON ca.block_id = b.block_id
$where
ORDER BY h.applied_date DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Student Hostel Applications - e-Kolej UTeM</title>
<link rel="stylesheet" href="style.css" />
<style>

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
    .dashboard-container {
    flex: 1;
    margin-left: 240px; 
    padding: 30px;
    background: #f8f8f8;
    gap: 20px;
    }

table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
th, td { padding: 8px; border: 1px solid #ccc; font-size: 14px; text-align: center; }
th { background-color: #003366; color: white; }
.status-pending { color: orange; font-weight: bold; }
.status-approved { color: #28a745; font-weight: bold; }
.status-rejected { color: #dc3545; font-weight: bold; }
.action-btn { padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; margin: 2px; }
.view-detail { background-color: #007BFF; color: white; }
.search-bar { margin-top: 20px; }
.search-bar input[type="text"] { padding: 6px; width: 200px; }
.search-bar button { padding: 6px 10px; }


.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; }
.modal-content {
  background: white;
  width: 700px;
  max-height: 85vh;
  margin: 60px auto;
  border-radius: 8px;
  position: relative;
  display: flex;
  flex-direction: column;
}
.modal-body {
  padding: 15px;
  overflow-y: auto;
  flex: 1;
}
.modal-action {
  position: sticky;
  bottom: 0;
  background: #fff;
  padding: 15px;
  border-top: 1px solid #ddd;
}
.close-modal { position:absolute; top:10px; right:15px; cursor:pointer; font-weight:bold; }
.modal table td { text-align:left; padding:5px 10px; }
.action-box {
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: stretch;
}

.action-input {
  width: 100%;
  padding: 6px;
  font-size: 13px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.action-divider {
  border: none;
  border-top: 1px dashed #ccc;
  margin: 6px 0;
}

.action-btn { padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; margin: 2px; }
.preview-box {
  position: right;
  width: 380px;
  height: 320px;
  border: 1px solid #ccc;
  background: #fff;
  z-index: 500;
}

.preview-box iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.medical-link:hover + .preview-box {
  display: block;
}

.filter-box {
    padding: 6px;
    width: 200px;      /* SAME as search box */
    height: 36px;      /* unify height */
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}
.table-container {
  max-height: 500px; /* adjust height as needed */
  overflow-y: auto;
  border-radius: 4px;
  
}

.table-container table {
  width: 100%;
  border-collapse: collapse;
}

.table-container thead th {
  position: sticky;
  top: 0;
  background-color: #003366;
  color: white;
  z-index: 2;
}

</style>
</head>

<body class="staff-page">
<div style="display: flex; min-height: 100vh;">
<div class="sidebar">
  <img src="LogoJawiUTeM_white-01.png" alt="Logo" />
  <div style="text-align: center;">
    <h3><?php echo $_SESSION['user_name']; ?></h3>
    <p><?php echo $_SESSION['user_id']; ?></p>
  </div>
  <nav class="nav-links">
    <br><a href="staff_home.php">🏠 Dashboard<br><br></a>
    <a href="staff_view_app.php" style="font-weight:bold;">📝 Student Application<br><br></a>
    <a href="staff_reg_college.php">🏢 Register College & Block<br><br></a>
    <a href="staff_reg_room.php">🚪 Register Residence/Rooms<br><br></a>
    <a href="staff_assign_room.php">🔑 Room Assignment<br><br></a>
    <a href="staff_report.php">📊 Report<br><br></a>
  </nav>
  <form action="logout.php" method="post" style="margin-top: auto;">
    <button type="submit" class="signout-btn">Sign Out</button>
  </form>
</div>

<div class="dashboard-container">
  <h2>Student Hostel Applications</h2>

  <!-- Search -->
  <form method="GET" class="search-bar" style="display:flex; gap:10px; align-items:center;">
    <input type="text" name="search"  class="filter-box" placeholder="Search Matric No / Name" value="<?= htmlspecialchars($search) ?>">
  
  <!-- Gender Filter -->
  <select name="gender" class="filter-box">
    <option value="">All Genders</option>
    <option value="Male" <?= ($_GET['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
    <option value="Female" <?= ($_GET['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
  </select>

  <!-- Session Filter -->
  <select name="session" class="filter-box">
    <option value="">All Sessions</option>
    <?php
    $session_q = mysqli_query($conn, "SELECT DISTINCT session FROM hostel_application ORDER BY session DESC");
    while ($s = mysqli_fetch_assoc($session_q)) {
        $selected = ($_GET['session'] ?? '') === $s['session'] ? 'selected' : '';
        echo "<option value='{$s['session']}' $selected>{$s['session']}</option>";
    }
    ?>
    </select>

    <!-- Faculty Filter -->
    <select name="faculty" class="filter-box">
      <option value="">All Faculties</option>
      <option value="FACULTY OF ELECTRICAL TECHNOLOGY AND ENGINEERING (FTKE)" <?= ($faculty=='FACULTY OF ELECTRICAL TECHNOLOGY AND ENGINEERING (FTKE)')?'selected':'' ?>>FACULTY OF ELECTRICAL TECHNOLOGY AND ENGINEERING (FTKE)</option>
      <option value="FACULTY OF ELECTRONICS AND COMPUTER TECHNOLOGY AND ENGINEERING (FTKEK)" <?= ($faculty=='FACULTY OF ELECTRONICS AND COMPUTER TECHNOLOGY AND ENGINEERING (FTKEK)')?'selected':'' ?>>FACULTY OF ELECTRONICS AND COMPUTER TECHNOLOGY AND ENGINEERING (FTKEK)</option>
      <option value="FACULTY OF INFORMATION AND COMMUNICATIONS TECHNOLOGY (FTMK)" <?= ($faculty=='FACULTY OF INFORMATION AND COMMUNICATIONS TECHNOLOGY (FTMK)')?'selected':'' ?>>FACULTY OF INFORMATION AND COMMUNICATIONS TECHNOLOGY (FTMK)</option>
      <option value="FACULTY OF MECHANICAL TECHNOLOGY AND ENGINEERING (FTKM)" <?= ($faculty=='FACULTY OF MECHANICAL TECHNOLOGY AND ENGINEERING (FTKM)')?'selected':'' ?>>FACULTY OF MECHANICAL TECHNOLOGY AND ENGINEERING (FTKM)</option>
      <option value="FACULTY OF TECHNOLOGY MANAGEMENT AND TECHNOPRENEURSHIP (FPTT)" <?= ($faculty=='FACULTY OF TECHNOLOGY MANAGEMENT AND TECHNOPRENEURSHIP (FPTT)')?'selected':'' ?>>FACULTY OF TECHNOLOGY MANAGEMENT AND TECHNOPRENEURSHIP (FPTT)</option>
      <option value="FACULTY OF INDUSTRIAL AND MANUFACTURING TECHNOLOGY AND ENGINEERING (FTKIP)" <?= ($faculty=='FACULTY OF INDUSTRIAL AND MANUFACTURING TECHNOLOGY AND ENGINEERING (FTKIP)')?'selected':'' ?>>FACULTY OF INDUSTRIAL AND MANUFACTURING TECHNOLOGY AND ENGINEERING (FTKIP)</option>
      <option value="FACULTY OF ARTIFICIAL INTELLIGENCE AND CYBER SECURITY (FAIX)" <?= ($faculty=='FACULTY OF ARTIFICIAL INTELLIGENCE AND CYBER SECURITY (FAIX)')?'selected':'' ?>>FACULTY OF ARTIFICIAL INTELLIGENCE AND CYBER SECURITY (FAIX)</option>
     </select>

  </select>
    
    <button type="submit">Search</button>
  </form>

<div class="table-container">
  <table>
       <thead>
    <tr>
      <th>No</th>
      <th>Matric No</th>
      <th>Name</th>
      <th>Year</th>
      <th>Faculty</th>
      <th>Reason</th>
      <th>Applied Date</th>
            <th>Status</th>
      <th>Assigned College</th>
      <th>Reject Reason</th>   
      <th>Actions</th>
    </tr>
       </thead>
           <tbody>
    <?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
    <tr 
        data-appid="<?= $row['application_id'] ?>"
        data-cohort="<?= htmlspecialchars($row['cohort']) ?>"
        data-ic="<?= htmlspecialchars($row['ic_no']) ?>"
        data-phone="<?= htmlspecialchars($row['student_phone']) ?>"
        data-race="<?= htmlspecialchars($row['race']) ?>"
        data-religion="<?= htmlspecialchars($row['religion']) ?>"
        data-program="<?= htmlspecialchars($row['program']) ?>"
        data-reason="<?= htmlspecialchars($row['reason']) ?>"
        data-details="<?= htmlspecialchars($row['details']) ?>"
        data-gender="<?= htmlspecialchars($row['gender']) ?>"
        data-medical="<?= htmlspecialchars($row['medical_doc']) ?>"
        data-college="<?= htmlspecialchars($row['college_name'] ?? '') ?>"
        data-block="<?= htmlspecialchars($row['block_no'] ?? '') ?>"
        data-reject="<?= htmlspecialchars($row['reject_reason'] ?? '') ?>"
        data-status="<?= $row['status'] ?>"
        data-session="<?= htmlspecialchars($row['session']) ?>"
        >
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['matrix_no']) ?></td>
        <td><?= htmlspecialchars($row['student_name']) ?></td>
        <td><?= htmlspecialchars($row['academic_year']) ?></td>
        <td><?= htmlspecialchars($row['faculty']) ?></td>
        <td><?= htmlspecialchars($row['reason']) ?></td>
        <td><?= htmlspecialchars($row['applied_date']) ?></td>
        <td class="<?php
            if ($row['status']==='Pending') echo 'status-pending';
            elseif($row['status']==='Approved') echo 'status-approved';
            elseif($row['status']==='Rejected') echo 'status-rejected';?>
            ">
            <?= htmlspecialchars($row['status']) ?>
        </td>
        <td><?= htmlspecialchars($row['college_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($row['reject_reason'] ?? '-') ?></td>  
     <!-- Action -->
     <td>
      <div class="action-box">
        <button class="action-btn view-detail">View Detail</button>
        </div>
      </td>
    </tr>
    <?php endwhile; ?>
       </tbody>
</table>
</div>

<!-- ================= MODAL ================= -->
<div id="detailModal" class="modal">
<div class="modal-content">

<span class="close-modal">&times;</span>
<h3 style="padding:15px;">Student Details</h3>
<!-- ================= SCROLLABLE BODY ================= -->
    <div class="modal-body">
      <table>
        <tr><td>Name</td><td id="detail_name"></td></tr>
        <tr><td>Matric No</td><td id="detail_matrix"></td></tr>
        <tr><td>IC No</td><td id="detail_ic"></td></tr>
        <tr><td>Faculty</td><td id="detail_faculty"></td></tr>
        <tr><td>Program</td><td id="detail_program"></td></tr>
        <tr><td>Year</td><td id="detail_year"></td></tr>
        <tr><td>Cohort</td><td id="detail_cohort"></td></tr>
        <tr><td>Phone</td><td id="detail_phone"></td></tr>
        <tr><td>Race</td><td id="detail_race"></td></tr>
        <tr><td>Religion</td><td id="detail_religion"></td></tr>
        <tr><td>Gender</td><td id="detail_gender"></td></tr>
        <tr><td>Reason</td><td id="detail_reason"></td></tr>
        <tr><td>Details</td><td id="detail_details"></td></tr>
        <tr><td>Status</td><td id="detail_status"></td></tr>
        <tr><td>Applied Date</td><td id="detail_date"></td></tr>
        <tr><td>Session</td><td id="detail_session"></td></tr>
        <tr><td>College</td><td id="detail_college"></td></tr>
          <tr><td>Block</td><td id="detail_block"></td></tr>
        <tr><td>Reject Reason</td><td id="detail_reject"></td></tr>

      <!-- ===== Medical Evidence ===== -->
        <tr id="medical_row" style="display:none;">
          <td>Medical Evidence</td>
          <td>
            <a class="medical-link" id="medical_link">Download</a>

            <!-- Hover Preview -->
            <div class="preview-box">
              <iframe id="medical_preview"></iframe>
            </div>
          </td>
        </tr>
      </table>
    </div>

    <!-- ================= STICKY ACTION ================= -->
    <div class="modal-action">
      
      <form action="staff_update_status.php" method="POST">
        <input type="hidden" name="application_id" id="modal_app_id">

        <!-- Assign College -->
        
        <select name="college_id" id="college_id" class="action-input" required>
        <option value="">-- Select College --</option>
        <?php
        $college_q = mysqli_query($conn, "SELECT college_id, college_name FROM college");
        while ($c = mysqli_fetch_assoc($college_q)) {
            echo "<option value='{$c['college_id']}'>{$c['college_name']}</option>";
        }
        ?>
        </select>

        <!-- Assign Block (dropdown akan populate ikut college via JS) -->
    <select name="block_id" id="block_select" class="action-input" required>
      <option value="">-- Select Block --</option>
 
    </select>

      <textarea name="reject_reason" class="action-input" id="reject_reason" rows="2" placeholder="Reason if Reject"></textarea>
      <button type="button" class="action-btn approve" onclick="approveApp()">Approve</button>
      <button type="button" class="action-btn reject" onclick="rejectApp()">Reject</button>
      <input type="hidden" name="action" id="action_type">
      </form> 
    </div>
  </div>
</div>

<script>
const modal = document.getElementById('detailModal');
const closeModal = document.querySelector('.close-modal');

document.querySelectorAll('.view-detail').forEach(btn=>{
btn.onclick = ()=>{
const tr = btn.closest('tr');
const currentStatus = tr.dataset.status;

detail_name.innerText = tr.children[2].innerText;
detail_matrix.innerText = tr.children[1].innerText;
detail_ic.innerText = tr.dataset.ic;
detail_faculty.innerText = tr.children[4].innerText;
detail_program.innerText = tr.dataset.program;
detail_year.innerText = tr.children[3].innerText;
detail_cohort.innerText = tr.dataset.cohort;
detail_phone.innerText = tr.dataset.phone;
detail_race.innerText = tr.dataset.race;
detail_religion.innerText = tr.dataset.religion;
detail_gender.innerText = tr.dataset.gender;
detail_reason.innerText = tr.dataset.reason;
detail_details.innerText = tr.dataset.details|| '-';;
detail_status.innerText = tr.children[7].innerText;
detail_date.innerText = tr.children[6].innerText;
detail_college.innerText = tr.dataset.college || '-';
detail_block.innerText = tr.dataset.block || '-';
detail_reject.innerText = tr.dataset.reject || '-';
detail_session.innerText = tr.dataset.session || '-';

modal_app_id.value=tr.dataset.appid;

const medicalRow = document.getElementById('medical_row');
const medicalLink = document.getElementById('medical_link');
const medicalPreview = document.getElementById('medical_preview');

/* ===== RESET MEDICAL STATE (WAJIB) ===== */
medicalRow.style.display = 'none';
medicalLink.href = '#';
medicalPreview.src = '';

/* ===== SHOW ONLY IF MEDICAL ===== */
if (tr.dataset.reason === 'Medical Condition' && tr.dataset.medical !== '') {
  medicalRow.style.display = 'table-row';
  medicalLink.href ="download_medical.php?app_id=" + tr.dataset.appid;
  medicalPreview.src ="download_medical.php?app_id=" + tr.dataset.appid + "&preview=1";
}

modal.style.display='block';
};
});

closeModal.onclick = () => {
  modal.style.display = 'none';
};

window.onclick = (e) => {
  if (e.target === modal) {
    modal.style.display = 'none';
  }
};


// Approve / Reject
function approveApp(){
    const collegeSelect = document.getElementById('college_id');
    const blockSelect = document.getElementById('block_select');
    const assignedCollege = detail_college.innerText;
    const studentGender = detail_gender.innerText;

    if(collegeSelect.value === '' || blockSelect.value === ''){
        alert('Assign Both College & Block first');
        return;
    }

    if(assignedCollege && assignedCollege !== '-'){
        alert('Student has already been assigned to a college!');
        return;
    }

const selectedBlock = blockSelect.selectedOptions[0];
const blockGender = selectedBlock.dataset.gender;// Male/Female/Any

if(!blockGender){
    alert('Block gender not found.');
    return;
}

if(blockGender !== studentGender){
    alert(`Cannot assign ${studentGender} student to ${blockGender} block!`);
    return;
}
    action_type.value = 'Approved';
    document.querySelector('.modal-action form').submit();
}

function rejectApp(){
  if(reject_reason.value.trim()===''){
    alert('Fill reject reason');
    return;
  }
  action_type.value='Rejected';
  document.querySelector('.modal-action form').submit();
}

// ===== JS for block dropdown depend on college / Populate block based on college =====
document.getElementById('college_id').addEventListener('change', function(){
    let collegeId = this.value;
    let blockSelect = document.getElementById('block_select');
    blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
    if(collegeId){
        fetch('load_block.php?college_id=' + collegeId)
        .then(r => r.json())
        .then(data => {
            data.forEach(b => {
                let opt = document.createElement('option');
                opt.value = b.block_id;
                opt.textContent = b.block_no;
                opt.dataset.gender = b.gender;
                blockSelect.appendChild(opt);
            });
        });
    }
});
</script>
</body>
</html>
