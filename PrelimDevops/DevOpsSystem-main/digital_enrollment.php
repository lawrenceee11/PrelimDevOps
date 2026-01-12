<?php
SESSION_START();
include 'config/dbcon.php';

// Ensure appointments and subjects tables exist
$create_appointments = "CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  slots INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create_appointments);

$create_subjects = "CREATE TABLE IF NOT EXISTS subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  course VARCHAR(255) NOT NULL,
  instructor VARCHAR(255) DEFAULT NULL,
  year_level VARCHAR(10) DEFAULT NULL,
  hours INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create_subjects);

// Handle form submissions (add/delete) BEFORE any HTML output to allow header() redirects
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Add appointment
  if (isset($_POST['action']) && $_POST['action'] === 'add_appointment') {
    $date = $_POST['date'] ?? '';
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';

    // Normalize start time to H:i:s and ensure end time is set to start + 1 hour if missing
    if (!empty($start)) {
      $start = date('H:i:s', strtotime($start));
      if (empty($end)) {
        $end = date('H:i:s', strtotime($start . ' +1 hour'));
      } else {
        $end = date('H:i:s', strtotime($end));
      }
    }

    // Check for conflicts (overlap) on same date: existing.start < new_end AND existing.end > new_start
    $conflict = false;
    if (!empty($date) && !empty($start) && !empty($end)) {
      $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE date = ? AND NOT (end_time <= ? OR start_time >= ?)");
      $chk->bind_param('sss', $date, $start, $end);
      $chk->execute();
      $cres = $chk->get_result();
      if ($cres) {
        $crow = $cres->fetch_assoc();
        if ($crow && intval($crow['cnt']) > 0) { $conflict = true; }
      }
      $chk->close();
    }

    if ($conflict) {
      $_SESSION['status'] = 'Conflict: an appointment overlaps that time on the selected date.';
      $_SESSION['status_type'] = 'danger';
      header("Location: digital_enrollment.php");
      exit;
    }

    $slots = intval($_POST['slots'] ?? 0);
    $stmt = $conn->prepare("INSERT INTO appointments (date,start_time,end_time,slots) VALUES (?,?,?,?)");
    $stmt->bind_param('sssi',$date,$start,$end,$slots);
    $stmt->execute();
    $_SESSION['status'] = 'Appointment added successfully.';
    $_SESSION['status_type'] = 'success';
    header("Location: digital_enrollment.php");
    exit;
  }

  // Delete appointment
  if (isset($_POST['delete_appointment'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    header("Location: digital_enrollment.php");
    exit;
  }

  // Add subject
  if (isset($_POST['action']) && $_POST['action'] === 'add_subject') {
    $name = $_POST['subject'] ?? '';
    $course = $_POST['course'] ?? '';
    $instr = $_POST['instructor'] ?? '';
    $year = $_POST['year_level'] ?? '';
    $hours = intval($_POST['hours'] ?? 0);
    $stmt = $conn->prepare("INSERT INTO subjects (name,course,instructor,year_level,hours) VALUES (?,?,?,?,?)");
    $stmt->bind_param('ssssi',$name,$course,$instr,$year,$hours);
    $stmt->execute();
    // set a success flash and flag to show a modal confirming the new subject
    $_SESSION['status'] = 'Subject added successfully.';
    $_SESSION['status_type'] = 'success';
    $_SESSION['show_subject_modal'] = true;
    $_SESSION['subject_added_name'] = $name;
    header("Location: digital_enrollment.php");
    exit;
  }

  // Delete subject
  if (isset($_POST['delete_subject'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    header("Location: digital_enrollment.php");
    exit;
  }

  // Approve student (validate subjects exist for student's course/year)
  if (isset($_POST['approve_student'])) {
    $id = intval($_POST['id']);
    // fetch student's course and year
    $q = $conn->prepare("SELECT course, year FROM enroll WHERE id = ?");
    $q->bind_param('i', $id);
    $q->execute();
    $qr = $q->get_result();
    $student = $qr ? $qr->fetch_assoc() : null;
    $q->close();
    $course = $student['course'] ?? '';
    $year = $student['year'] ?? '';

    // check for matching subjects
    $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM subjects WHERE course = ? AND year_level = ?");
    $chk->bind_param('ss', $course, $year);
    $chk->execute();
    $cres = $chk->get_result();
    $cnt = 0;
    if ($cres) { $crow = $cres->fetch_assoc(); $cnt = intval($crow['cnt']); }
    $chk->close();

    if ($cnt <= 0) {
      $_SESSION['status'] = 'Cannot approve: no subjects found for ' . ($course ?: 'the student\'s course') . ' Year ' . ($year ?: 'N/A') . '.';
      $_SESSION['status_type'] = 'danger';
      header("Location: digital_enrollment.php");
      exit;
    }

    $stmt = $conn->prepare("UPDATE enroll SET status = 'APPROVED' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $_SESSION['status'] = 'Student approved.';
    $_SESSION['status_type'] = 'success';
    header("Location: digital_enrollment.php");
    exit;
  }

  // Reject student
  if (isset($_POST['reject_student'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE enroll SET status = 'REJECTED' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $_SESSION['status'] = 'Student rejected.';
    $_SESSION['status_type'] = 'warning';
    header("Location: digital_enrollment.php");
    exit;
  }
}

// Now include plugins and sidebar (these output HTML)
include 'config/plugins.php';
include __DIR__ . '/sidebar.php';
?>

<div class="container-fluid my-4">
  <style>
    /* Ensure table cells wrap instead of being clipped */
    .table td { white-space: normal; word-break: break-word; }
  </style>

  <?php if (isset($_SESSION['status'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['status_type'] ?? 'info'); ?> alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($_SESSION['status']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['status'], $_SESSION['status_type']); ?>
  <?php endif; ?>

  <!-- Enrollment Scheduling Appointment -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="text-center mb-3">Enrollment Scheduling Appointment</h5>

      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Slots</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <form method="post">
              <td><input type="date" name="date" class="form-control" required></td>
              <td><input type="time" name="start_time" class="form-control" required></td>
              <td><input type="time" name="end_time" class="form-control" readonly required></td>
              <td><input type="number" name="slots" class="form-control" min="1" value="1" required></td>
              <td>
                <button type="submit" name="action" value="add_appointment" class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button>
              </td>
            </form>
          </tr>
<?php
$stmt = $conn->prepare("SELECT id, date, start_time, end_time, slots FROM appointments ORDER BY date, start_time");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $fmtDate = date("F j, Y | l", strtotime($row['date']));
  $fmtStart = date("h:i A", strtotime($row['start_time']));
  $fmtEnd = date("h:i A", strtotime($row['end_time']));
  echo "<tr>";
  echo "<td>" . $fmtDate . "</td>";
  echo "<td>" . $fmtStart . "</td>";
  echo "<td>" . $fmtEnd . "</td>";
  echo "<td>" . htmlspecialchars(
    $row['slots']
  ) . "</td>";
  echo "<td>
        <form method=\"post\" onsubmit=\"return confirm('Delete this appointment?');\" style=\"display:inline;\">\n          <input type=\"hidden\" name=\"id\" value=\"" . $row['id'] . "\">\n          <button type=\"submit\" name=\"delete_appointment\" value=\"1\" class=\"btn btn-danger btn-sm\"><i class=\"fa fa-trash\"></i></button>\n        </form>
        </td>";
  echo "</tr>";
}
?>

        </tbody>
      </table>
    </div>
  </div>

  <!-- Student Approval Request -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="text-center mb-3">Student Approval Request</h5>

      <table class="table table-bordered text-center">
        <thead class="table-light">
          <tr>
            <th>Appointment Date</th>
            <th>Time</th>
            <th>Username</th>
            <th>Name</th>
            <th>Course</th>
            <th>Year</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $sq = $conn->prepare("SELECT * FROM enroll WHERE status = 'PENDING' ORDER BY id ASC");
            $sq->execute();
            $r = $sq->get_result();
            if ($r && $r->num_rows > 0) {
              while ($s = $r->fetch_assoc()) {
                $sid = (int)$s['id'];
                $appointment_date = htmlspecialchars($s['appointment_date']);
                $time = htmlspecialchars($s['time']);
                $username = htmlspecialchars($s['username']);
                $lastname = htmlspecialchars($s['lastname']);
                $firstname = htmlspecialchars($s['firstname']);
                $middlename = htmlspecialchars($s['middlename']);
                $elemName = htmlspecialchars($s['elemName']);
                $elemYear = htmlspecialchars($s['elemYear']);
                $juniorName = htmlspecialchars($s['juniorName']);
                $juniorYear = htmlspecialchars($s['juniorYear']);
                $seniorName = htmlspecialchars($s['seniorName']);
                $seniorYear = htmlspecialchars($s['seniorYear']);
                $sex = htmlspecialchars($s['sex']);
                $dob = htmlspecialchars($s['dob']);
                $phonenumber = htmlspecialchars($s['phonenumber']);
                $guardianName = htmlspecialchars($s['guardianName']);
                $guardianPhoneNumber = htmlspecialchars($s['guardianPhoneNumber']);
                $guardianAddress = htmlspecialchars($s['guardianAddress']);
                $course = htmlspecialchars($s['course']);
                $yearRaw = $s['year'] ?? '';
                $year = htmlspecialchars(ucwords(strtolower($yearRaw)));
                $section = htmlspecialchars($s['section'] ?? '');
                $email = htmlspecialchars($s['email']);
                $name = htmlspecialchars(trim($lastname . ', ' . $firstname));
                echo '<tr>';
                echo '<td>' . $appointment_date . '</td>';
                echo '<td>' . htmlspecialchars($time) . '</td>';
                echo '<td>' . $username . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $course . '</td>';
                echo '<td style="white-space:normal; word-break:break-word;" title="' . htmlspecialchars($yearRaw) . '">' . $year . '</td>';
                echo '<td><span class="text-dark">Pending</span></td>'; 
                // compute number of matching subjects for this student's course/year
                $subCount = 0;
                $sc = $conn->prepare("SELECT COUNT(*) AS cnt FROM subjects WHERE course = ? AND year_level = ?");
                $sc->bind_param('ss', $s['course'], $s['year']);
                $sc->execute();
                $sr = $sc->get_result();
                if ($sr) { $srow = $sr->fetch_assoc(); $subCount = intval($srow['cnt']); }
                $sc->close();

                $dataAttrs = ' data-id="' . $sid . '"'
                  . ' data-username="' . $username . '"'
                  . ' data-email="' . $email . '"'
                  . ' data-lastname="' . $lastname . '"'
                  . ' data-firstname="' . $firstname . '"'
                  . ' data-middlename="' . $middlename . '"'
                  . ' data-elemname="' . $elemName . '"'
                  . ' data-elemyear="' . $elemYear . '"'
                  . ' data-juniorname="' . $juniorName . '"'
                  . ' data-junioryear="' . $juniorYear . '"'
                  . ' data-seniorname="' . $seniorName . '"'
                  . ' data-senioryear="' . $seniorYear . '"'
                  . ' data-sex="' . $sex . '"'
                  . ' data-dob="' . $dob . '"'
                  . ' data-phonenumber="' . $phonenumber . '"'
                  . ' data-guardianname="' . $guardianName . '"'
                  . ' data-guardianphone="' . $guardianPhoneNumber . '"'
                  . ' data-guardianaddress="' . $guardianAddress . '"'
                  . ' data-course="' . $course . '"'
                  . ' data-year="' . $year . '"'
                  . ' data-section="' . $section . '"'
                  . ' data-appointment_date="' . $appointment_date . '"'
                  . ' data-time="' . htmlspecialchars($time) . '"'
                  . ' data-subjects-count="' . $subCount . '"';
                echo '<td><button type="button" class="btn btn-sm btn-primary student-open" data-source="approval"' . $dataAttrs . '>Open</button></td>';

                echo '</tr>';
              }
            } else {
              echo '<tr><td colspan="8">No pending students.</td></tr>';
            }
            $sq->close();
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Student details modal -->
  <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="studentModalLabel">Student Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="studentDetailForm">
            <div class="">
              <label class="form-label">Username</label>
              <input type="text" readonly class="form-control" id="mUsername">
            </div>
            <div class="">
              <label class="form-label">Email</label>
              <input type="email" readonly class="form-control" id="mEmail">
            </div>

            <div class="">
              <label class="form-label">Last name</label>
              <input type="text" readonly class="form-control" id="mLastname">
            </div>
            <div class="">
              <label class="form-label">First name</label>
              <input type="text" readonly class="form-control" id="mFirstname">
            </div>
            <div class="">
              <label class="form-label">Middle name</label>
              <input type="text" readonly class="form-control" id="mMiddlename">
            </div>

            <div class="">
              <label class="form-label">Elementary (name)</label>
              <input type="text" readonly class="form-control" id="mElemName">
            </div>
            <div class="">
              <label class="form-label">Elementary (year)</label>
              <input type="text" readonly class="form-control" id="mElemYear">
            </div>
            <div class="">
              <label class="form-label">Junior High (name)</label>
              <input type="text" readonly class="form-control" id="mJuniorName">
            </div>
            <div class="">
              <label class="form-label">Junior (year)</label>
              <input type="text" readonly class="form-control" id="mJuniorYear">
            </div>
            <div class="">
              <label class="form-label">Senior High (name)</label>
              <input type="text" readonly class="form-control" id="mSeniorName">
            </div>
            <div class="">
              <label class="form-label">Senior (year)</label>
              <input type="text" readonly class="form-control" id="mSeniorYear">
            </div>

            <div class="">
              <label class="form-label">Sex</label>
              <input type="text" readonly class="form-control" id="mSex">
            </div>
            <div class="">
              <label class="form-label">DOB</label>
              <input type="text" readonly class="form-control" id="mDob">
            </div>
            <div class="">
              <label class="form-label">Contact number</label>
              <input type="text" readonly class="form-control" id="mPhone">
            </div>

            <div class="">
              <label class="form-label">Guardian name</label>
              <input type="text" readonly class="form-control" id="mGuardianName">
            </div>
            <div class="">
              <label class="form-label">Guardian phone</label>
              <input type="text" readonly class="form-control" id="mGuardianPhone">
            </div>
            <div class="">
              <label class="form-label">Guardian address</label>
              <textarea readonly class="form-control" id="mGuardianAddress" rows="2"></textarea>
            </div>

            <div class="">
              <label class="form-label">Course</label>
              <input type="text" readonly class="form-control" id="mCourse">
            </div>
            <div class="">
              <label class="form-label">Year</label>
              <input type="text" readonly class="form-control" id="mYear">
            </div>
            <div class="">
              <label class="form-label">Section</label>
              <input type="text" readonly class="form-control" id="mSection">
            </div>

            <div class="">
              <label class="form-label">Appointment date</label>
              <input type="text" readonly class="form-control" id="mAppointmentDate">
            </div>
            <div class="">
              <label class="form-label">Appointment time</label>
              <input type="text" readonly class="form-control" id="mAppointmentTime">
            </div>
            <div id="approvalWarning" class="alert alert-danger d-none mt-3">Cannot approve: no subjects found for this student's course and year.</div>
          </form>
        </div>
        <div class="modal-footer">
          <form method="post" id="approveForm" style="display:inline;">
            <input type="hidden" name="id" id="approveId" value="">
            <button type="submit" name="approve_student" class="btn btn-success">Approve</button>
          </form>
          <form method="post" id="rejectForm" style="display:inline; margin-left:6px;">
            <input type="hidden" name="id" id="rejectId" value="">
            <button type="submit" name="reject_student" class="btn btn-danger">Reject</button>
          </form>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
document.addEventListener('click', function(e){
  var btn = e.target.closest && e.target.closest('.student-open');
  if (!btn) return;
  e.preventDefault();

  function setVal(idSel, attr){
    var el = document.getElementById(idSel);
    if (el) el.value = btn.getAttribute(attr) || '';
  }

  setVal('mUsername','data-username');
  setVal('mEmail','data-email');
  setVal('mLastname','data-lastname');
  setVal('mFirstname','data-firstname');
  setVal('mMiddlename','data-middlename');
  setVal('mElemName','data-elemname');
  setVal('mElemYear','data-elemyear');
  setVal('mJuniorName','data-juniorname');
  setVal('mJuniorYear','data-junioryear');
  setVal('mSeniorName','data-seniorname');
  setVal('mSeniorYear','data-senioryear');
  setVal('mSex','data-sex');
  setVal('mDob','data-dob');
  setVal('mPhone','data-phonenumber');
  setVal('mGuardianName','data-guardianname');
  setVal('mGuardianPhone','data-guardianphone');
  setVal('mGuardianAddress','data-guardianaddress');
  setVal('mCourse','data-course');
  setVal('mYear','data-year');
  setVal('mSection','data-section');
  setVal('mAppointmentDate','data-appointment_date');
  setVal('mAppointmentTime','data-time');

  var id = btn.getAttribute('data-id');
  document.getElementById('approveId').value = id;
  document.getElementById('rejectId').value = id;

  var source = btn.getAttribute('data-source'); // approval | masterlist
  var subjectsCount = parseInt(btn.getAttribute('data-subjects-count') || '0', 10);

  var approveForm = document.getElementById('approveForm');
  var rejectForm = document.getElementById('rejectForm');
  var approvalWarning = document.getElementById('approvalWarning');
  var approveBtn = document.querySelector('#approveForm button[name="approve_student"]');
  var label = document.getElementById('studentModalLabel');

  if (source === 'approval') {
    // APPROVAL REQUEST
    if (subjectsCount <= 0) {
      approvalWarning.classList.remove('d-none');
      approveBtn.disabled = true;
    } else {
      approvalWarning.classList.add('d-none');
      approveBtn.disabled = false;
    }

    approveForm.style.display = 'inline';
    rejectForm.style.display = 'inline';
    label.textContent = 'Student Details — Pending';

  } else {
    // MASTERLIST → READ ONLY
    approveForm.style.display = 'none';
    rejectForm.style.display = 'none';
    approvalWarning.classList.add('d-none');
    approveBtn.disabled = false;
    label.textContent = 'Student Details';
  }

  var modal = new bootstrap.Modal(document.getElementById('studentModal'));
  modal.show();
});
</script>


  <!-- Subject Added Modal (auto-show on add) -->
  <div class="modal fade" id="subjectAddedModal" tabindex="-1" aria-labelledby="subjectAddedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header">
          <h5 class="modal-title" id="subjectAddedModalLabel">Subject Added</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="subjectAddedText">Subject successfully added.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
  <?php if (isset($_SESSION['show_subject_modal'])): $subjectName = htmlspecialchars($_SESSION['subject_added_name'] ?? ''); unset($_SESSION['show_subject_modal'], $_SESSION['subject_added_name']); ?>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      var text = document.getElementById('subjectAddedText');
      if (text) text.textContent = 'Subject "<?php echo $subjectName; ?>" added successfully.';
      var modalEl = document.getElementById('subjectAddedModal');
      if (modalEl) { var modal = new bootstrap.Modal(modalEl); modal.show(); }
    });
  </script>
  <?php endif; ?>

  <!-- Masterlist -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="text-center mb-3">Masterlist</h5>

      <table class="table table-bordered text-center">
        <thead class="table-light">
          <tr>
            <th>Username</th>
            <th>Name</th>
            <th>Course</th>
            <th>Year</th>
            <th>Section</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $stmt = $conn->prepare("SELECT * FROM enroll ORDER BY lastname, firstname");
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
              while ($s = $res->fetch_assoc()) {
                $sid = (int)$s['id'];
                $username = htmlspecialchars($s['username']);
                $lastname = htmlspecialchars($s['lastname']);
                $firstname = htmlspecialchars($s['firstname']);
                $name = htmlspecialchars(trim($s['lastname'] . ', ' . $s['firstname']));
                $course = htmlspecialchars($s['course']);
                $yearRaw = $s['year'] ?? '';
                $year = htmlspecialchars(ucwords(strtolower($yearRaw)));
                $section = htmlspecialchars($s['section'] ?? '');
                $status = strtoupper($s['status'] ?? 'PENDING');
                if ($status === 'APPROVED') {
                  $badge = '<span>Approved</span>';
                } elseif ($status === 'REJECTED') {
                  $badge = '<span>Rejected</span>';
                } else {
                  $badge = '<span class="text-dark">Pending</span>';
                }

                // compute matching subjects count for this student
                $subCount = 0;
                $sc = $conn->prepare("SELECT COUNT(*) AS cnt FROM subjects WHERE course = ? AND year_level = ?");
                $sc->bind_param('ss', $s['course'], $s['year']);
                $sc->execute();
                $sr = $sc->get_result();
                if ($sr) { $srow = $sr->fetch_assoc(); $subCount = intval($srow['cnt']); }
                $sc->close();

                $dataAttrs = ' data-id="' . $sid . '"'
                  . ' data-username="' . $username . '"'
                  . ' data-email="' . htmlspecialchars($s['email']) . '"'
                  . ' data-lastname="' . $lastname . '"'
                  . ' data-firstname="' . $firstname . '"'
                  . ' data-middlename="' . htmlspecialchars($s['middlename']) . '"'
                  . ' data-elemname="' . htmlspecialchars($s['elemName']) . '"'
                  . ' data-elemyear="' . htmlspecialchars($s['elemYear']) . '"'
                  . ' data-juniorname="' . htmlspecialchars($s['juniorName']) . '"'
                  . ' data-junioryear="' . htmlspecialchars($s['juniorYear']) . '"'
                  . ' data-seniorname="' . htmlspecialchars($s['seniorName']) . '"'
                  . ' data-senioryear="' . htmlspecialchars($s['seniorYear']) . '"'
                  . ' data-sex="' . htmlspecialchars($s['sex']) . '"'
                  . ' data-dob="' . htmlspecialchars($s['dob']) . '"'
                  . ' data-phonenumber="' . htmlspecialchars($s['phonenumber']) . '"'
                  . ' data-guardianname="' . htmlspecialchars($s['guardianName']) . '"'
                  . ' data-guardianphone="' . htmlspecialchars($s['guardianPhoneNumber']) . '"'
                  . ' data-guardianaddress="' . htmlspecialchars($s['guardianAddress']) . '"'
                  . ' data-course="' . $course . '"'
                  . ' data-year="' . $year . '"'
                  . ' data-section="' . $section . '"'
                  . ' data-appointment_date="' . htmlspecialchars($s['appointment_date']) . '"'
                  . ' data-time="' . htmlspecialchars($s['time']) . '"'
                  . ' data-status="' . htmlspecialchars($status) . '"'
                  . ' data-subjects-count="' . $subCount . '"';

                echo '<tr>';
                echo '<td>' . $username . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $course . '</td>';
                echo '<td style="white-space:normal; word-break:break-word;" title="' . htmlspecialchars($s['year'] ?? '') . '">' . htmlspecialchars(ucwords(strtolower($s['year'] ?? ''))) . '</td>';
                echo '<td>' . $section . '</td>';
                echo '<td>' . $badge . '</td>';
                echo '<td><button type="button" class="btn btn-sm btn-primary student-open" data-source="masterlist"' . $dataAttrs . '>Open</button></td>';

                echo '</tr>';
              }
            } else {
              echo '<tr><td colspan="7">No students found.</td></tr>';
            }
            $stmt->close();
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Subjects -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="text-center mb-3">Subjects</h5>

      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Subject</th>
            <th>Course</th>
            <th>Instructor</th>
            <th>Year</th>
            <th>Hours</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <form method="post">
              <td><input type="text" name="subject" class="form-control" placeholder="Type subject" required></td>
              <td>
                <select name="course" class="form-select" required>
                  <option selected disabled value="">Select course</option>
                  <option>BS Computer Science</option>
                  <option>BS Information Technology</option>
                  <option>BS Computer Engineering</option>
                </select>
              </td>
              <td><input type="text" name="instructor" class="form-control" placeholder="Instructor"></td>
              <td>
                <select name="year_level" class="form-select" required>
                  <option value="">Set</option>
                  <option>First Year</option>
                  <option>Second Year</option>
                  <option>Third Year</option>
                  <option>Fourth Year</option>
                </select>
              </td>
              <td><input type="number" name="hours" class="form-control" min="0" value="3" required></td>
              <td>
                <button type="submit" name="action" value="add_subject" class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button>
              </td>
            </form>
          </tr>
<?php
$stmt = $conn->prepare("SELECT id, name, course, instructor, year_level, hours FROM subjects ORDER BY course, name");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  echo "<tr>";
  echo "<td>" . htmlspecialchars($row['name']) . "</td>";
  echo "<td>" . htmlspecialchars($row['course']) . "</td>";
  echo "<td>" . htmlspecialchars($row['instructor'] ? $row['instructor'] : '-') . "</td>";
  echo "<td>" . htmlspecialchars($row['year_level']) . "</td>";
  echo "<td>" . htmlspecialchars($row['hours']) . "</td>";
  echo "<td>
          <form method=\"post\" onsubmit=\"return confirm('Delete this subject?');\" style=\"display:inline;\">\n            <input type=\"hidden\" name=\"id\" value=\"" . $row['id'] . "\">\n            <button type=\"submit\" name=\"delete_subject\" value=\"1\" class=\"btn btn-danger btn-sm\">Delete</button>\n          </form>
        </td>";
  echo "</tr>";
}
?>

        </tbody>
      </table>
    </div>
  </div>
  <script>
    (function(){
      var startInput = document.querySelector('input[name="start_time"]');
      var endInput = document.querySelector('input[name="end_time"]');
      function pad(n){ return (n < 10 ? '0' : '') + n; }
      function setEndFromStart(){
        if (!startInput || !endInput) return;
        var val = startInput.value; // expected 'HH:MM' or 'HH:MM:SS'
        if (!val) { endInput.value = ''; return; }
        var parts = val.split(':');
        var h = parseInt(parts[0],10);
        var m = parseInt(parts[1] || 0,10);
        if (isNaN(h) || isNaN(m)) return;
        var dt = new Date(); dt.setHours(h); dt.setMinutes(m); dt.setSeconds(0);
        dt.setMinutes(dt.getMinutes() + 60); // add 60 minutes
        var nh = dt.getHours(); var nm = dt.getMinutes();
        endInput.value = pad(nh) + ':' + pad(nm);
      }
      if (startInput) {
        startInput.addEventListener('change', setEndFromStart);
        startInput.addEventListener('input', setEndFromStart);
        // initialize if a value is present (useful if preserving values)
        setEndFromStart();
      }
    })();
  </script>

</div>
