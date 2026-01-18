<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Fetch blocks and colleges
$blocks = mysqli_query($conn, "SELECT b.block_id, b.block_no, c.college_name FROM block b JOIN college c ON b.college_id = c.college_id");

// Fetch residences (houses)
$houses = mysqli_query($conn, "
    SELECT 
        h.house_id, 
        h.house_code, 
        h.actual_load, 
        h.current_load,
        b.block_no, 
        c.college_name
    FROM house h
    JOIN block b ON h.block_id = b.block_id
    JOIN college c ON b.college_id = c.college_id
    ORDER BY h.house_id ASC
");

// Fetch rooms
$rooms = mysqli_query($conn, "
    SELECT r.room_id, r.room_no, r.capacity, r.current_capacity, h.house_code
    FROM room r
    JOIN house h ON r.house_id = h.house_id
    ORDER BY r.room_id ASC
");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Register Residence & Rooms - e-Kolej UTeM</title>
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
.section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
.form-table-wrapper { display: flex; gap: 30px; align-items: flex-start; }
.form-container { flex: 1; }
.table-container { flex: 1; max-height: 250px; overflow-y: auto; border: 1px solid #ccc; border-radius: 6px; background: white; color: black; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 10px; border: 1px solid #ccc; font-size: 14px; }
th { background: #003366; color: white; position: sticky; top: 0; }
h2 { color: #003366; margin-bottom: 15px; }
input, select { padding: 8px; width: 100%; margin-top: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
.btn-group { display: flex; gap: 10px; }
button { padding: 8px 15px; background: #003366; color: white; border: none; border-radius: 4px; cursor: pointer; }
button.cancel { background: #888; }
label { color: black; font-weight: bold; }
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
        <a href="staff_reg_room.php"style="font-weight:bold;">🚪 Register Residence/Room<br><br></a>
        <a href="staff_assign_room.php">🔑 Room Assignment<br><br></a>
        <a href="staff_report.php">📊 Report<br><br></a>
    </nav>

    <form action="logout.php" method="post" style="margin-top: auto;">
        <button type="submit" class="signout-btn">Sign Out</button>
    </form>
</div>

<div class="dashboard-container">

    <!-- =======================
         SECTION 1: RESIDENCE
    ======================== -->
    <div class="section">
        <h2>Register Residence (Kediaman)</h2>
        <div class="form-table-wrapper">

            <!-- FORM -->
            <div class="form-container">
                <form action="staff_reg_house_process.php" method="POST">

                    <label>Select College:</label>
                    <select id="college_select" name="college_name" required>
                        <option value="">-- Select College --</option>
                        <?php  
                        mysqli_data_seek($blocks, 0);
                        $college_unique = [];
                        while ($b = mysqli_fetch_assoc($blocks)) {
                            $college_unique[$b['college_name']] = true;
                        }
                        foreach ($college_unique as $cname => $_): ?>
                            <option value="<?= $cname ?>"><?= $cname ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Select Block:</label>
                    <select name="block_id" id="block_select" required disabled>
                        <option value="">-- Select Block --</option>
                    </select>

                    <label>Residence Unit:</label>
                    <input type="text" name="house_code" required placeholder="(e.g., SL-L-1-01)">

                    <label>Actual Load:</label>
                    <input type="number" name="actual_load" min="1" required>

                    <div class="btn-group">
                        <button type="submit">Add</button>
                        <button type="reset" class="cancel">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Residence Unit</th>
                            <th>Actual Load</th>
                            <th>Current Load</th>
                        </tr>
                    </thead>
                    <tbody id="house_table_body">
                    <?php
                    mysqli_data_seek($houses, 0);
                    while ($h = mysqli_fetch_assoc($houses)) {
                        echo "<tr data-college='{$h['college_name']}' style='display:none'>";
                        echo "<td>{$h['house_code']}</td>";
                        echo "<td>{$h['actual_load']}</td>";
                        echo "<td>{$h['current_load']}</td>";
                        echo "</tr>";
                    }
                    ?>
<tr id="no_house_row" style="display:none;">
    <td colspan="3" style="text-align:center; font-style:italic; color:#888;">
        No residence registered for this college yet.
    </td>
</tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =======================
         SECTION 2: ROOM
    ======================== -->
    <div class="section">
        <h2>Register Room (Bilik)</h2>
        <div class="form-table-wrapper">

            <!-- FORM -->
            <div class="form-container">
                <form action="staff_reg_room_process.php" method="POST">

                    <label>Select Residence Unit:</label>
                    <select id="house_select" name="house_code" required>
                        <option value="">-- Select Residence --</option>
                        <?php
                        mysqli_data_seek($houses, 0);
                        while ($h = mysqli_fetch_assoc($houses)) {
                            echo "<option value='{$h['house_code']}'>{$h['house_code']}</option>";
                        }
                        ?>
                    </select>

                    <label>Room Code:</label>
                    <input type="text" name="room_no" required placeholder="Enter room code (e.g., A)">

                    <label>Room Capacity:</label>
                    <input type="number" name="capacity" min="1" required>

                    <div class="btn-group">
                        <button type="submit">Add</button>
                        <button type="reset" class="cancel">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Residence</th>
                            <th>Room No</th>
                            <th>Capacity</th>
                            <th>Current Capacity</th>
                        </tr>
                    </thead>
                    <tbody id="room_table_body">
                        <?php while ($r = mysqli_fetch_assoc($rooms)): ?>
                        <tr data-house="<?= $r['house_code'] ?>" style="display:none">
                            <td><?= $r['house_code'] ?></td>
                            <td><?= $r['room_no'] ?></td>
                            <td><?= $r['capacity'] ?></td>
                            <td><?= $r['current_capacity'] ?></td>
                        </tr>
                        <?php endwhile; ?>
<tr id="no_room_row" style="display:none;">
    <td colspan="4" style="text-align:center; font-style:italic; color:#888;">
        No room registered for this residence yet.
    </td>
</tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
</div>

<script>
// Block dataset
const blockData = [
<?php 
mysqli_data_seek($blocks, 0);
while ($b = mysqli_fetch_assoc($blocks)): ?>
    ,{
        block_id: "<?= $b['block_id'] ?>",
        block_no: "<?= $b['block_no'] ?>",
        college_name: "<?= $b['college_name'] ?>",
    },
<?php endwhile; ?>
];

const collegeSelect = document.getElementById("college_select");
const blockSelect = document.getElementById("block_select");
const houseRows = document.querySelectorAll("#house_table_body tr");
const houseInput = document.querySelector("input[name='house_code']");
const houseSelect = document.getElementById("house_select");
const roomRows = document.querySelectorAll("#room_table_body tr");
const form = document.querySelector("form[action='staff_reg_house_process.php']");

// Update blocks and filter residence table
collegeSelect.addEventListener("change", () => {
    const selectedCollege = collegeSelect.value;
    let visibleCount = 0;

    // Update block dropdown (keep your existing code)
    blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
    if (!selectedCollege) {
        blockSelect.disabled = true;
    } else {
        const filtered = blockData.filter(b => b.college_name === selectedCollege);
        filtered.forEach(b => {
            let opt = document.createElement("option");
            opt.value = b.block_id;
            opt.textContent = b.block_no;
            blockSelect.appendChild(opt);
        });
        blockSelect.disabled = false;
    }

    // Filter house table
    houseRows.forEach(row => {
        if (row.dataset.college === selectedCollege) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });

    // Show / hide "no house" message
    document.getElementById("no_house_row").style.display =
        (selectedCollege && visibleCount === 0) ? "" : "none";
});


// Client-side format check for residence
form.addEventListener("submit", function(e) {
    const block = blockSelect.options[blockSelect.selectedIndex].text.trim();
    const houseVal = houseInput.value.trim();

    if (!block) {
        alert("Please select a block first.");
        e.preventDefault();
        return;
    }

    const pattern = new RegExp("^" + block + "-([1-9])-([0]?[1-9]|1[0-2])$");
    if (!pattern.test(houseVal)) {
        alert(`Invalid Residence Unit!\nMust match ${block}-LEVEL-HOUSE (e.g., ${block}-1-01)`);
        e.preventDefault();
        return;
    }
});

// Filter room table based on selected residence
houseSelect.addEventListener("change", () => {
    const selectedHouse = houseSelect.value;
    let visibleRooms = 0;

    roomRows.forEach(row => {
        if (row.dataset.house === selectedHouse) {
            row.style.display = "";
            visibleRooms++;
        } else {
            row.style.display = "none";
        }
    });

    document.getElementById("no_room_row").style.display =
        (selectedHouse && visibleRooms === 0) ? "" : "none";
});

</script>
</body>
</html>
