<?php
SESSION_START();
include 'config/plugins.php';
?>

<?php include __DIR__ . '/sidebar.php'; ?>

<style>
/* Modern inbox styles (visual only) */
.message-card { background: #fff; border-radius: 12px; box-shadow: 0 10px 28px rgba(15,20,30,0.06); transition: transform .15s ease, box-shadow .15s ease; }
.message-card:hover { transform: translateY(-6px); box-shadow: 0 16px 42px rgba(15,20,30,0.10); }
.message-avatar { width:64px; height:64px; border-radius:50%; font-weight:700; display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px; flex-shrink:0; box-shadow: 0 6px 16px rgba(12,16,22,0.12); }
.message-card p { color: #222; margin-bottom: 0.5rem; }
.message-card .text-truncate { overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; }
.message-actions { margin-top: 8px; }
.message-actions .btn { padding: 6px 10px; border-radius: 8px; }
.message-actions .btn-icon { width:36px; height:36px; padding:0; display:inline-flex; align-items:center; justify-content:center; }
.message-actions .btn-icon i { font-size: 14px; }
.message-meta { color: #6b7280; font-size: 13px; }
/* Toolbar */
.inbox-toolbar { display:flex; gap:12px; align-items:center; margin-top:14px; margin-bottom:8px; }
.inbox-toolbar .search { flex:1; max-width:420px; }
.inbox-toolbar .search input { width:100%; padding:10px 12px; border-radius: 10px; border:1px solid #e5e7eb; }
.inbox-toolbar .controls { display:flex; gap:8px; align-items:center; }
/* Responsive tweaks */
@media (max-width:600px) {
  .message-avatar { width:52px; height:52px; font-size:15px; }
  .inbox-toolbar { flex-direction:column; align-items:stretch; }
}

.message-date { color: #6b7280; font-size: 13px; }

/* Fade-out animation for client-side delete (visual only) */
.fade-out { opacity: 0; transform: translateY(-8px); transition: opacity .35s ease, transform .35s ease; }
</style>

<div class="container my-4">
  <h1>Inbox</h1>
  <p class="text-muted">Messages submitted via the Contact page are shown below.</p>

  <div class="inbox-toolbar">
    <div class="search">
      <input type="search" placeholder="Search messages (visual only)" aria-label="Search messages">
    </div>
    <div class="controls">
      <select class="form-select form-select-sm" aria-label="Sort messages" style="width:190px;">
        <option selected>Sort: Latest</option>
        <option>Sort: Oldest</option>
      </select>
      <button class="btn btn-sm btn-outline-secondary" title="Refresh (visual)"><i class="fa-solid fa-arrows-rotate"></i></button>
    </div>
  </div>

  <?php
  require_once __DIR__ . '/config/dbcon.php';
  $res = $conn->query("SELECT id, name, email, message, date_submitted FROM contact ORDER BY date_submitted DESC");
  if ($res && $res->num_rows > 0): ?>

    <div class="mt-3">
      <?php while ($row = $res->fetch_assoc()):
        // generate initials for avatar
        $parts = preg_split('/\s+/', trim($row['name']));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $p) { $initials .= strtoupper(substr($p,0,1)); }
        $bgHue = ord(substr($initials,0,1)) % 360; // simple hue
        $bgColor = "hsl({$bgHue}deg 70% 40%)";
      ?>

        <div class="message-card p-3 mb-3" data-fulldate="<?= htmlspecialchars(date('M j, Y \\a\\t g:ia', strtotime($row['date_submitted']))) ?>">
          <div class="d-flex gap-3">
            <div class="message-avatar" style="background: <?= $bgColor ?>;"><?= htmlspecialchars($initials) ?></div>
            <div class="flex-fill">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="mb-0"><?= htmlspecialchars($row['name']); ?></h6>
                  <div class="small text-muted"><?= htmlspecialchars($row['email']); ?></div>
                  <div class="message-date small text-muted mt-1" title="<?= htmlspecialchars(date('M j, Y \\a\\t g:ia', strtotime($row['date_submitted']))) ?>"><?= date('M j, Y', strtotime($row['date_submitted'])); ?></div>
                </div>
                <div class="text-end">
                  <small class="text-muted"><?= date('M j, Y', strtotime($row['date_submitted'])); ?></small>
                  <div class="mt-1"><span class="badge bg-info text-dark">Contact page</span></div>
                </div>
              </div>


              <div class="message-actions">
                <button class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#messageModal" onclick="openMessage(this)" title="View"><i class="fa-solid fa-eye"></i></button>
                <button class="btn btn-sm btn-outline-danger btn-icon ms-2 btn-delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </div>

              <div class="d-none message-full"><?= nl2br(htmlspecialchars($row['message'])); ?></div>
            </div>
          </div>
        </div>

      <?php endwhile; ?>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageModalLabel">Message</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="small text-muted" id="messageMeta"></p>
            <div id="messageContent"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <script>
    function openMessage(btn){
      var card = btn.closest('.message-card');
      var name = card.querySelector('h6').innerText || '';
      var email = card.querySelector('.small.text-muted').innerText || '';
      var date = card.dataset.fulldate || (card.querySelector('.text-end small') ? card.querySelector('.text-end small').innerText : '');
      var content = card.querySelector('.message-full').innerHTML || '';
      document.getElementById('messageModalLabel').innerText = name;
      document.getElementById('messageMeta').innerText = email + ' • ' + date;
      document.getElementById('messageContent').innerHTML = content;
    }

    // Client-side only: hide card on Delete (visual only)
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.btn-delete').forEach(function(btn){
        btn.addEventListener('click', function(e){
          var card = btn.closest('.message-card');
          if (!card) return;
          card.classList.add('fade-out');
          setTimeout(function(){ card.remove(); }, 380);
        });
      });
    });
    </script>

  <?php else: ?>
    <div class="alert alert-info mt-3">No messages yet.</div>
  <?php endif; ?>

</div>
