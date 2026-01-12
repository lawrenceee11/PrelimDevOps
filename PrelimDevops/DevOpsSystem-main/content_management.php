<?php
SESSION_START();
include 'config/plugins.php';
require_once __DIR__ . '/config/dbcon.php';
?>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="container my-4">
  <h1>Content Management</h1>
  <p>Manage site-wide content such as the site logo below.</p>

  <?php if (isset($_SESSION['status'])): ?>
    <div class="alert alert-info mt-2"><?php echo htmlspecialchars($_SESSION['status']); unset($_SESSION['status']); ?></div>
  <?php endif; ?>

  <style>
    /* Center and stack logo preview and actions */
    .logo-row { display:flex; flex-direction:column; gap:14px; align-items:center; justify-content:center; text-align:center; max-width:480px; margin:0 auto; }
    .logo-preview { width:160px; height:160px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center; background:#fff; border-radius:8px; }
    .logo-form { flex:1; min-width:220px; }
    .logo-actions { display:flex; gap:10px; align-items:center; margin-top:6px; justify-content:center; }
    .logo-actions .btn { min-width:80px; }
    .logo-filename { color:#666; font-size:0.9rem; margin-top:6px; text-align:center; }
    @media (max-width:600px){ .logo-row { width:100%; } .logo-preview { width:96px; height:96px; } .logo-actions { flex-direction:column; gap:8px; } .logo-actions .btn { width:100%; } }
  </style> 

  <div class="card p-4 text-center mt-3">
    <h2 class="text-center mb-4" style="font-size:2.0rem; margin-bottom:12px;">Update Logo</h2>
    <div class="logo-row">
      <div class="logo-preview">
        <?php
          $logoPath = __DIR__ . '/' . $SITE_LOGO;
          $logoUrl = htmlspecialchars($SITE_LOGO);
          if (file_exists($logoPath)) {
              $logoUrl .= '?v=' . filemtime($logoPath);
          }
        ?>
        <img id="currentLogo" src="<?= $logoUrl ?>" alt="Logo" style="max-width:100%; max-height:100%; object-fit:contain;">
      </div>

      <div class="logo-actions">
        <button type="button" class="btn btn-primary add" data-bs-toggle="modal" data-bs-target="#logoModal">Change Logo</button>
        <button type="button" id="resetLogo" class="btn btn-danger delete">Reset</button>
      </div>
      <div class="logo-filename" id="logoFilename" style="display:none;"></div>
    </div>
  </div>
  

  <!-- Logo Upload Modal -->
  <div class="modal fade" id="logoModal" tabindex="-1" aria-labelledby="logoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="logoForm" action="config/updateLogo.php" method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="logoModalLabel">Update Logo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="logoInput" class="form-label">Choose image</label>
              <input type="file" class="form-control" id="logoInput" name="logo" accept="image/*" required>
            </div>
            <div class="d-flex align-items-center gap-3">
              <img id="modalLogoPreview" src="<?= $logoUrl ?>" alt="Logo" style="width:80px; height:80px; object-fit:contain;">
              <div id="logoFilenameModal" class="logo-filename" style="display:none;"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary add">Upload</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Profile editor -->
  <div class="card p-4 mt-3">
    <h5 class="text-center mb-3"style="font-size:2.0rem; margin-bottom:12px;">School Profile</h5>
    <form action="config/updateProfile.php" method="POST">
      <h6 class="mb-3">Details:</h6>
        <div class="mb-3 row">
          <label class="col-sm-1 col-form-label text-end text-end-to-left" style="font-weight:600">Institute</label>
          <div class="col-sm-11">
            <input name="inst" type="text" class="form-control" value="<?= htmlspecialchars($SITE_INST) ?>" required>
          </div>
        </div>
        <div class="mb-3 row">
          <label class="col-sm-1 col-form-label text-end text-end-to-left" style="font-weight:600">Location</label>
          <div class="col-sm-11">
            <input name="location" type="text" class="form-control" value="<?= htmlspecialchars($SITE_LOCATION) ?>">
          </div>
        </div>
        <div class="mb-3 row">
          <label class="col-sm-1 col-form-label text-end text-end-to-left" style="font-weight:600">Email</label>
          <div class="col-sm-11">
            <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($SITE_EMAIL) ?>" required>
          </div>
        </div>
        <div class="mb-3 row">
          <label class="col-sm-1 col-form-label text-end text-end-to-left" style="font-weight:600">Phone</label>
          <div class="col-sm-11">
            <input name="phone" type="text" class="form-control" value="<?= htmlspecialchars($SITE_PHONE) ?>">
          </div>
        </div>
        <div class="mb-3 row">
          <label class="col-sm-1 col-form-label text-end text-end-to-left" style="font-weight:600">TTY</label>
          <div class="col-sm-11">
            <input name="tty" type="text" class="form-control" value="<?= htmlspecialchars($SITE_TTY) ?>">
          </div>
        </div>

      <div style="margin-top:12px; display:flex; gap:8px;">
        <button class="btn btn-primary add" type="submit">Save Profile</button>
        <?php
          $profile_saved = false;
          if (isset($_SESSION['profile_saved']) && $_SESSION['profile_saved']) {
            $profile_saved = true;
            unset($_SESSION['profile_saved']);
          }
          if (!$profile_saved) {
            echo '<button type="button" id="previewProfile" class="btn" style="background:#6c757d; color:#fff;">Preview</button>';
          } else {
            echo '<span class="text-success" style="align-self:center;">Profile saved — preview disabled.</span>';
          }
        ?>
      </div>
    </form>

    <div style="margin-top:14px;">
      <p id="profilePreview" class="mb-0" style="font-size: 16px; font-weight: bold;"></p>
    </div>
  </div>

    

    

  <!-- Covers manager -->
  <div class="card p-4 mt-3">
    <h2 class="text-center" style="font-size:2.0rem; margin-bottom:12px;">Homepage Covers (slider)</h2>
    <div style="display:flex; justify-content:center; align-items:center; gap:3; margin-bottom:12px;">
      <button type="button" class="btn btn-success add" data-bs-toggle="modal" data-bs-target="#coverModal">Add Cover</button>
    </div>

    <!-- Cover Upload Modal -->
    <div class="modal fade" id="coverModal" tabindex="-1" aria-labelledby="coverModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form id="coverForm" action="config/uploadCover.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="coverModalLabel">Upload Cover</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="coverInput" class="form-label">Choose image</label>
                <input type="file" class="form-control" id="coverInput" name="cover" accept="image/*" required>
              </div>
              <div class="mb-3"><input type="text" class="form-control" id="coverTitle" name="title" placeholder="Title (optional)"></div>
              <div class="mb-3"><textarea class="form-control" id="coverCaption" name="caption" placeholder="Caption / description (optional)" style="height:80px;"></textarea></div>
              <div class="mt-3 d-flex gap-3 align-items-center">
                <img id="coverPreview" src="" alt="" style="width:120px; height:80px; object-fit:cover; display:none; border:1px solid #eee;">
                <div id="coverFilenameModal" class="logo-filename" style="display:none;"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary add">Upload Cover</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div style="margin-top:12px; display:flex; gap:12px; flex-wrap:wrap;"> 
      <?php
        $coversFile = __DIR__ . '/config/covers.json';
        $covers = [];
        if (file_exists($coversFile)) {
          $covers = json_decode(file_get_contents($coversFile), true) ?? [];
        }
        foreach ($covers as $c) {
          // handle both legacy string and object formats
          if (is_string($c)) {
            $pathRel = $c; $title = ''; $caption = '';
          } else {
            $pathRel = $c['path'] ?? ''; $title = $c['title'] ?? ''; $caption = $c['caption'] ?? '';
          }
          $path = __DIR__ . '/' . $pathRel;
          $url = htmlspecialchars($pathRel);
          if (file_exists($path)) { $url .= '?v=' . filemtime($path); }
          $escTitle = htmlspecialchars($title);
          $escCaption = htmlspecialchars($caption);
          echo "<div style=\"width:220px; text-align:left; border:1px solid #eee; padding:8px; background:#fff;\">";
          echo "<img src=\"$url\" style=\"width:100%; height:110px; object-fit:cover; display:block; margin-bottom:6px;\">";
          echo "<div style=\"font-weight:600; font-size:0.95rem; margin-bottom:4px;\">$escTitle</div>";
          echo "<div style=\"font-size:0.85rem; color:#666; margin-bottom:8px;\">$escCaption</div>";
          // Edit form (inline)
          echo "<form method=\"POST\" action=\"config/updateCover.php\" style=\"margin-bottom:6px;\">";
          echo "<input type=\"hidden\" name=\"path\" value=\"$pathRel\">";
          echo "<input name=\"title\" placeholder=\"Title\" value=\"$escTitle\" class=\"form-control mb-2\" style=\"flex:1;\">";
          echo "<input name=\"caption\" placeholder=\"Caption\" value=\"$escCaption\" class=\"form-control mb-3\" style=\"flex:1;\">";
          echo "<button class=\"btn btn-primary edit\" style=\"width:100%;\" type=\"submit\">Save</button></form>";
          echo "<form method=\"POST\" action=\"config/deleteCover.php\" onsubmit=\"return confirm('Delete this cover?');\">";
          echo "<input type=\"hidden\" name=\"path\" value=\"$pathRel\">";
          echo "<button class=\"btn btn-danger delete\" style=\"width:100%;\">Delete</button></form></div>";
        }
      ?>
    </div>
  </div>

    <!-- Home Cards manager -->
    <div class="card p-4 mt-3">
      <h2 class="text-center mb-3" style="font-size:2.0rem; margin-bottom:12px;">Manage Home Cards</h2>
      <div style="display:flex; justify-content:center; margin-bottom:12px;">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cardAddModal">Add Card</button>
      </div>
      <div style="overflow:auto;">
        <table class="table table-striped" style="min-width:720px;">
          <thead><tr><th>ID</th><th>Image</th><th>Title</th><th>Excerpt</th><th>Order</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php
              $cardsRes = $conn->query('SELECT * FROM home_cards ORDER BY sort_order ASC, id ASC');
              while ($c = $cardsRes->fetch_assoc()):
                $cid = (int)$c['id'];
                $ct = htmlspecialchars($c['title']);
                $cd = htmlspecialchars(strlen($c['description'])>120? substr($c['description'],0,117).'...': $c['description']);
                $cs = $c['status']? 'Active':'Inactive';
                $co = (int)$c['sort_order'];
                $img = $c['image_path'] ?? '';
                $imgHtml = '';
                if ($img && file_exists(__DIR__ . '/' . $img)) { $imgHtml = '<img src="'.htmlspecialchars($img).'?v=' . filemtime(__DIR__ . '/' . $img) . '" style="width:60px; height:40px; object-fit:cover; border-radius:4px;">'; }
            ?>
            <tr>
              <td><?= $cid ?></td>
              <td><?= $imgHtml ?></td>
              <td><?= $ct ?></td>
              <td><?= htmlspecialchars($cd) ?></td>
              <td><?= $co ?></td>
              <td><?= $cs ?></td>
              <td style="white-space:nowrap;">
                <button class="btn btn-sm btn-secondary edit-card" data-id="<?= $cid ?>" data-title="<?= htmlspecialchars($c['title'], ENT_QUOTES) ?>" data-description="<?= htmlspecialchars($c['description'], ENT_QUOTES) ?>" data-status="<?= $c['status'] ?>" data-order="<?= $co ?>" data-image="<?= htmlspecialchars($c['image_path'] ?? '', ENT_QUOTES) ?>">Edit</button>
                <form method="POST" action="config/cards_delete.php" style="display:inline; margin-left:6px;">
                  <input type="hidden" name="id" value="<?= $cid ?>">
                  <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this card?');" type="submit">Delete</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Card Modals -->
    <div class="modal fade" id="cardAddModal" tabindex="-1" aria-labelledby="cardAddLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" action="config/cards_add.php" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="cardAddLabel">Add Home Card</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Order</label>
                <input name="sort_order" type="number" class="form-control" value="0">
              </div>
              <div class="mb-3">
                <label class="form-label">Image (optional)</label>
                <input type="file" name="image" id="cardAddImage" accept="image/*" class="form-control">
              </div>
              <div class="mb-3"><img id="cardAddImagePreview" src="" style="display:none; width:120px; height:80px; object-fit:cover; border:1px solid #eee;"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Add</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="cardEditModal" tabindex="-1" aria-labelledby="cardEditLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" action="config/cards_update.php" id="cardEditForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="editCardId">
            <div class="modal-header">
              <h5 class="modal-title" id="cardEditLabel">Edit Home Card</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Title</label>
                <input name="title" id="editCardTitle" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" id="editCardDescription" class="form-control" rows="4" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Order</label>
                <input name="sort_order" id="editCardOrder" type="number" class="form-control" value="0">
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="editCardStatus" name="status" value="1">
                <label class="form-check-label">Active</label>
              </div>
              <div class="mb-3">
                <label class="form-label">Image (optional)</label>
                <input type="file" name="image" id="editCardImage" accept="image/*" class="form-control">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="removeImageCheckbox" name="remove_image" value="1">
                  <label class="form-check-label">Remove existing image</label>
                </div>
              </div>
              <div class="mb-3"><img id="editCardImagePreview" src="" style="display:none; width:120px; height:80px; object-fit:cover; border:1px solid #eee;"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  
  
    <script>
    // Wire edit buttons and handle image preview
    document.querySelectorAll('.edit-card').forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = this.getAttribute('data-id');
        document.getElementById('editCardId').value = id;
        document.getElementById('editCardTitle').value = this.getAttribute('data-title');
        document.getElementById('editCardDescription').value = this.getAttribute('data-description');
        document.getElementById('editCardOrder').value = this.getAttribute('data-order');
        document.getElementById('editCardStatus').checked = this.getAttribute('data-status') == '1';
        // image handling
        var img = this.getAttribute('data-image') || '';
        var preview = document.getElementById('editCardImagePreview');
        if (img) { preview.src = img + '?v=' + (new Date()).getTime(); preview.style.display = 'block'; } else { preview.src = ''; preview.style.display = 'none'; }
        document.getElementById('removeImageCheckbox').checked = false;
        var modal = new bootstrap.Modal(document.getElementById('cardEditModal'));
        modal.show();
      });
    });

    // image previews for add/edit
    var addInput = document.getElementById('cardAddImage');
    var addPreview = document.getElementById('cardAddImagePreview');
    if (addInput) {
      addInput.addEventListener('change', function(e){
        var f = e.target.files[0];
        if (!f) { addPreview.style.display = 'none'; addPreview.src = ''; return; }
        var r = new FileReader(); r.onload = function(ev){ addPreview.src = ev.target.result; addPreview.style.display = 'block'; }; r.readAsDataURL(f);
      });
    }
    var editInput = document.getElementById('editCardImage');
    var editPreview = document.getElementById('editCardImagePreview');
    if (editInput) {
      editInput.addEventListener('change', function(e){
        var f = e.target.files[0];
        if (!f) { return; }
        var r = new FileReader(); r.onload = function(ev){ editPreview.src = ev.target.result; editPreview.style.display = 'block'; document.getElementById('removeImageCheckbox').checked = false; }; r.readAsDataURL(f);
      });
    }

    // feature edit/add wiring
    document.querySelectorAll('.feature-edit').forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = this.getAttribute('data-id');
        document.getElementById('featureId').value = id;
        document.getElementById('featureHeader').value = this.getAttribute('data-header');
        document.getElementById('featureTitle').value = this.getAttribute('data-title');
        document.getElementById('featureBody').value = this.getAttribute('data-body');
        document.getElementById('featureFooter').value = this.getAttribute('data-footer');
        document.getElementById('featureBg').value = this.getAttribute('data-bg') || '#ffffff';
        var modal = new bootstrap.Modal(document.getElementById('featureEditModal'));
        modal.show();
      });
    });
    var addBtn = document.querySelector('.feature-add');
    if (addBtn) {
      addBtn.addEventListener('click', function(){
        document.getElementById('featureId').value = '';
        document.getElementById('featureHeader').value = '';
        document.getElementById('featureTitle').value = '';
        document.getElementById('featureBody').value = '';
        document.getElementById('featureFooter').value = '';
        document.getElementById('featureBg').value = '#ffffff';
      });
    }
    </script>

  <?php
    // load all feature cards for editing
    function _textColorForBg($hex) {
      $hex = ltrim($hex, '#');
      if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
      $r = hexdec(substr($hex,0,2)); $g = hexdec(substr($hex,2,2)); $b = hexdec(substr($hex,4,2));
      $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
      return $brightness > 128 ? '#000000' : '#ffffff';
    }
    $features = [];
    $fQ = $conn->query('SELECT * FROM feature_card ORDER BY id ASC');
    if ($fQ) { while ($r = $fQ->fetch_assoc()) { $features[] = $r; } }
  ?>
  <div class="card p-4 text-center mt-3">
    <h2 class="text-center mb-4" style="font-size:2.0rem; margin-bottom:12px;">Manage features</h2>
    <div style="display:flex; justify-content:center; gap:8px; margin-bottom:12px;">
      <button class="btn btn-success feature-add" data-bs-toggle="modal" data-bs-target="#featureEditModal">Add Feature</button>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center;">
      <?php foreach ($features as $feature):
        $fBg = htmlspecialchars($feature['bg_color']);
        $fText = _textColorForBg($feature['bg_color']);
      ?>
      <div class="card" style="width:320px; background:<?= $fBg ?>; color:<?= $fText ?>;">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><?= htmlspecialchars($feature['header']) ?></span>
          <div>
            <form method="POST" action="config/feature_delete.php" style="display:inline; margin:0;" onsubmit="return confirm('Delete this feature?');"><input type="hidden" name="id" value="<?= $feature['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
          </div>
        </div>
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($feature['title']) ?></h5>
          <p class="card-text"><?= nl2br(htmlspecialchars($feature['body'])) ?></p>
        </div>
        <div class="card-footer" style="color:<?= $fText ?>; opacity:0.9"><?= htmlspecialchars($feature['footer']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Feature add Modal -->
  <div class="modal fade" id="featureEditModal" tabindex="-1" aria-labelledby="featureEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="config/feature_update.php" id="featureForm">
          <input type="hidden" name="id" id="featureId" value="">
          <div class="modal-header">
            <h5 class="modal-title" id="featureEditLabel">Add Feature Card</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Header</label>
              <input name="header" id="featureHeader" class="form-control" value="">
            </div>
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input name="title" id="featureTitle" class="form-control" value="">
            </div>
            <div class="mb-3">
              <label class="form-label">Body</label>
              <textarea name="body" id="featureBody" class="form-control" rows="4"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Footer</label>
              <input name="footer" id="featureFooter" class="form-control" value="">
            </div>
            <div class="mb-3">
              <label class="form-label">Background color</label>
              <input type="color" name="bg_color" id="featureBg" class="form-control form-control-color" value="#ffffff" style="width:80px;">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  

</div>

<script>
  // Logo input & preview (works with modal)
  var filenameEl = document.getElementById('logoFilename');
  var fileInput = document.getElementById('logoInput') || document.querySelector('input[name="logo"]');
  var modalPreview = document.getElementById('modalLogoPreview');
  var currentLogo = document.getElementById('currentLogo');
  if (fileInput) {
    fileInput.addEventListener('change', function(e){
      var f = e.target.files[0];
      if (!f) { if (filenameEl) { filenameEl.style.display = 'none'; filenameEl.textContent = ''; } if (modalPreview) modalPreview.src = currentLogo.src; return; }
      var reader = new FileReader();
      reader.onload = function(ev){ if (currentLogo) currentLogo.src = ev.target.result; if (modalPreview) modalPreview.src = ev.target.result; };
      reader.readAsDataURL(f);
      if (filenameEl) { filenameEl.textContent = f.name; filenameEl.style.display = 'block'; }
      var filenameModal = document.getElementById('logoFilenameModal');
      if (filenameModal) { filenameModal.textContent = f.name; filenameModal.style.display = 'block'; }
    });
  }

  // Reset to default (calls handler with reset flag)
  var resetBtn = document.getElementById('resetLogo');
  if (resetBtn) {
    resetBtn.addEventListener('click', function(){
      if (!confirm('Reset logo to the default image?')) return;
      var f = document.createElement('form');
      f.method = 'POST'; f.action = 'config/updateLogo.php';
      var inp = document.createElement('input'); inp.type = 'hidden'; inp.name = 'reset'; inp.value = '1'; f.appendChild(inp);
      document.body.appendChild(f); f.submit();
    });
  }

  // Reset modal state when opened
  var logoModal = document.getElementById('logoModal');
  if (logoModal) {
    logoModal.addEventListener('show.bs.modal', function(){
      var inp = document.getElementById('logoInput');
      if (inp) { inp.value = ''; }
      if (modalPreview && currentLogo) { modalPreview.src = currentLogo.src; }
      var fn = document.getElementById('logoFilenameModal'); if (fn) { fn.style.display = 'none'; fn.textContent = ''; }
    });
  }

  // Covers upload modal handling: preview and filename
  var coverFilenameEl = document.getElementById('coverFilename');
  var coverInput = document.getElementById('coverInput');
  var coverPreview = document.getElementById('coverPreview');
  var coverFilenameModal = document.getElementById('coverFilenameModal');
  if (coverInput) {
    coverInput.addEventListener('change', function(e){
      var f = e.target.files[0];
      if (!f) {
        if (coverFilenameEl) { coverFilenameEl.style.display = 'none'; coverFilenameEl.textContent = ''; }
        if (coverFilenameModal) { coverFilenameModal.style.display = 'none'; coverFilenameModal.textContent = ''; }
        if (coverPreview) { coverPreview.style.display = 'none'; coverPreview.src = ''; }
        return;
      }
      var reader = new FileReader();
      reader.onload = function(ev){ if (coverPreview) { coverPreview.src = ev.target.result; coverPreview.style.display = 'block'; } };
      reader.readAsDataURL(f);
      if (coverFilenameEl) { coverFilenameEl.textContent = f.name; coverFilenameEl.style.display = 'block'; }
      if (coverFilenameModal) { coverFilenameModal.textContent = f.name; coverFilenameModal.style.display = 'block'; }
    });
  }

  var coverModal = document.getElementById('coverModal');
  if (coverModal) {
    coverModal.addEventListener('show.bs.modal', function(){
      var inp = document.getElementById('coverInput'); if (inp) { inp.value = ''; }
      if (coverPreview) { coverPreview.style.display = 'none'; coverPreview.src = ''; }
      if (coverFilenameModal) { coverFilenameModal.style.display = 'none'; coverFilenameModal.textContent = ''; }
    });
  }

  // Profile preview (client side)
  var previewBtn = document.getElementById('previewProfile');
  if (previewBtn) {
    previewBtn.addEventListener('click', function(){
      var inst = document.querySelector('input[name="inst"]').value;
      var loc = document.querySelector('input[name="location"]').value;
      var email = document.querySelector('input[name="email"]').value;
      var phone = document.querySelector('input[name="phone"]').value;
      var tty = document.querySelector('input[name="tty"]').value;
      var preview = inst + '<br><br>' + loc + '<br><br>' + email + '<br><br>' + phone + '<br><br>' + tty;
      document.getElementById('profilePreview').innerHTML = preview;
    });
  }
</script>