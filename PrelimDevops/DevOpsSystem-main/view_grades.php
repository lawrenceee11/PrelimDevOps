<?php
SESSION_START();
include 'config/plugins.php';
require 'config/dbcon.php';

// Check if username is provided
if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("Invalid student.");
}

$username = $conn->real_escape_string($_GET['username']);

// ==========================
// GET STUDENT INFO
// ==========================
$student_sql = "SELECT firstname, lastname, course, year, section FROM enroll WHERE username='$username' LIMIT 1";
$student_result = $conn->query($student_sql);
if (!$student_result || $student_result->num_rows === 0) die("Student not found.");
$student = $student_result->fetch_assoc();

// ==========================
// HANDLE DELETE REQUEST (AJAX or form)
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    // accept posted username fallback so AJAX can send it in POST body
    $posted_username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : null;
    $target_username = $posted_username ?: $username;

    $d = $conn->prepare("DELETE FROM grades WHERE id = ? AND username = ?");
    if ($d) {
        $d->bind_param('is', $del_id, $target_username);
        $d->execute();
        $affected = $d->affected_rows;
        $d->close();

        // Clean any buffered output to ensure valid JSON
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        if ($affected > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Grade deleted']);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Not found or not authorized']);
            exit;
        }
    } else {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
}

// ==========================
// SAVE GRADES IF POSTED
// Improved: use record id when available and skip empty subject rows to avoid invisible entries
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grades']) && is_array($_POST['grades'])) {
    foreach ($_POST['grades'] as $data) {
        // Trim and escape values
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $subject = isset($data['subject']) ? trim($conn->real_escape_string($data['subject'])) : '';
        $instructor = isset($data['instructor']) ? trim($conn->real_escape_string($data['instructor'])) : '';

        // Skip rows without a subject (these create empty-looking rows)
        if ($subject === '') continue;

        $prelim  = isset($data['prelim']) && $data['prelim'] !== '' ? floatval($data['prelim']) : 0;
        $prelim = max(0, min(100, $prelim));
        $midterm = isset($data['midterm']) && $data['midterm'] !== '' ? floatval($data['midterm']) : 0;
        $midterm = max(0, min(100, $midterm));
        $finals  = isset($data['finals']) && $data['finals'] !== '' ? floatval($data['finals']) : 0;
        $finals = max(0, min(100, $finals));

        $gradesEntered = [$prelim, $midterm, $finals];
        $average = !empty($gradesEntered) ? round(array_sum($gradesEntered) / count($gradesEntered), 2) : 0;
        $remarks = $average >= 75 ? 'Passed' : 'Failed';

        if ($id > 0) {
            // Update by id (reliable even if subject text changed)
            $u = $conn->prepare("UPDATE grades SET subject = ?, instructor = ?, prelim = ?, midterm = ?, finals = ?, average = ?, remarks = ? WHERE id = ? AND username = ?");
            if ($u) {
                // Types: subject (s), instructor (s), prelim (d), midterm (d), finals (d), average (d), remarks (s), id (i), username (s)
                $u->bind_param('ssddddsis', $subject, $instructor, $prelim, $midterm, $finals, $average, $remarks, $id, $username);
                if (!$u->execute()) {
                    echo "<div class='alert alert-danger'>Error updating $subject: " . htmlspecialchars($u->error) . "</div>";
                }
                $u->close();
            } else {
                echo "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
            }
        } else {
            // Insert new record
            $ins = $conn->prepare("INSERT INTO grades (username, subject, instructor, prelim, midterm, finals, average, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($ins) {
                // Types: username (s), subject (s), instructor (s), prelim (d), midterm (d), finals (d), average (d), remarks (s)
                $ins->bind_param('sssdddds', $username, $subject, $instructor, $prelim, $midterm, $finals, $average, $remarks);
                if (!$ins->execute()) {
                    echo "<div class='alert alert-danger'>Error inserting $subject: " . htmlspecialchars($ins->error) . "</div>";
                }
                $ins->close();
            } else {
                echo "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
            }
        }
    }

    // Use Post-Redirect-Get to avoid duplicate client-side alerts: set session status and redirect
    $_SESSION['status'] = 'Grades saved successfully!';
    header('Location: view_grades.php?username=' . urlencode($username));
    exit;
}

// ==========================
// FETCH GRADES
// ==========================
$grades_sql = "SELECT id, subject, instructor, prelim, midterm, finals, average, remarks FROM grades WHERE username='$username' ORDER BY subject";
$grades_result = $conn->query($grades_sql);

$grades = [];
if ($grades_result && $grades_result->num_rows > 0) {
    while ($row = $grades_result->fetch_assoc()) $grades[] = $row;
}
?>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="container my-4">
    <?php if (isset($_SESSION['status'])): ?>
      <div class="alert alert-success mt-2"><?php echo htmlspecialchars($_SESSION['status']); unset($_SESSION['status']); ?></div>
    <?php endif; ?>
    <h1>Student Grades</h1>

    <!-- STUDENT INFO -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?></h5>
            <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
            <p class="mb-1"><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
            <p class="mb-1"><strong>Year:</strong> <?= htmlspecialchars(ucwords(strtolower($student['year']))) ?></p>
            <p class="mb-0"><strong>Section:</strong> <?= htmlspecialchars($student['section'] ?? '-') ?></p>
        </div>
    </div>

    <!-- GRADES FORM -->
    <form method="post">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Grades</h5>

                <table class="table table-striped table-bordered" id="gradesTable">
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
                                    <input type="hidden" name="grades[<?= $index ?>][id]" value="<?= intval($row['id']) ?>">
                                    <input type="text" name="grades[<?= $index ?>][subject]" class="form-control" value="<?= htmlspecialchars($row['subject']) ?>">
                                </td>
                                    <td><input type="text" name="grades[<?= $index ?>][instructor]" class="form-control" value="<?= htmlspecialchars($row['instructor']) ?>"></td>
                                    <td><input type="number" step="0.01" min="0" max="100" name="grades[<?= $index ?>][prelim]" class="form-control gradeInput" value="<?= $row['prelim'] ?>"></td>
                                    <td><input type="number" step="0.01" min="0" max="100" name="grades[<?= $index ?>][midterm]" class="form-control gradeInput" value="<?= $row['midterm'] ?>"></td>
                                    <td><input type="number" step="0.01" min="0" max="100" name="grades[<?= $index ?>][finals]" class="form-control gradeInput" value="<?= $row['finals'] ?>"></td>
                                    <td class="average"><?= $row['average'] ?></td>
                                    <td class="remarks"><?= htmlspecialchars($row['remarks']) ?></td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteRow" data-id="<?= intval($row['id']) ?>">Delete</button></td>
                                </tr>
                                <?php $index++; endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td><input type="text" name="grades[0][subject]" class="form-control"></td>
                                <td><input type="text" name="grades[0][instructor]" class="form-control"></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="grades[0][prelim]" class="form-control gradeInput"></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="grades[0][midterm]" class="form-control gradeInput"></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="grades[0][finals]" class="form-control gradeInput"></td>
                                <td class="average"></td>
                                <td class="remarks"></td>
                                <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <button type="button" class="btn btn-info mb-2" id="addRow">Add Subject</button>
                <br>
                <button type="submit" class="btn btn-success">Save Grades</button>
                <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
            </div>
        </div>
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
