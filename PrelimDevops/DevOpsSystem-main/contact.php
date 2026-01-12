<?php
SESSION_START();  
include 'config/plugins.php';
require_once __DIR__ . '/config/site.php';
?>

<style>
/* HERO SECTION */
.contact-hero {
  background: url('image/wow.jpg') center/cover no-repeat;
  height: 300px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.contact-hero::after {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.24);
}

.contact-hero h1 {
  position: relative;
  color: #fff;
  font-weight: 600;
  letter-spacing: 1.2px;
}

/* CONTACT CARD */
.contact-card {
  margin-top: -100px;
  border-radius: 20px;
  border: none;
  background: #ffffff;
  box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

/* FORM LABELS */
.contact-card label {
  font-weight: 500;
  margin-bottom: 6px;
  color: #333;
}

/* INPUTS & TEXTAREA – PREMIUM */
.contact-card .form-control {
  background-color: #f3f3f3;          /* softer than white */
  border: 1px solid #ccc;
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 0.95rem;
  color: #000;
  transition: 0.2s ease-in-out;
}

/* Placeholder */
.contact-card .form-control::placeholder {
  color: #666;
}

/* Focus effect – BLACK highlight */
.contact-card .form-control:focus {
  background-color: #f8f8f8;
  border-color: #000;
  box-shadow: 0 0 0 0.15rem rgba(0,0,0,0.25);
}

/* TEXTAREA tweaks */
.contact-card textarea.form-control {
  resize: none;
  line-height: 1.6;
}

/* SEND BUTTON – PREMIUM */
#sendBtn {
  border-radius: 50px;
  padding: 10px 26px;
  font-weight: 500;
  letter-spacing: 0.5px;
  transition: 0.25s ease;
}

#sendBtn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 18px rgba(13,110,253,0.35);
}

/* INFO CARDS */
.info-card {
  border-radius: 16px;
  padding: 26px 20px;
  background: #f8f9fa;
  text-align: center;
  transition: 0.3s ease;
}

.info-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.12);
}

.info-card i {
  font-size: 1.8rem;
  color: #0d6efd;
  margin-bottom: 10px;
}

.info-card p {
  margin: 0;
  font-weight: 500;
  color: #333;
}
</style>

<nav class="navbar navbar-expand-sm bg-light navbar-light" 
     style="box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000;">
  <div class="container-fluid container py-4">

    <?php
      $logoPath = __DIR__ . '/' . $SITE_LOGO;
      $logoUrl = htmlspecialchars($SITE_LOGO);
      if (file_exists($logoPath)) { 
        $logoUrl .= '?v=' . filemtime($logoPath); 
      }
    ?>

    <a class="navbar-brand" href="index.php">
      <img src="<?= $logoUrl ?>" alt="Logo" style="height:40px;">
    </a>

    <button class="navbar-toggler" type="button" 
            data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="collapsibleNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link fs-6" style="width:4rem;" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fs-6" style="width:6rem;" href="contact.php">Contact Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fs-6" style="width:6rem;" href="enroll.php">Enroll Now</a>
        </li>
      </ul>

    </div>
  </div>
</nav>

<!-- HERO -->
<section class="contact-hero">

</section>

<!-- CONTACT CONTENT -->
<div class="container mb-5">
  <div class="card contact-card p-4 p-md-5">
    <div class="row g-4">

      <!-- FORM -->
      <div class="col-md-7">
        <h4 class="mb-3">Send us a message</h4>

        <form action="config/addContact.php" method="POST">
          <label>Name</label>
          <input type="text" name="name" class="form-control mb-3" required>

          <label>Email</label>
          <input type="email" name="email" class="form-control mb-3" required>

          <label>Message</label>
          <textarea name="message" class="form-control mb-3" rows="4" required></textarea>

          <label>Captcha</label>
          <div class="row mb-2">
            <div class="col">
              <p id="captchaDisplay" class="fw-bold fs-5"></p>
            </div>
            <div class="col">
              <input type="text" id="captchaInput" name="captcha" class="form-control" required>
            </div>
          </div>

          <div id="captchaError" class="text-danger mb-2" style="display:none;">
            Captcha code is incorrect.
          </div>

          <?php
          if (isset($_SESSION['success'])){
            echo '<div class="text-success mb-2">'.$_SESSION['success'].'</div>';
            unset($_SESSION['success']);
          }
          ?>

          <button id="sendBtn" type="button" class="btn btn-primary px-4">
            Send Message
          </button>
        </form>
      </div>

      <!-- INFO -->
      <div class="col-md-5">
        <div class="row g-3">
          <div class="col-12 info-card">
            <i class="fa-solid fa-school"></i>
            <p><?= htmlspecialchars($SITE_INST) ?></p>
          </div>
          <div class="col-12 info-card">
            <i class="fa-solid fa-location-dot"></i>
            <p><?= htmlspecialchars($SITE_LOCATION) ?></p>
          </div>
          <div class="col-12 info-card">
            <i class="fa-solid fa-envelope"></i>
            <p><?= htmlspecialchars($SITE_EMAIL) ?></p>
          </div>
          <div class="col-12 info-card">
            <i class="fa-solid fa-phone"></i>
            <p><?= htmlspecialchars($SITE_PHONE) ?></p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<<script>
const captchaChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%&!";
let captchaCode = "";

function generateCaptcha(length = 6) {
  let result = "";
  for (let i = 0; i < length; i++) {
    result += captchaChars.charAt(
      Math.floor(Math.random() * captchaChars.length)
    );
  }
  return result;
}

// Generate captcha on load
captchaCode = generateCaptcha();
document.getElementById('captchaDisplay').textContent = captchaCode;

document.getElementById('sendBtn').addEventListener('click', function() {
  const userInput = document.getElementById('captchaInput').value.trim();

  if (userInput === captchaCode) {
    document.querySelector('form').submit();
  } else {
    document.getElementById('captchaError').style.display = 'block';
    captchaCode = generateCaptcha();
    document.getElementById('captchaDisplay').textContent = captchaCode;
  }
});
</script>
