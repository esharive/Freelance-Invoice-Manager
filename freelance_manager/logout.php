<?php
// logout.php - friendly, animated logout page (standalone, Bootstrap5)
session_start();

// Compute a robust base path for building links (auto-detect where this script lives).
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$BASE_PATH = $base_dir === '/' ? '' : $base_dir;

// capture user name (if any) for friendly message, then destroy session
$user_name = $_SESSION['user_name'] ?? '';

// clear session data and destroy
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

// sanitize for output
$safe_name = $user_name ? htmlspecialchars($user_name) : '';
$redirect_to = $BASE_PATH . '/login.php';
$auto_seconds = 4;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Signed out — Freelance Manager</title>

  <!-- Bootstrap & icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#f4f7fb; --card:#fff; --brand:#0d6efd; --accent:#0b5ed7; --muted:#6c757d; --radius:12px;
    }
    body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial;background:var(--bg);margin:0;-webkit-font-smoothing:antialiased;}
    .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px}
    .box{width:100%;max-width:880px;background:linear-gradient(180deg,#fff,#fbfdff);border-radius:14px;padding:28px;box-shadow:0 20px 60px rgba(11,24,40,0.06);display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:center}
    .brand {font-weight:700;color:var(--brand);font-size:1.05rem}
    .goodbye {font-size:1.5rem;font-weight:700}
    .muted {color:var(--muted)}
    .btn-primary{border-radius:10px;padding:.55rem 1rem}
    .illustr {display:flex;align-items:center;justify-content:center;border-radius:10px;background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;height:220px}
    .count {font-weight:700;color:var(--brand)}
    .fade-in {opacity:0; transform:translateY(12px); transition: all 480ms cubic-bezier(.2,.9,.3,1);}
    .fade-in.in {opacity:1; transform:none;}
    @media (max-width:920px){ .box{grid-template-columns:1fr; padding:18px} .illustr{height:140px} }
  </style>
</head>
<body>

<div class="wrap">
  <div class="box fade-in" id="logoutBox">
    <div>
      <div class="d-flex align-items-center gap-3 mb-3">
        <div style="width:52px;height:52px;border-radius:10px;background:linear-gradient(135deg,var(--brand),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem">
          <i class="bi bi-box-arrow-right"></i>
        </div>
        <div>
          <div class="brand">FreelanceManager</div>
          <div class="muted small">Invoices & Clients</div>
        </div>
      </div>

      <div class="goodbye mb-2">
        <?php if ($safe_name): ?>
          Goodbye, <?php echo $safe_name; ?> — you are signed out.
        <?php else: ?>
          You are signed out.
        <?php endif; ?>
      </div>

      <p class="muted">For your security, your session has been cleared. You will be redirected to the login page in <span class="count" id="countdown"><?php echo (int)$auto_seconds; ?></span> seconds.</p>

      <div class="mt-3 d-flex gap-2 flex-wrap">
        <a href="<?php echo htmlspecialchars($redirect_to); ?>" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i> Sign in</a>
        <a href="<?= $BASE_PATH ?>/home.php" class="btn btn-outline-secondary"><i class="bi bi-house me-1"></i> Home</a>
        <button id="cancelBtn" class="btn btn-ghost btn-outline-secondary">Cancel redirect</button>
      </div>

      <div class="mt-3 small muted">If you didn't sign out, please <a href="<?= $BASE_PATH ?>/support.php">contact support</a>.</div>
    </div>

    <div class="illustr text-center">
      <div>
        <div style="font-size:44px;line-height:1"><i class="bi bi-check2-circle"></i></div>
        <div class="mt-2" style="font-weight:600">Signed out</div>
        <div class="muted small mt-1">Safe & secure</div>
      </div>
    </div>
  </div>
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // expose base path to JS (safely encoded)
  const BASE_PATH = <?= json_encode($BASE_PATH); ?>;

  // entrance animation
  document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('logoutBox').classList.add('in');
  });

  // countdown + auto-redirect (user can cancel)
  (function(){
    var seconds = <?php echo (int)$auto_seconds; ?>;
    var countdownEl = document.getElementById('countdown');
    var cancelBtn = document.getElementById('cancelBtn');
    var redirectTo = <?= json_encode($redirect_to); ?>;
    var timer = null;
    function tick(){
      seconds--;
      if (countdownEl) countdownEl.textContent = seconds;
      if (seconds <= 0) {
        window.location.href = redirectTo;
      }
    }
    timer = setInterval(tick, 1000);

    cancelBtn.addEventListener('click', function(){
      if (timer) { clearInterval(timer); timer = null; }
      cancelBtn.textContent = 'Redirect cancelled';
      cancelBtn.disabled = true;
      if (countdownEl) countdownEl.textContent = '—';
    });
  })();
</script>
</body>
</html>
