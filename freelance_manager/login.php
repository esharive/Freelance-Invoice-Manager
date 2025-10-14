<?php
// login.php - standalone login page (Bootstrap5, responsive, animated, interactive)
session_start();

// Compute a robust base path for building links (auto-detect where this script lives).
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$BASE_PATH = $base_dir === '/' ? '' : $base_dir;

require_once __DIR__ . '/config/db.php';

$err = '';
$email = $_POST['email'] ?? '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $err = 'Provide email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // Successful login: regenerate session id
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];

                header('Location: ' . $BASE_PATH . '/index.php');
                exit;
            } else {
                $err = 'Invalid credentials.';
            }
        } else {
            $err = 'Invalid credentials.';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login — Freelance Manager</title>

  <!-- Bootstrap & icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#f4f7fb; --card:#fff; --brand:#0d6efd; --muted:#6c757d; --radius:12px;
    }
    body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial;background:var(--bg);margin:0;-webkit-font-smoothing:antialiased;}
    .auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:36px;}
    .auth-card{width:100%;max-width:920px;border-radius:14px;background:linear-gradient(180deg,#fff,#fbfdff);box-shadow:0 20px 60px rgba(11,24,40,0.06);overflow:hidden;display:grid;grid-template-columns:1fr 420px;}
    .auth-left{padding:36px 40px;}
    .auth-right{padding:28px;background:linear-gradient(180deg,#fbfdff,#ffffff);display:flex;align-items:center;justify-content:center}
    .brand {font-weight:700;color:var(--brand);font-size:1.1rem}
    h1{font-size:1.4rem;margin:0 0 8px 0}
    .muted{color:var(--muted)}
    .form-control:focus{box-shadow:0 6px 24px rgba(13,110,253,0.08);border-color:var(--brand)}
    .btn-primary{border-radius:10px;padding:.6rem 1rem}
    .fade-up{opacity:0;transform:translateY(18px);transition:all 520ms cubic-bezier(.2,.9,.3,1)}
    .fade-up.in{opacity:1;transform:none}
    .small-muted{color:var(--muted);font-size:.9rem}
    .forgot-link{font-size:.95rem}
    @media (max-width:900px){
      .auth-card{grid-template-columns:1fr}
      .auth-right{order:-1;padding:18px}
    }
  </style>
</head>
<body>

<!-- simple top bar -->
<nav class="navbar navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $BASE_PATH ?>/home.php">
      <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#0d6efd,#0b5ed7);display:flex;align-items:center;justify-content:center;color:#fff">
        <i class="bi bi-briefcase-fill"></i>
      </div>
      <div style="line-height:1">
        <div style="font-weight:700">Freelance<span style="color:#0b5ed7">Manager</span></div>
        <small class="text-muted" style="font-size:.75rem">Invoices & Clients</small>
      </div>
    </a>
    <div>
      <a class="btn btn-outline-primary btn-sm" href="<?= $BASE_PATH ?>/register.php"><i class="bi bi-person-plus me-1"></i> Register</a>
    </div>
  </div>
</nav>

<!-- Login card -->
<div class="auth-wrap">
  <div class="auth-card">
    <!-- left: form -->
    <div class="auth-left fade-up" id="loginCard">
      <div class="mb-3 d-flex align-items-center gap-3">
        <div style="width:54px;height:54px;border-radius:12px;background:linear-gradient(135deg,#0d6efd,#0b5ed7);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem">
          <i class="bi bi-box-arrow-in-right"></i>
        </div>
        <div>
          <div class="brand">FreelanceManager</div>
          <div class="small-muted">Welcome back — sign in to continue</div>
        </div>
      </div>

      <h1>Login to your account</h1>
      <p class="muted">Enter your credentials to access your dashboard.</p>

      <?php if ($err): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($err); ?></div>
      <?php endif; ?>

      <form method="post" id="loginForm" novalidate>
        <div class="mb-3">
          <label class="form-label small">Email</label>
          <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>" placeholder="you@company.com">
        </div>

        <div class="mb-2">
          <label class="form-label small">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password" class="form-control" required placeholder="Your password">
            <button type="button" id="pwToggle" class="btn btn-outline-secondary" title="Show / hide password"><i class="bi bi-eye"></i></button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="rememberMe" name="remember">
            <label class="form-check-label small" for="rememberMe">Remember me</label>
          </div>
          <div><a class="forgot-link" href="<?= $BASE_PATH ?>/forgot_password.php">Forgot?</a></div>
        </div>

        <div class="mb-3">
          <button id="submitBtn" type="submit" class="btn btn-primary w-100">Sign in</button>
        </div>

        <div class="text-center small-muted">No account? <a href="<?= $BASE_PATH ?>/register.php">Create one</a></div>
      </form>
    </div>

    <!-- right: marketing -->
    <div class="auth-right fade-up" data-delay="120">
      <div style="max-width:320px;text-align:center">
        <div style="font-weight:700;font-size:1.05rem">Why FreelanceManager?</div>
        <p class="muted small mt-2">Fast invoice creation • Client management • Payment tracking • Simple reports</p>

        <div class="card-soft mt-3 p-3">
          <div class="d-flex justify-content-between">
            <div><strong>Secure by design</strong><div class="muted small">We hash passwords and protect sessions</div></div>
            <div><span class="badge bg-light text-muted">v1.0</span></div>
          </div>
          <div class="mt-2 small-muted">Need help? <a href="<?= $BASE_PATH ?>/contact.php">Contact support</a></div>
        </div>

        <div class="mt-3">
          <a class="btn btn-outline-primary w-100 mb-2" href="<?= $BASE_PATH ?>/home.php"><i class="bi bi-eye me-1"></i> View tour</a>
          <a class="btn btn-link w-100" href="<?= $BASE_PATH ?>/privacy.php">Privacy policy</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // expose base path to JS (safely encoded)
  const BASE_PATH = <?= json_encode($BASE_PATH); ?>;

  // Entrance animation
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.fade-up').forEach((el, i) => {
      const delay = parseInt(el.dataset.delay || 60) + i*40;
      setTimeout(()=> el.classList.add('in'), delay);
    });

    // Password toggle
    const pwToggle = document.getElementById('pwToggle');
    const pwInput = document.getElementById('password');
    pwToggle?.addEventListener('click', function(){
      if (pwInput.type === 'password') { pwInput.type = 'text'; pwToggle.innerHTML = '<i class="bi bi-eye-slash"></i>'; }
      else { pwInput.type = 'password'; pwToggle.innerHTML = '<i class="bi bi-eye"></i>'; }
    });

    // Improve UX: focus email on load
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) emailInput.focus();

    // Prevent double submit & simple client-side validation
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    form?.addEventListener('submit', function(e){
      const email = (form.querySelector('[name="email"]').value || '').trim();
      const pw = (form.querySelector('[name="password"]').value || '');
      if (!email || !pw) {
        e.preventDefault();
        alert('Please provide email and password.');
        return;
      }
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Signing in...';
    });
  });
</script>
</body>
</html>
