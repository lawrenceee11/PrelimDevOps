    <?php
    session_start();
    require_once __DIR__ . '/config/dbcon.php';

    if (!isset($_SESSION['username'])) {
        header("Location: index.php");
        exit;
    }

    if (strpos($_SESSION['username'], '@student') === false) {
        $_SESSION['error'] = "Access denied.";
        header("Location: index.php");
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Bootstrap CSS for modal support -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    /* ====== LAYOUT ====== */
    body {
        margin: 0;
        display: flex;
        min-height: 100vh;
        background: #f4f6f9;
        font-family: 'Inter', system-ui, sans-serif;
        font-size: 16px; /* slightly larger base text */
    }

    /* ====== SIDEBAR ====== */
    .sidebar {
        width: 260px;
        background: #111827;
        color: #fff;
        display: flex;
        flex-direction: column;
        padding: 20px 0;
    }

    .sidebar h2 {
        text-align: center;
        margin: 10px 0 18px 0;
        font-size: 1.15rem;
        letter-spacing: 0.2px;
    }

    .sidebar-top {
        text-align: center;
        padding: 20px 12px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        margin-bottom: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .sidebar a {
        color: #cbd5e1;
        text-decoration: none;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: 0.2s;
        cursor: pointer;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background: #1f2933;
        color: #fff;
        border-radius: 8px;
    }

    .sidebar-top .avatar { width: 96px; height: 96px; }

    .sidebar .spacer {
        flex: 1;
    }

    .logout {
        background: #dc2626;
        color: #fff !important;
        margin: 0 16px;
        border-radius: 10px;
    }
    .logout:hover {
        background: #b91c1c;
    }

    /* ====== MAIN ====== */
    .main {
        flex: 1;
        padding: 30px;
    }

    /* ====== DASHBOARD UI ====== */
    .dashboard-wrapper {
        max-width: 1200px;
        margin: auto;
    }

    /* Header */
    .dashboard-header {
        background: #fff;
        border-radius: 18px;
        padding: 28px 30px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        margin-bottom: 28px;
    }

    .dashboard-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 28px; /* larger heading */
    }

    .dashboard-header p {
        color: #6c757d;
        margin-top: 6px;
        font-size: 15px; /* larger subtext */
    }

    /* Cards */
    .modern-card {
        background: #fff;
        border-radius: 18px;
        padding: 26px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        margin-bottom: 28px;
    }

    /* Profile */
    .profile-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        align-items: start;
    }

    /* Responsive adjustments */
    @media (max-width: 900px) {
        .profile-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 560px) {
        .profile-grid { grid-template-columns: 1fr; }
    }

    .profile-item {
        background: #f8f9fc;
        padding: 18px;
        border-radius: 14px;
    }

    .profile-item span {
        font-size: 13px;
        color: #6c757d;
    }

    .profile-item span i {
        color: #6c757d;
        margin-right: 8px;
        width: 20px;
        text-align: center;
        font-size: 14px;
    }

    .profile-item strong {
        display: block;
        font-size: 18px;
        margin-top: 6px;
    }

    /* Avatar */
    .avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.9);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }


    .sidebar-name {
        color: #cbd5e1;
        font-weight: 600;
        margin-top: 6px;
        font-size: 15px;
        text-align: center;
    }

    /* Table */
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .table thead th {
        border: none;
        color: #6c757d;
        font-size: 14px;
        text-transform: uppercase;
        text-align: center;
    }

    .table tbody tr {
        background: #fff;
        box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    }

    .table tbody td {
        padding: 16px;
        text-align: center;
        border: none;
        font-size: 15px;
    }

    .table tbody tr td:first-child { border-radius: 12px 0 0 12px; }
    .table tbody tr td:last-child { border-radius: 0 12px 12px 0; }

    .pass { color: #155724; background-color: transparent; font-weight: 700; padding: 0; border-radius: 0; display: inline; }
    .fail { color: #dc3545; background-color: transparent; font-weight: 700; padding: 0; border-radius: 0; display: inline; }
    .avg { font-weight: 700; }

    .empty-state {
        text-align: center;
        color: #6c757d;
        padding: 30px;
        font-size: 15px;
    }

    /* ====== HIDE SECTIONS BY DEFAULT ====== */
    #dashboard-info, #profile, #grades {
        display: none; /* hide initially */
    }

    #dashboard-info p {
        font-size: 14px;
        color: #495057;
        line-height: 1.6;
    }

    </style>
    </head>

    <body>

    <!-- ===== SIDEBAR ===== -->
    <div class="sidebar">
        <?php
            // load user's avatar if column exists and path stored
            $avatarUrl = 'image/profiles/default_avatar.svg';
            $hasProfileCol = false;
            $colChk = $conn->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'enroll' AND COLUMN_NAME = 'profile_pic' LIMIT 1");
            if ($colChk) {
                $colChk->execute();
                $colChk->store_result();
                $hasProfileCol = $colChk->num_rows > 0;
                $colChk->close();
            }
            if ($hasProfileCol) {
                $aStmt = $conn->prepare("SELECT profile_pic, firstname, lastname FROM enroll WHERE username=? LIMIT 1");
                if ($aStmt) {
                    $aStmt->bind_param('s', $_SESSION['username']);
                    $aStmt->execute();
                    $aRow = $aStmt->get_result()->fetch_assoc();
                    $aPath = $aRow['profile_pic'] ?? null;
                    $student_name = trim((($aRow['firstname'] ?? '') . ' ' . ($aRow['lastname'] ?? '')));
                    if (!empty($aPath) && file_exists(__DIR__ . '/' . $aPath)) {
                        $avatarUrl = htmlspecialchars($aPath) . '?v=' . filemtime(__DIR__ . '/' . $aPath);
                    }
                    $aStmt->close();
                }
            } else {
                // fetch student's name for display when profile_pic column not present
                $nameStmt = $conn->prepare("SELECT firstname, lastname FROM enroll WHERE username=? LIMIT 1");
                if ($nameStmt) {
                    $nameStmt->bind_param('s', $_SESSION['username']);
                    $nameStmt->execute();
                    $r = $nameStmt->get_result()->fetch_assoc();
                    $student_name = trim((($r['firstname'] ?? '') . ' ' . ($r['lastname'] ?? '')));
                    $nameStmt->close();
                }
            }
        ?>
        <div class="sidebar-top">
            <img src="<?= $avatarUrl ?>" alt="Profile" class="avatar" />
            <div class="sidebar-name"><?= htmlspecialchars($student_name ?? $_SESSION['username']) ?></div>
        </div>
        <h2>Student Portal</h2>
        <a class="active" onclick="showSection('dashboard-info', this)">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>

        <a onclick="showSection('grades', this)">
            <i class="fa-solid fa-table"></i> View Grades
        </a>

        <a onclick="showSection('profile', this)">
            <i class="fa-solid fa-id-badge"></i> Profile
        </a>

        <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="fa-solid fa-user-pen"></i> Edit Profile
        </a>

        <a href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
            <i class="fa-solid fa-key"></i> Change Password
        </a>

        <div class="spacer"></div>

        <a href="#" class="logout" data-bs-toggle="modal" data-bs-target="#logoutModal" role="button">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main">
        <div class="dashboard-wrapper">

            <!-- DASHBOARD -->
            <div class="modern-card" id="dashboard-info">
                <div class="dashboard-header">
                    <h1>🎓 Student Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($_SESSION['username']); ?></p>
                </div>

                <h4 class="mb-4">🏠 Dashboard Overview</h4>
                <p>Here you can view your personal profile, check your academic grades, and navigate through your student panel. Click the links on the left to quickly access different sections.</p>
            </div>

            <!-- PROFILE -->
            <div class="modern-card" id="profile">
                <h4 class="mb-4">👤 Student Profile</h4>

                <?php
                $stmt = $conn->prepare("SELECT * FROM enroll WHERE username=? LIMIT 1");
                $stmt->bind_param("s", $_SESSION['username']);
                $stmt->execute();
                $student = $stmt->get_result()->fetch_assoc();
                ?>

                <div class="profile-grid">
                    <div class="profile-item"><span><i class="fa-solid fa-user"></i> Name</span><strong><?= htmlspecialchars($student['firstname'].' '.$student['lastname']); ?></strong></div>
                    <div class="profile-item"><span><i class="fa-solid fa-graduation-cap"></i> Course</span><strong><?= htmlspecialchars($student['course'] ?? '—') ?></strong></div>
                    <div class="profile-item"><span><i class="fa-solid fa-building-columns"></i> Year & Section</span><strong><?= htmlspecialchars(ucwords(strtolower($student['year'] ?? '')) . ' - ' . ($student['section'] ?? '')) ?></strong></div>
                    <div class="profile-item"><span><i class="fa-solid fa-phone"></i> Phone</span><strong><?= htmlspecialchars($student['phonenumber'] ?? '—') ?></strong></div>
                    <div class="profile-item"><span><i class="fa-solid fa-envelope"></i> Email</span><strong><?= htmlspecialchars($student['email'] ?? '—') ?></strong></div>
                    <div class="profile-item"><span><i class="fa-solid fa-location-dot"></i> Address</span><strong><?= htmlspecialchars($student['guardianAddress'] ?? '—') ?></strong></div>
                </div>
            </div>

            <!-- Edit Profile Modal -->
            <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <form action="config/updateStudentProfile.php" method="POST" id="editProfileForm" enctype="multipart/form-data">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editProfileLabel">Edit Profile</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="username" value="<?= htmlspecialchars($_SESSION['username']) ?>">
                      <div class="mb-2"><label class="form-label">First name</label><input name="firstname" class="form-control" required value="<?= htmlspecialchars($student['firstname'] ?? '') ?>"></div>
                      <div class="mb-2"><label class="form-label">Last name</label><input name="lastname" class="form-control" required value="<?= htmlspecialchars($student['lastname'] ?? '') ?>"></div>
                      <div class="mb-2"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required value="<?= htmlspecialchars($student['email'] ?? '') ?>"></div>
                      <div class="mb-2"><label class="form-label">Phone</label><input name="phonenumber" maxlength="10" pattern="[0-9]{10}" title="Digits only (10)" class="form-control" value="<?= htmlspecialchars($student['phonenumber'] ?? '') ?>"></div>
                      <div class="mb-2">
                        <label class="form-label">Profile photo <small class="text-muted">(optional)</small></label>
                        <input type="file" name="profile_pic" id="profilePicInput" accept="image/*" class="form-control">
                        <small class="text-muted">Max 2MB. JPG/PNG/GIF.</small>
                        <div class="mt-2"><img id="profilePicPreview" src="<?= $avatarUrl ?>" alt="Preview" style="width:72px; height:72px; border-radius:50%; object-fit:cover;"></div>
                      </div>
                      <div class="mb-2"><label class="form-label">Section</label><input name="section" class="form-control" value="<?= htmlspecialchars($student['section'] ?? '') ?>"></div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Change Password Modal -->
            <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <form action="config/updateStudentPassword.php" method="POST" id="changePasswordForm">
                    <div class="modal-header">
                      <h5 class="modal-title" id="changePasswordLabel">Change Password</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="username" value="<?= htmlspecialchars($_SESSION['username']) ?>">
                      <div class="mb-2"><label class="form-label">Current password</label><input name="current_password" type="password" class="form-control" required></div>
                      <div class="mb-2"><label class="form-label">New password</label><input name="new_password" id="newPassword" type="password" class="form-control" required minlength="6"></div>
                      <div class="mb-2"><label class="form-label">Confirm new password</label><input name="confirm_password" id="confirmPassword" type="password" class="form-control" required minlength="6"></div>
                      <div id="pwMismatch" class="text-danger small mt-1" style="display:none;">Passwords do not match.</div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-danger">Change password</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- GRADES -->
            <div class="modern-card" id="grades">
                <h4 class="mb-4">📊 Academic Grades</h4>

                <?php
                $gradesRes = $conn->query("
                    SELECT subject, instructor, prelim, midterm, finals, average, remarks
                    FROM grades
                    WHERE username='".$conn->real_escape_string($_SESSION['username'])."'
                ");
                ?>

                <?php if ($gradesRes && $gradesRes->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Instructor</th>
                            <th>Prelim</th>
                            <th>Midterm</th>
                            <th>Finals</th>
                            <th>Average</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($g = $gradesRes->fetch_assoc()):
                        // normalize remarks and determine class (case-insensitive)
                        $remark = trim($g['remarks'] ?? '');
                        $cls = (strcasecmp($remark, 'Passed') === 0) ? 'pass' : 'fail';
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($g['subject']); ?></td>
                            <td><?= htmlspecialchars($g['instructor']); ?></td>
                            <td><?= htmlspecialchars($g['prelim']); ?></td>
                            <td><?= htmlspecialchars($g['midterm']); ?></td>
                            <td><?= htmlspecialchars($g['finals']); ?></td>
                            <td class="avg"><?= htmlspecialchars($g['average']); ?></td>
                            <td><span class="<?= $cls; ?>"><?= htmlspecialchars($remark); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state">📌 No grades available yet.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- ===== JS FOR TOGGLING SECTIONS ===== -->
    <script>
    function showSection(sectionId, el) {
        // Hide all sections
        document.getElementById('dashboard-info').style.display = 'none';
        document.getElementById('profile').style.display = 'none';
        document.getElementById('grades').style.display = 'none';

        // Show selected section
        document.getElementById(sectionId).style.display = 'block';

        // Remove active class from all sidebar links
        document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));

        // Add active class to clicked link (except logout)
        if(!el.classList.contains('logout')) {
            el.classList.add('active');
        }
    }

    // Trigger the first active link after DOM is fully loaded
    document.addEventListener('DOMContentLoaded', () => {
        const activeLink = document.querySelector('.sidebar a.active');
        if(activeLink) activeLink.click();
    });
    
    // Password confirmation check
    document.getElementById('changePasswordForm') && document.getElementById('changePasswordForm').addEventListener('submit', function(e){
      var n = document.getElementById('newPassword');
      var c = document.getElementById('confirmPassword');
      var err = document.getElementById('pwMismatch');
      if (!n || !c) return;
      if (n.value !== c.value) {
        e.preventDefault();
        if (err) err.style.display = 'block';
        return false;
      }
    });

    // Profile picture preview
    (function(){
      var input = document.getElementById('profilePicInput');
      var preview = document.getElementById('profilePicPreview');
      if (!input) return;
      input.addEventListener('change', function(e){
        var f = e.target.files[0];
        if (!f) return;
        if (!f.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function(ev){ if (preview) preview.src = ev.target.result; };
        reader.readAsDataURL(f);
      });
    })();
    </script>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            Are you sure you want to logout?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <a href="config/loginAuth.php?logout=1" class="btn btn-danger">Logout</a>
        </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS (bundle with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    </body>
    </html>
