<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

/* =======================
   KPI
======================= */
$totalCapacity = $conn->query("SELECT SUM(capacity) AS total FROM room")->fetch_assoc()['total'] ?? 0;
$totalStudents = $conn->query("SELECT COUNT(DISTINCT matrix_no) AS total FROM student_room WHERE checkout_date IS NULL")->fetch_assoc()['total'] ?? 0;
$occupancyRate = $conn->query("SELECT ROUND((SUM(current_capacity)/SUM(capacity))*100,2) AS rate FROM room")->fetch_assoc()['rate'] ?? 0;
$alertHouses = $conn->query("SELECT COUNT(*) AS total FROM house WHERE (current_load/actual_load) >= 0.9")->fetch_assoc()['total'] ?? 0;

/* =======================
   College List (Dropdown)
======================= */
$colleges = $conn->query("SELECT college_id, college_name FROM college ORDER BY college_name ASC");

/* =======================
   Application Status
======================= */
$appTable = $conn->query("SELECT status, COUNT(*) AS total FROM hostel_application GROUP BY status");

/* =======================
   Exception & Pending
======================= */
$exception = $conn->query("
    SELECT ha.matrix_no, s.student_name, ha.status
    FROM hostel_application ha
    JOIN student s ON ha.matrix_no = s.matrix_no
    LEFT JOIN student_room sr ON ha.matrix_no = sr.matrix_no
    WHERE ha.status = 'Approved' AND sr.matrix_no IS NULL
");

$pendingApp = $conn->query("
    SELECT ha.application_id, ha.matrix_no, s.student_name, ha.applied_date
    FROM hostel_application ha
    JOIN student s ON ha.matrix_no = s.matrix_no
    WHERE ha.status = 'Pending'
    ORDER BY ha.applied_date ASC
");

/* =======================
   Application Chart
======================= */
$appChart = $conn->query("SELECT status, COUNT(*) AS total FROM hostel_application GROUP BY status");
$appStatus = [];
$appTotal = [];
while ($row = $appChart->fetch_assoc()) {
    $appStatus[] = $row['status'];
    $appTotal[] = (int)$row['total'];
}
/* =======================
   Student List Report
======================= */
$search = $_GET['search'] ?? '';
$filterCollege = $_GET['college'] ?? '';
$filterBlock = $_GET['block'] ?? '';
$filterHouse = $_GET['house'] ?? '';

$studentListSql = "
SELECT sr.assignment_id, s.matrix_no, s.student_name, c.college_name, b.block_no, h.house_code, r.room_no, sr.checkin_date
FROM student_room sr
JOIN student s ON sr.matrix_no = s.matrix_no
JOIN room r ON sr.room_id = r.room_id
JOIN house h ON r.house_id = h.house_id
JOIN block b ON h.block_id = b.block_id
JOIN college c ON b.college_id = c.college_id
WHERE sr.checkout_date IS NULL
";

$conditions = [];
if ($search) {
    $searchEsc = $conn->real_escape_string($search);
    $conditions[] = "(s.matrix_no LIKE '%$searchEsc%' OR s.student_name LIKE '%$searchEsc%')";
}
if ($filterCollege) {
    $conditions[] = "c.college_id = '".intval($filterCollege)."'";
}
if ($filterBlock) {
    $conditions[] = "b.block_id = '".intval($filterBlock)."'";
}

if ($filterHouse) {
    $conditions[] = "h.house_id = '".intval($filterHouse)."'";
}
// Apply conditions to SQL
if (count($conditions) > 0) {
    $studentListSql .= " AND " . implode(" AND ", $conditions);
}

$studentListSql .= " ORDER BY c.college_name, b.block_no, h.house_code, r.room_no, s.matrix_no";
$studentList = $conn->query($studentListSql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Dashboard | e-Kolej UTeM</title>
    <link rel="stylesheet" href="style.css"/>
    <style>
        .dashboard-container { padding:20px; }
        h2 { color:#000; margin-bottom:15px; }
        .kpi-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:25px; }
        .kpi-card{ background:#fff; border-radius:12px; padding:18px; box-shadow:0 0 6px rgba(0,0,0,0.1); }
        .kpi-card h4{margin:0;color:#003366;font-size:14px;}
        .kpi-card p{margin-top:8px;font-size:26px;font-weight:bold;color:#000;}
        .section-box{ background:#fff; padding:18px; border-radius:12px; box-shadow:0 0 6px rgba(0,0,0,0.1); margin-bottom:25px; }
        table{width:100%;border-collapse:collapse;margin-top:10px;}
        th,td{padding:8px;border:1px solid #ddd;text-align:center;}
        th{background:#003366;color:#fff;}
        tr:hover{background:#eef3ff;}
        .status-ok{color:green;font-weight:bold;}
        .status-warn{color:#d39e00;font-weight:bold;}
        .status-full{color:red;font-weight:bold;}
        .chart-box{ max-width:600px; margin:auto; margin-bottom:30px; }
        .chart-box canvas{ width:100% !important; height:350px !important; }
        .report-table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 40px; }
        .report-table th, .report-table td { padding: 12px 14px; border-bottom: 1px solid #eee; text-align: center; }
        .report-table th { background: #0a2c66; color:#fff; }
        .status.full { color:#e63946; font-weight:bold; }
        .status.almost-full { color:#f4a261; font-weight:bold; }
        .status.available { color:#2a9d8f; font-weight:bold; }
        .section-title { margin: 30px 0 15px; color:#0a2c66; }
        .alert { padding:6px 12px; border-radius:20px; font-size:13px; font-weight:bold; }
        .alert.pending { background:#fff3cd; color:#856404; }

        /* charts side-by-side */
        .chart-container{ display:flex; gap:20px; flex-wrap:wrap; justify-content:center; margin-bottom:40px; }
        .chart-container .chart-box{ flex:1; min-width:300px; max-width:500px; }

        .search-box {
            padding: 6px;
            width: 250px;      /* SAME as search box */
            height: 36px;      /* unify height */
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #003366;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .table-scroll {
    max-height: 400px;        /* control height */
    overflow-y: auto;         /* vertical scrollbar */
    border-radius: 8px;
}

/* Optional: sticky header */
.table-scroll thead th {
    position: sticky;
    top: 0;
    background: #0a2c66;
    color: white;
    z-index: 2;
}

    </style>
</head>
<body>
<div class="container">
<div class="sidebar">
    <img src="LogoJawiUTeM_white-01.png" alt="Logo" />
    <div style="text-align:center;">
        <h3><?= $_SESSION['user_name'] ?></h3>
        <p><?= $_SESSION['user_id'] ?></p>
    </div>
    <nav class="nav-links">
        <br><a href="staff_home.php">🏠 Dashboard<br><br></a>
        <a href="staff_view_app.php">📝 Student Application<br><br></a>
        <a href="staff_reg_college.php">🏢 Register College & Block<br><br></a>
        <a href="staff_reg_room.php">🚪 Register Residence/Rooms<br><br></a>
        <a href="staff_assign_room.php">🔑 Room Assignment<br><br></a>
        <a href="staff_report.php" style="font-weight:bold">📊 Report<br><br></a>
    </nav>
    <form action="logout.php" method="post" style="margin-top:auto;">
        <button type="submit" class="signout-btn">Sign Out</button>
    </form>
</div>

<div class="dashboard-container">
<h2>Report Dashboard</h2>

<div class="kpi-grid">
    <div class="kpi-card"><h4>Total Student Slots / Beds Number</h4><p><?= $totalCapacity ?></p></div>
    <div class="kpi-card"><h4>Total Registered Students</h4><p><?= $totalStudents ?></p></div>
    <div class="kpi-card"><h4>Occupancy Rate (Overall)</h4><p><?= $occupancyRate ?>%</p></div>
    <!--<div class="kpi-card"><h4>Alert Houses</h4><p><?= $alertHouses ?></p></div> -->
</div>

<!-- STUDENT LIST -->
<h3 class="section-title">📋 List of Registered Student</h3>
<form method="get" style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
    <input type="text" name="search" class= "search-box" placeholder="Matrix No / Name" value="<?= htmlspecialchars($search) ?>" />
    <select name="college" class= "search-box">
        <option value="">-- Select College --</option>
        <?php
        $colList = $conn->query("SELECT college_id, college_name FROM college ORDER BY college_name");
        while($cl = $colList->fetch_assoc()):
        ?>
            <option value="<?= $cl['college_id'] ?>" <?= ($filterCollege==$cl['college_id']?'selected':'') ?>><?= $cl['college_name'] ?></option>
        <?php endwhile; ?>
    </select>
    
    <select name="block" class= "search-box">
        <option value="">-- Select Block --</option>
        <?php
        $blockList = $conn->query("SELECT block_id, block_no FROM block ORDER BY block_no");
        while($bl = $blockList->fetch_assoc()):
        ?>
            <option value="<?= $bl['block_id'] ?>" <?= ($filterBlock==$bl['block_id']?'selected':'') ?>><?= $bl['block_no'] ?></option>
        <?php endwhile; ?>
    </select>

    <select name="house" id="houseSelect" class="search-box">
    <option value="">-- Select House --</option>
    <!-- Options will be loaded dynamically -->
    </select>

    <button type="submit" class="btn">Search</button>
</form>

<div class="table-scroll">
<table class="report-table">
<thead>
<tr>
    <th>No</th>
    <th>College</th>
    <th>House</th>
    <th>Room</th>
    <th>Matric No</th>
    <th>Student Name</th>
    
    <th>Check-in Date</th>
</tr>
</thead>
<tbody>
<?php if($studentList->num_rows > 0):
    $no = 1;
    while($st = $studentList->fetch_assoc()):
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $st['college_name'] ?></td>
    <td><?= $st['house_code'] ?></td>
    <td><?= $st['room_no'] ?></td>
    <td><?= $st['matrix_no'] ?></td>
    <td><?= $st['student_name'] ?></td>

    <td><?= date('d M Y', strtotime($st['checkin_date'])) ?></td>
</tr>
<?php endwhile; else: ?>
<tr class="no-student"><td colspan="7">No students found</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>


<!-- CHARTS PIE + BAR -->
<div class="chart-container" style="display:flex; gap:20px; flex-wrap:nowrap; justify-content:center; align-items:flex-start;">
    <!-- Pie Chart: sentiasa visible -->
    <div class="chart-box" style="min-width:300px; max-width:500px; height:400px;">
        <h3 class="section-title">Application Status Distribution</h3>
        <canvas id="appStatusChart"></canvas>
    </div>

    <!-- House Utilization -->
    <div class="chart-box" style="min-width:300px; max-width:500px; min-height:400px;">
        <h3 class="section-title">House Utilization Overview (by Block)</h3>
        <select id="collegeSelect" style="margin-bottom:10px;"  class= "search-box">
            <option value="">-- Select College --</option>
            <?php while($c = $colleges->fetch_assoc()): ?>
                <option value="<?= $c['college_id'] ?>"><?= $c['college_name'] ?></option>
            <?php endwhile; ?>
        </select>
        <div id="houseReportContainer"></div>
    </div>
</div>

<!-- APPLICATION STATUS TABLE -->
<h3 class="section-title">Student Application Status</h3>
<table class="report-table">
<thead>
<tr><th>Status</th><th>Total</th></tr>
</thead>
<tbody>
<?php while($a = $appTable->fetch_assoc()): ?>
<tr>
    <td><?= $a['status'] ?></td>
    <td><?= $a['total'] ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>


<!-- EXCEPTION REPORT -->
<h3 class="section-title">🚨 Exception / Alert Report</h3>
<h4>Approved but Not Yet Registered</h4>
<table class="report-table">
<thead>
<tr><th>Matrix No</th><th>Student Name</th><th>Status</th></tr>
</thead>
<tbody>
<?php if($exception->num_rows > 0): ?>
<?php while($e = $exception->fetch_assoc()): ?>
<tr>
<td><?= $e['matrix_no'] ?></td>
<td><?= $e['student_name'] ?></td>
<td><?= $e['status'] ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="3">No exception found</td></tr>
<?php endif; ?>
</tbody>
</table>

<h4>Pending Student Applications</h4>
<div class="table-scroll">
<table class="report-table">
<thead>
<tr>
    <th>Application ID</th>
    <th>Matrix No</th>
    <th>Student Name</th>
    <th>Applied Date</th>
    <th>Action Required</th>
</tr>
</thead>
<tbody>
<?php if($pendingApp->num_rows > 0): ?>
    <?php while($p = $pendingApp->fetch_assoc()): ?>
    <tr>
        <td><?= $p['application_id'] ?></td>
        <td><?= $p['matrix_no'] ?></td>
        <td><?= $p['student_name'] ?></td>
        <td><?= date('d M Y', strtotime($p['applied_date'])) ?></td>
        <td>
            <a class="alert pending" 
               href="staff_view_app.php?application_id=<?= $p['application_id'] ?>">
               Review
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5">No pending application</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

</div> <!-- END DASHBOARD -->
</div> <!-- END CONTAINER -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Pie chart - Application Status
new Chart(document.getElementById('appStatusChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($appStatus) ?>,
        datasets: [{ data: <?= json_encode($appTotal) ?>, backgroundColor: ['#2a9d8f','#f4a261','#e63946'] }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}} }
});

// AJAX untuk house report per college
$('#collegeSelect').on('change', function(){
    let collegeId = $(this).val();
    if(!collegeId){
        $('#houseReportContainer').html('');
        return;
    }
    $.get('load_house_report.php', {college_id: collegeId}, function(html){
        $('#houseReportContainer').html(html);

        // render bar chart setelah load
        const ctx = document.querySelector('#houseChart');
        if(ctx){
            const houseCode = JSON.parse(ctx.dataset.housecode);
            const currentLoad = JSON.parse(ctx.dataset.currentload);
            const actualLoad = JSON.parse(ctx.dataset.actualload);

            new Chart(ctx, {
                type:'bar',
                data:{
                    labels: houseCode,
                    datasets:[
                        {label:'Current Load', data:currentLoad, backgroundColor:'#457b9d'},
                        {label:'Total Actual Load', data:actualLoad, backgroundColor:'#a8dadc'}
                    ]
                },
                options:{ responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true}}, plugins:{legend:{position:'bottom'}} }
            });
        }
    });
});


$(document).ready(function(){

    // When College is changed → load Blocks
    $('select[name="college"]').on('change', function(){
        let collegeId = $(this).val();
        $.get('load_block_dpdown.php', {college_id: collegeId, selected_block: ''}, function(data){
            $('select[name="block"]').html(data);
            $('#houseSelect').html('<option value="">-- Select House --</option>'); // reset house
        });
    });

    // When Block is changed → load Houses
    $('select[name="block"]').on('change', function(){
        let blockId = $(this).val();
        let selectedHouse = '<?= $filterHouse ?? '' ?>'; // current selected house
        $.get('load_house_dpdown.php', {block_id: blockId, selected_house: selectedHouse}, function(data){
            $('#houseSelect').html(data);
        });
    });

    // --- Auto-load Blocks & Houses after page reload ---
    let selectedCollege = '<?= $filterCollege ?? '' ?>';
    let selectedBlock = '<?= $filterBlock ?? '' ?>';
    let selectedHouse = '<?= $filterHouse ?? '' ?>';

    if(selectedCollege){
        // Load Blocks for selected college
        $.get('load_block_dpdown.php', {college_id: selectedCollege, selected_block: selectedBlock}, function(data){
            $('select[name="block"]').html(data);

            if(selectedBlock){
                // Load Houses for selected block
                $.get('load_house_dpdown.php', {block_id: selectedBlock, selected_house: selectedHouse}, function(data){
                    $('#houseSelect').html(data);
                });
            }
        });
    }

});

</script>
</body>
</html>
