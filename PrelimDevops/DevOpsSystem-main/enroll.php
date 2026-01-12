<?php
session_start();
include 'config/plugins.php';
require_once __DIR__ . '/config/dbcon.php';
require_once __DIR__ . '/config/site.php';
?>
<style>
.message-success { color: green; font-weight: 500; }
.message-error { color: red; font-weight: 500; }
</style>
<style>
  
@keyframes popIn {
  0% {transform: scale(0.9); opacity: 0;}
  100% {transform: scale(1); opacity: 1;}
}
.modal-content { animation: popIn 0.3s ease-out; }
/* Remove browser built-in password eye (Edge / Chromium) */
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
  display: none;
}

input[type="password"]::-webkit-credentials-auto-fill-button {
  visibility: hidden;
  position: absolute;
  pointer-events: none;
}
.footer {
  background: #111;
  color: #ccc;
  padding: 40px 20px;
}

.footer-inner {
  max-width: 1100px;
  margin: auto;
}

.footer h5 {
  color: #fff;
  font-weight: 600;
}

.footer i {
  color: #0d6efd;
  margin-right: 6px;
}

.footer hr {
  border-color: rgba(255,255,255,0.1);
  margin: 25px 0;
}

.footer .small {
  color: #aaa;
}

/* Premium Cards */
.info-card { border-radius: 18px; background: linear-gradient(135deg, #ffffff, #f0f4ff); box-shadow: 0 12px 25px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden; animation: fadeInUp 0.6s ease forwards;}
.info-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px rgba(0,0,0,0.15);}
.info-card i { font-size: 2rem; color: #0d6efd; margin-bottom: 12px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);}
.card-title { font-weight: 600; margin-bottom: 10px; position: relative; }
.info-card p { color: #333; font-size: 0.95rem; line-height: 1.5;}
@keyframes fadeInUp {0% {opacity: 0; transform: translateY(20px);} 100% {opacity: 1; transform: translateY(0);}}
</style>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-sm bg-light navbar-light" style="box-shadow:0 2px 4px rgba(0,0,0,0.1);position:sticky;top:0;z-index:1000;">
  <div class="container-fluid container py-4">
    <?php
      $logoPath = __DIR__ . '/' . $SITE_LOGO;
      $logoUrl = htmlspecialchars($SITE_LOGO);
      if(file_exists($logoPath)) { $logoUrl .= '?v='.filemtime($logoPath); }
    ?>
    <a class="navbar-brand" href="index.php"><img src="<?= $logoUrl ?>" alt="Logo" style="height:40px; width:auto; object-fit:contain;"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link fs-6" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link fs-6" href="contact.php">Contact Us</a></li>
        <li class="nav-item"><a class="nav-link fs-6" href="enroll.php">Enroll Now</a></li>
      </ul>
    </div>
  </div>
</nav>
<?php
// Success messages
if (isset($_SESSION['status'])) {
    echo '<div class="container mt-4 mb-4 message-success">'
        .htmlspecialchars($_SESSION['status']).
        '</div>';
    unset($_SESSION['status']);
}

// Error messages
if (isset($_SESSION['error'])) {
    echo '<div class="container mt-4 mb-4 message-error">'
        .htmlspecialchars($_SESSION['error']).
        '</div>';
    unset($_SESSION['error']);
}
?>


<!-- ================= START FORM ================= -->
<form action="config/AddStudent.php" method="POST">

<!-- ================= APPOINTMENT ================= -->
<div class="container shadow rounded-3 p-4 my-4">
  <p class="mb-4" style="font-size:2rem;">Select your appointment schedule</p>
  <table class="table table-bordered">
    <thead>
      <tr><th>Date</th><th>Time</th><th>Slots</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php
        $stmt = $conn->prepare("SELECT id, date, start_time, end_time, slots FROM appointments WHERE date >= CURDATE() ORDER BY date, start_time");
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $hasAvailable = false;
        foreach ($rows as $r){ if(intval($r['slots'])>0){ $hasAvailable=true; break; } }
        if(count($rows)>0){
            foreach($rows as $row){
                $fmtDate = date("F j, Y | l", strtotime($row['date']));
                $fmtStart = date("h:i A", strtotime($row['start_time']));
                $fmtEnd = date("h:i A", strtotime($row['end_time']));
                $slots = intval($row['slots']);
                $disabled = $slots<=0 ? 'disabled':'';
                $badge = $slots<=0 ? '<span>Full</span>':'<span>'.$slots.' slot'.($slots>1?'s':'').'</span>';
                $requiredAttr = $hasAvailable?'required':'';
                echo '<tr>';
                echo '<td>'.htmlspecialchars($fmtDate).'</td>';
                echo '<td>'.htmlspecialchars($fmtStart).' - '.htmlspecialchars($fmtEnd).'</td>';
                echo '<td>'.$badge.'</td>';
                echo '<td><input type="radio" name="appointmentID" value="'.intval($row['id']).'" data-date="'.htmlspecialchars($row['date']).'" data-start="'.htmlspecialchars($row['start_time']).'" data-end="'.htmlspecialchars($row['end_time']).'" '.$disabled.' '.$requiredAttr.'></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4">No appointments available.</td></tr>';
        }
      ?>
    </tbody>
  </table>
  <input type="hidden" name="appointment_date" id="appointment_date" value="">
  <input type="hidden" name="time" id="appointment_time" value="">
</div>

<!-- ================= ACCOUNT ================= -->
<div class="container shadow rounded-3 p-4 my-4">
  <p class="mb-4" style="font-size:2rem;">I. Create Your Student Portal Account</p>
  <div class="mb-3">
    <label for="username" class="form-label">Username:</label>
    <div class="input-group">
      <input type="text" class="form-control" id="Username" name="username" placeholder="Username" required>
      <div class="input-group-text">@Student</div>
    </div>
  </div>
  <div class="mb-3">
    <label for="email" class="form-label">Email address:</label>
    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
  </div>
 <div class="mb-3">
  <label for="password" class="form-label">Password:</label>
  <div class="input-group">
    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
    <span class="input-group-text cursor-pointer" id="togglePassword">
      <i class="fa-solid fa-eye"></i>
    </span>
  </div>
</div>

<!-- ================= EDUCATIONAL ATTAINMENT ================= -->
<div class="container shadow rounded-3 p-4 my-4">
  <p class="mb-4" style="font-size:2rem;">II. Educational Information</p>
  <?php
  $levels = ['Elementary'=>'elem', 'Junior High School'=>'junior','Senior High School'=>'senior'];
  foreach($levels as $label=>$prefix){
      echo "<label class='form-label'>$label:</label>
      <div class='row mb-4'>
        <div class='col-8'><input type='text' name='{$prefix}Name' class='form-control' placeholder='School Name' required></div>
        <div class='col'><input type='text' name='{$prefix}Year' class='form-control' placeholder='Year Graduated' required></div>
      </div>";
  }
  ?>
</div>

<!-- ================= PERSONAL INFORMATION ================= -->
<div class="container shadow rounded-3 p-4 my-4">
  <p class="mb-3" style="font-size:2rem;">III. Enrollment Form</p>
  <p class="text-center mb-3" style="font-size:1.5rem;">Student's Personal Information</p>

  <div class="row mb-3">
    <div class="col"><input type="text" name="lastname" class="form-control" placeholder="Last Name" required></div>
    <div class="col"><input type="text" name="firstname" class="form-control" placeholder="First Name" required></div>
    <div class="col"><input type="text" name="middlename" class="form-control" placeholder="Middle Name" required></div>
  </div>

  <div class="row mb-3">
    <div class="col">
      <select name="sex" class="form-select" required>
        <option disabled selected>Sex</option>
        <option>Male</option><option>Female</option>
      </select>
    </div>
    <div class="col">
      <input type="date" name="dob" class="form-control" required>
    </div>
  </div>

  <div class="mb-3">
    <label>Contact Number:</label>
    <div class="input-group">
      <div class="input-group-text">+63</div>
      <input type="text" name="phoneNumber" maxlength="10" pattern="[0-9]{10}" title="Digits only (10)" class="form-control" placeholder="9XXXXXXXXX" required>
    </div>
  </div>

  <div class="mb-3">
    <label>Home Address:</label>
    <textarea name="address" class="form-control" placeholder="Enter Full Address" required></textarea>
  </div>

  <div class="row mb-3">
    <div class="col">
      <input type="text" name="guardianName" class="form-control" placeholder="Guardian Name" required>
    </div>
    <div class="col">
      <div class="input-group">
        <div class="input-group-text">+63</div>
        <input type="text" name="guardianPhoneNumber" maxlength="10" pattern="[0-9]{10}" title="Digits only (10)" class="form-control" placeholder="9XXXXXXXXX" required>
      </div>
    </div>
  </div>

  <div class="mb-3">
    <textarea name="guardianAddress" class="form-control" placeholder="Guardian Address" required></textarea>
  </div>
</div>

<!-- ================= STUDENT ENROLLMENT ================= -->
<div class="container shadow rounded-3 p-4 my-4">
  <p class="text-center mb-3" style="font-size:1.5rem;">Student's Enrollment</p>
  <div class="row mb-3">
    <div class="col"><select name="course" class="form-select" required>
      <option disabled selected>Course</option>
      <option>BS Information Technology</option>
      <option>BS Computer Science</option>
      <option>BS Computer Engineering</option>
    </select></div>
    <div class="col"><select name="year" class="form-select" required>
      <option disabled selected>Year Level</option>
      <option>First Year</option><option>Second Year</option><option>Third Year</option><option>Fourth Year</option>
    </select></div>
    <div class="col"><select name="section" class="form-select" required>
      <option disabled selected>Section</option>
      <option>A</option><option>B</option><option>C</option>
    </select></div>
  </div>
</div>

<!-- ================= PRIVACY CARDS ================= -->
<div class="container my-5">
  <div class="row g-4">
    <?php
    $cards = [
      ['fa-user-shield','Data Privacy Notice','Before you submit personal information, read this notice. We respect your privacy and comply with relevant data protection laws.'],
      ['fa-address-card','Information We Collect','We collect your name, email, phone number, and other details you provide.'],
      ['fa-gear','How We Use Your Info','Your information helps us respond to inquiries, provide services, improve our website, and comply with legal requirements.'],
      ['fa-handshake','Sharing Info','We do not sell or trade your information. Sharing only happens with notice or as required by law.'],
      ['fa-lock','Protection Measures','We use encryption and industry-standard security to protect your information.'],
      ['fa-gavel','Your Rights','You can access, correct, or delete your data, and withdraw consent anytime.'],
      ['fa-file-pen','Notice Updates','We may update this notice periodically. Revisions apply to info collected after the update.'],
      ['fa-envelope','Contact Us','Questions? Reach out via our <a href="contact.php">contact page</a>.']
    ];
    foreach($cards as $c){
        echo '<div class="col-lg-3 col-md-6"><div class="card info-card p-4 h-100"><i class="fa-solid '.$c[0].'"></i><h5 class="card-title">'.$c[1].'</h5><p>'.$c[2].'</p></div></div>';
    }
    ?>
  </div>
</div>

<!-- ================= SUBMIT BUTTON ================= -->
<div class="text-center my-4">
    <button id="submitBtn" type="button" class="btn btn-primary btn-lg px-5">Submit Enrollment</button>
</div>

<footer class="bg-dark text-center text-lg-start mt-5">
  <div class="container p-5">
    <div class="row">
      <div class="col-lg-9 col-md-12 mb-4 mb-md-0">
        <h5 class="text-uppercase text-light"><?= htmlspecialchars(
          $SITE_INST ?: 'Institute'
        ) ?></h5>
        <p class="text-light">Empowering education for a brighter future.</p>
      </div>
      <div class="col-lg-3 col-md-12 mb-4 mb-md-0">
        <h5 class="text-uppercase text-light">Contact</h5>
        <p class="text-light"><br><i class="fa-solid fa-school"></i> <?= htmlspecialchars($SITE_INST) ?><br><br><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($SITE_LOCATION) ?><br><br>
          <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($SITE_EMAIL) ?><br><br><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($SITE_PHONE) ?><br><br><i class="fa-solid fa-tty"></i> <?= htmlspecialchars($SITE_TTY) ?>
        </p>
      </div>
    </div>
  </div>
  <div class="text-center text-light p-3" style="background-color: rgba(0, 0, 0, 0.2);">
    © <?= date('Y') ?> Copyright: <?= htmlspecialchars($SITE_INST) ?>
  </div>
</footer>


<!-- ================= MODALS ================= -->
<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirm Submission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Are you sure you want to submit the enrollment form?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= JS ================= -->
<script defer>
(function(){
  // Appointment hidden fields
  function setAppointmentFields(){
    var sel = document.querySelector('input[name="appointmentID"]:checked');
    var dateEl = document.getElementById('appointment_date');
    var timeEl = document.getElementById('appointment_time');
    if(!dateEl || !timeEl) return;
    function to12h(t){if(!t)return'';var parts=t.split(':');var hh=parseInt(parts[0],10);var mm=(parts[1]||'00').split(':')[0];if(isNaN(hh)) return t; var am=hh>=12?'PM':'AM';hh=hh%12;hh=hh===0?12:hh;return hh+':'+(mm.length===1?'0'+mm:mm)+' '+am;}
    if(sel){ dateEl.value=sel.getAttribute('data-date')||''; var s=sel.getAttribute('data-start')||''; var e=sel.getAttribute('data-end')||''; timeEl.value=s&&e?to12h(s)+' - '+to12h(e):(s?to12h(s):''); } else { dateEl.value='';timeEl.value='';}
  }
  document.querySelectorAll('input[name="appointmentID"]').forEach(r=>r.addEventListener('change',setAppointmentFields));
  setAppointmentFields();

  // Submit button -> confirm modal
  document.getElementById('submitBtn').addEventListener('click',()=>{ new bootstrap.Modal(document.getElementById('confirmModal')).show(); });
  document.getElementById('confirmSubmit').addEventListener('click',()=>{
    setAppointmentFields();
    var form=document.querySelector('form');
    if(form.checkValidity()){ form.submit(); } else { form.reportValidity(); }
  });

  // Password toggle
 // Password toggle (NO DUPLICATE)
const passwordInput = document.getElementById('password');
const toggle = document.getElementById('togglePassword');
const icon = toggle.querySelector('i');

toggle.addEventListener('click', () => {
  const isHidden = passwordInput.type === 'password';
  passwordInput.type = isHidden ? 'text' : 'password';
  icon.className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
});

})();
</script>
