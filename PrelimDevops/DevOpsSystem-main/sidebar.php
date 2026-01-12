<?php
// Reusable sidebar include
// Prevent double-inclusion
if (defined('SIDEBAR_INCLUDED')) {
    return;
}
define('SIDEBAR_INCLUDED', true);

// Site settings
if (file_exists(__DIR__ . '/config/site.php')) {
    require_once __DIR__ . '/config/site.php';
} else {
    $SITE_LOGO = 'image/Logo.png';
    $SITE_NAME = 'Site';
}
$current = basename($_SERVER['PHP_SELF']);
?>
<style>
/* Sidebar (fixed) */
body { margin-left: 84px; }
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  width: 72px;
  background: #111;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px 8px;
  z-index: 1000;
}
.sidebar .brand img { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; }
.sidebar .nav { margin-top: 8px; display:flex; flex-direction:column; gap:8px; }
.sidebar a.nav-item {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 48px;
  border-radius: 8px;
  color: #fff;
  text-decoration: none;
  font-size: 20px;
}
.sidebar a.nav-item.active { background: rgba(255,255,255,0.06); transform: scale(1.03); }
.sidebar .logout { margin-top: auto; color: #ff6b6b; text-decoration: none; font-size: 20px; padding: 8px; }

/* Responsive: convert sidebar -> top navbar at smaller widths */
@media (max-width: 766px) {
  /* push page content below the navbar */
  body { margin-left: 0; margin-top: 64px; }

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: auto;
    width: 100%;
    height: 56px;
    background: #111;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    padding: 6px 12px;
    z-index: 1000;
    border-bottom: 1px solid rgba(255,255,255,0.04);
  }

  .sidebar .brand img { width: 40px; height: 40px; }
  .sidebar .nav { margin-top: 0; display:flex; flex-direction:row; gap:10px; align-items:center; margin-left:12px; }
  .sidebar a.nav-item { width: auto; height: auto; padding:8px; border-radius:8px; font-size:18px; }
  .sidebar a.nav-item.active { transform: none; }
  /* Push logout to the far right */
  .sidebar .logout { margin-top: 0; margin-left: auto; color: #ff6b6b; padding: 8px; }
}

/* Keep original mobile hide behavior for very small devices if desired */
@media (max-width: 420px) {
  body { margin-top: 56px; }
  .sidebar { height: 56px; }
  .sidebar .nav { gap:8px; }
  .sidebar a.nav-item { padding:6px; font-size:16px; }
}
</style>

<aside class="sidebar" role="navigation" aria-label="Main navigation">
  <a href="index.php" class="brand" title="<?= htmlspecialchars($SITE_NAME) ?>">
    <?php
      $logoPath = __DIR__ . '/' . $SITE_LOGO;
      $logoUrl = htmlspecialchars($SITE_LOGO);
      if (file_exists($logoPath)) {
          $logoUrl .= '?v=' . filemtime($logoPath);
      }
    ?>
    <img src="<?= $logoUrl ?>" alt="Logo">
  </a>

  <nav class="nav" aria-hidden="false">
    <a href="user_management.php" class="nav-item <?= ($current == 'user_management.php') ? 'active' : '' ?>" title="Users"><i class="fa-solid fa-users text-secondary"></i></a>
    <a href="content_management.php" class="nav-item <?= ($current == 'content_management.php') ? 'active' : '' ?>" title="Content Management"><i class="fa-solid fa-file-alt text-primary"></i></a>
    <a href="digital_enrollment.php" class="nav-item <?= ($current == 'digital_enrollment.php') ? 'active' : '' ?>" title="Digital Enrollment"><i class="fa-solid fa-user-plus text-success"></i></a>
    <a href="classroom.php" class="nav-item <?= ($current == 'classroom.php') ? 'active' : '' ?>" title="Classroom"><i class="fa-solid fa-chalkboard-teacher text-info"></i></a>
    <a href="inbox.php" class="nav-item <?= ($current == 'inbox.php') ? 'active' : '' ?>" title="Inbox"><i class="fa-solid fa-inbox text-warning"></i></a>
  </nav>

  <a href="#" class="logout" title="Logout" data-bs-toggle="modal" data-bs-target="#logoutModal" role="button" aria-controls="logoutModal"><i class="fa-solid fa-right-from-bracket text-danger"></i></a>
</aside>

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

