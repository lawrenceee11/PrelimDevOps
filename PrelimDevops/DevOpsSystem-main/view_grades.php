<?php
session_start();
include 'config/plugins.php';
require 'config/dbcon.php';

// ==========================
// CHECK USERNAME
// ==========================
if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("Invalid student.");
}
$username = $conn->real_escape_string($_GET['username']);

// ==========================
// GET STUDENT INFO
// ==========================
$student_sql = "SELECT firstname, lastname, course, year, section 
                FROM enroll 
                WHERE username='$username' LIMIT 1";
$student_result = $conn->query($student_sql);
if (!$student_result || $student_result->num_rows === 0) die("Student not found.");
$student = $student_result->fetch_assoc();

// ==========================
// FETCH SUBJECTS (AUTO)
// ==========================
$subjects = [];
$subj = $conn->prepare(
    "SELECT name, instructor 
     FROM subjects 
     WHERE course = ? AND year_level = ?
     ORDER BY name"
);
$subj->bind_param("ss", $student['course'], $student['year']);
$subj->execute();
$res = $subj->get_result();
while ($r = $res->fetch_assoc()) {
    $subjects[] = $r;
}
$subj->close();

// ==========================
// DELETE GRADE (AJAX)
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $d = $conn->prepare("DELETE FROM grades WHERE id = ? AND username = ?");
    $d->bind_param('is', $del_id, $username);
    $d->execute();
    echo json_encode(['success' => $d->affected_rows > 0]);
    exit;
}

// ==========================
// SAVE GRADES
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grades'])) {
    foreach ($_POST['grades'] as $data) {

        $id = isset($data['id']) ? intval($data['id']) : 0;
        $subject = trim($data['subject'] ?? '');
        $instructor = trim($data['instructor'] ?? '');

        if ($subject === '') continue;

        $prelim  = floatval($data['prelim'] ?? 0);
        $midterm = floatval($data['midterm'] ?? 0);
        $finals  = floatval($data['finals'] ?? 0);

        $average = round(($prelim + $midterm + $finals) / 3, 2);
        $remarks = $average >= 75 ? 'Passed' : 'Failed';

        if ($id > 0) {
            $u = $conn->prepare(
                "UPDATE grades 
                 SET prelim=?, midterm=?, finals=?, average=?, remarks=? 
                 WHERE id=? AND username=?"
            );
            $u->bind_param('ddddsis', $prelim, $midterm, $finals, $average, $remarks, $id, $username);
            $u->execute();
            $u->close();
        } else {
            $i = $conn->prepare(
                "INSERT INTO grades 
                (username, subject, instructor, prelim, midterm, finals, average, remarks)
                VALUES (?,?,?,?,?,?,?,?)"
            );
            $i->bind_param('sssdddds', $username, $subject, $instructor, $prelim, $midterm, $finals, $average, $remarks);
            $i->execute();
            $i->close();
        }
    }

    $_SESSION['status'] = 'Grades saved successfully!';
    header("Location: view_grades.php?username=" . urlencode($username));
    exit;
}

// ==========================
// FETCH GRADES
// ==========================
$grades = [];
$g = $conn->query("SELECT * FROM grades WHERE username='$username' ORDER BY subject");
while ($row = $g->fetch_assoc()) $grades[] = $row;
?>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="container my-4">

<?php if (isset($_SESSION['status'])): ?>
<div class="alert alert-success"><?= $_SESSION['status']; unset($_SESSION['status']); ?></div>
<?php endif; ?>

<h3>Student Grades</h3>

<div class="card mb-3">
<div class="card-body">
<strong><?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?></strong><br>
Course: <?= htmlspecialchars($student['course']) ?><br>
Year: <?= htmlspecialchars($student['year']) ?><br>
Section: <?= htmlspecialchars($student['section']) ?>
</div>
</div>

<form method="post">
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>Subject</th>
<th>Instructor</th>
<th>Prelim</th>
<th>Midterm</th>
<th>Finals</th>
<th>Average</th>
<th>Remarks</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php $index = 0; ?>

<?php if (!empty($grades)): ?>
<?php foreach ($grades as $row): ?>
<tr>
<td>
<input type="hidden" name="grades[<?= $index ?>][id]" value="<?= $row['id'] ?>">
<input type="hidden" name="grades[<?= $index ?>][subject]" value="<?= htmlspecialchars($row['subject']) ?>">
<?= htmlspecialchars($row['subject']) ?>
</td>

<td>
<input type="hidden" name="grades[<?= $index ?>][instructor]" value="<?= htmlspecialchars($row['instructor']) ?>">
<?= htmlspecialchars($row['instructor']) ?>
</td>

<td><input type="number" name="grades[<?= $index ?>][prelim]" value="<?= $row['prelim'] ?>" class="form-control"></td>
<td><input type="number" name="grades[<?= $index ?>][midterm]" value="<?= $row['midterm'] ?>" class="form-control"></td>
<td><input type="number" name="grades[<?= $index ?>][finals]" value="<?= $row['finals'] ?>" class="form-control"></td>

<td><?= $row['average'] ?></td>
<td><?= $row['remarks'] ?></td>

<td>
<button type="button" class="btn btn-danger btn-sm deleteRow" data-id="<?= $row['id'] ?>">Delete</button>
</td>
</tr>
<?php $index++; endforeach; ?>

<?php else: ?>
<?php foreach ($subjects as $s): ?>
<tr>
<td>
<input type="hidden" name="grades[<?= $index ?>][subject]" value="<?= htmlspecialchars($s['name']) ?>">
<?= htmlspecialchars($s['name']) ?>
</td>

<td>
<input type="hidden" name="grades[<?= $index ?>][instructor]" value="<?= htmlspecialchars($s['instructor']) ?>">
<?= htmlspecialchars($s['instructor']) ?>
</td>

<td><input type="number" name="grades[<?= $index ?>][prelim]" class="form-control"></td>
<td><input type="number" name="grades[<?= $index ?>][midterm]" class="form-control"></td>
<td><input type="number" name="grades[<?= $index ?>][finals]" class="form-control"></td>

<td></td>
<td></td>
<td></td>
</tr>
<?php $index++; endforeach; ?>
<?php endif; ?>

</tbody>
</table>

<button type="submit" class="btn btn-success">Save Grades</button>
<a href="javascript:history.back()" class="btn btn-secondary">← Back</a>

</form>
</div>


<style>
.fail { color: #fff; background-color: #dc3545; font-weight: bold; text-align: center; }
.pass { color: #155724; background-color: #d4edda; font-weight: bold; text-align: center; }
</style>

<script>
let rowIndex = <?= count($grades) ?>;

// Add new row dynamically
document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.getElementById('gradesTable').getElementsByTagName('tbody')[0];
    const row = tbody.insertRow();
    row.innerHTML = `
        <td><input type="text" name="grades[${rowIndex}][subject]" class="form-control"></td>
        <td><input type="text" name="grades[${rowIndex}][instructor]" class="form-control"></td>
        <td><input type="number" step="0.01" min="0" max="100" name="grades[${rowIndex}][prelim]" class="form-control gradeInput"></td>
        <td><input type="number" step="0.01" min="0" max="100" name="grades[${rowIndex}][midterm]" class="form-control gradeInput"></td>
        <td><input type="number" step="0.01" min="0" max="100" name="grades[${rowIndex}][finals]" class="form-control gradeInput"></td>
        <td class="average"></td>
        <td class="remarks"></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
    `;
    rowIndex++;
});

// Remove row (unsaved rows)
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('removeRow')) {
        e.target.closest('tr').remove();
        return;
    }

    // Delete persisted row via AJAX
    if (e.target.classList.contains('deleteRow')) {
        if (!confirm('Delete this grade from the database? This cannot be undone.')) return;
        var btn = e.target;
        var id = btn.getAttribute('data-id');
        var tr = btn.closest('tr');
        // disable button to prevent duplicate requests
        btn.disabled = true;
        fetch(window.location.pathname + '?username=' + encodeURIComponent('<?= htmlspecialchars($username) ?>'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'delete_id=' + encodeURIComponent(id) + '&username=' + encodeURIComponent('<?= htmlspecialchars($username) ?>')
        }).then(function(res){
            if (!res.ok) {
                return res.text().then(function(t){ throw new Error('Server returned ' + res.status + ': ' + t); });
            }
            var ct = res.headers.get('content-type') || '';
            if (ct.indexOf('application/json') !== -1) {
                return res.json();
            }
            return res.text().then(function(t){ throw new Error('Invalid JSON response: ' + t); });
        }).then(function(json){
            btn.disabled = false;
            if (json && json.success) {
                tr.remove();
                var container = document.querySelector('.container');
                if (container) {
                    // Remove any previous grade-action success alerts so message doesn't duplicate
                    var prev = container.querySelectorAll('.alert.alert-success[data-for="grade-action"]');
                    prev.forEach(function(el){ el.remove(); });
                    var msg = document.createElement('div');
                    msg.className = 'alert alert-success';
                    msg.setAttribute('data-for','grade-action');
                    var text = json.message || 'Grades saved successfully!';
                    var safeText = text.replace(/</g, '&lt;');
                    msg.textContent = safeText;
                    container.insertBefore(msg, container.firstChild);
                    // auto-dismiss after 4 seconds
                    setTimeout(function(){ if (msg.parentNode) msg.parentNode.removeChild(msg); }, 4000);
                }
            } else {
                btn.disabled = false;
                alert(json && json.message ? json.message : 'Delete failed');
            }
        }).catch(function(err){
            btn.disabled = false;
            console.error('Delete request error:', err);
            alert('Request failed: ' + (err && err.message ? err.message : 'unknown'));
        });
    }
});

// Live calculation of average & remarks
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('gradeInput')) {
        const row = e.target.closest('tr');
        const prelimInput = row.querySelector('input[name*="[prelim]"]');
        const midInput = row.querySelector('input[name*="[midterm]"]');
        const finalsInput = row.querySelector('input[name*="[finals]"]');
        let prelim = parseFloat(prelimInput.value) || 0;
        let midterm = parseFloat(midInput.value) || 0;
        let finals = parseFloat(finalsInput.value) || 0;
        // Clamp to 0..100
        prelim = Math.max(0, Math.min(100, prelim));
        midterm = Math.max(0, Math.min(100, midterm));
        finals = Math.max(0, Math.min(100, finals));
        // write back sanitized values
        prelimInput.value = prelim;
        midInput.value = midterm;
        finalsInput.value = finals;
        const average = ((prelim + midterm + finals) / 3).toFixed(2);

        const avgCell = row.querySelector('.average');
        const remarksCell = row.querySelector('.remarks');

        avgCell.textContent = average;
        remarksCell.textContent = average >= 75 ? 'Passed' : 'Failed';

        if (average >= 75) {
            avgCell.classList.remove('fail'); avgCell.classList.add('pass');
            remarksCell.classList.remove('fail'); remarksCell.classList.add('pass');
        } else {
            avgCell.classList.remove('pass'); avgCell.classList.add('fail');
            remarksCell.classList.remove('pass'); remarksCell.classList.add('fail');
        }
    }
});

// Auto-dismiss any server-rendered success alerts after 4s
document.addEventListener('DOMContentLoaded', function(){
  var serverAlerts = document.querySelectorAll('.container .alert.alert-success:not([data-no-autoclose])');
  serverAlerts.forEach(function(a){
    setTimeout(function(){ if (a.parentNode) a.parentNode.removeChild(a); }, 4000);
  });
});
</script>
