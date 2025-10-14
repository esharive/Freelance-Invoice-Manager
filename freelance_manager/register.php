<?php
// register.php - standalone registration page (Bootstrap5, interactive, secure)
session_start();

// Compute a robust base path for building links (auto-detect where this script lives).
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$BASE_PATH = $base_dir === '/' ? '' : $base_dir;

require_once __DIR__ . '/config/db.php';

$errors = [];
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

// POST handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // server-side validation
    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
    }

    // check duplicate email (only if no previous errors)
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $chk->bind_param('s', $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $errors[] = 'This email is already registered. Did you mean to login?';
        }
        $chk->close();
    }

    // create user
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $ins->bind_param('sss', $name, $email, $password_hash);
        if ($ins->execute()) {
            // set session and redirect
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_name'] = $name;

            header('Location: ' . $BASE_PATH . '/index.php');
            exit;
        } else {
            $errors[] = 'Could not create account. Please try again later.';
        }
        $ins->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Create account — Freelance Manager</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#f4f7fb; --card:#fff; --brand:#0d6efd; --muted:#6c757d; --radius:12px;
    }
    body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial;background:var(--bg);margin:0;-webkit-font-smoothing:antialiased;}
    .auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px;}
    .auth-card{width:100%;max-width:980px;border-radius:14px;background:linear-gradient(180deg,#fff,#fbfdff);box-shadow:0 20px 60px rgba(11,24,40,0.06);overflow:hidden;display:grid;grid-template-columns:1fr 420px;}
    .auth-left{padding:42px 40px;}
    .auth-right{padding:28px;background:linear-gradient(180deg,#fbfdff,#ffffff);display:flex;align-items:center;justify-content:center}
    .brand {font-weight:700;color:var(--brand);font-size:1.1rem}
    h1{font-size:1.5rem;margin:0 0 8px 0}
    .muted{color:var(--muted)}
    .form-control:focus{box-shadow:0 6px 24px rgba(13,110,253,0.08);border-color:var(--brand)}
    .pw-meter {height:8px;border-radius:6px;background:#e9ecef;overflow:hidden}
    .pw-meter > i {display:block;height:100%;transition:width .28s ease}
    .pw-weak{width:33%;background:#ff6b6b}
    .pw-medium{width:66%;background:#ffd24d}
    .pw-strong{width:100%;background:#4cd964}
    .btn-primary{border-radius:10px;padding:.6rem 1rem}
    .small-muted{color:var(--muted);font-size:.9rem}
    @media (max-width: 900px){
      .auth-card{grid-template-columns:1fr}
      .auth-right{order:-1;padding:18px}
    }
  </style>
</head>
<body>

<div class="auth-wrap">
  <div class="auth-card">
    <!-- left: form -->
    <div class="auth-left">
      <div class="mb-3 d-flex align-items-center gap-3">
        <div style="width:54px;height:54px;border-radius:12px;background:linear-gradient(135deg,#0d6efd,#0b5ed7);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem">
          <i class="bi bi-people-fill"></i>
        </div>
        <div>
          <div class="brand">FreelanceManager</div>
          <div class="small-muted">Create your account</div>
        </div>
      </div>

      <h1>Create an account</h1>
      <p class="muted">Sign up and start creating invoices, managing clients and tracking payments.</p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" id="registerForm" novalidate>
        <div class="mb-3">
          <label class="form-label small">Full name</label>
          <input name="name" class="form-control" required value="<?php echo htmlspecialchars($name); ?>" placeholder="Your full name">
        </div>

        <div class="mb-3">
          <label class="form-label small">Email address</label>
          <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>" placeholder="you@business.com">
        </div>

        <div class="mb-3">
          <label class="form-label small">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password" class="form-control" required placeholder="At least 8 characters" aria-describedby="pwToggle">
            <button class="btn btn-outline-secondary" type="button" id="pwToggle" title="Show / hide password"><i class="bi bi-eye"></i></button>
          </div>
          <div class="d-flex align-items-center gap-3 mt-2">
            <div class="pw-meter" style="flex:1"><i id="pwBar" class=""></i></div>
            <div id="pwLabel" class="small-muted" style="width:120px;text-align:right">Strength</div>
          </div>
        </div>

        <div class="mb-3">
          <button id="submitBtn" type="submit" class="btn btn-primary w-100">Create account</button>
        </div>

        <div class="text-center small-muted">Already have an account? <a href="<?= $BASE_PATH ?>/login.php">Login</a></div>
      </form>
    </div>

    <!-- right: marketing / benefits -->
    <div class="auth-right">
      <div style="max-width:320px">
        <div style="font-weight:700;font-size:1.05rem">Why choose FreelanceManager?</div>
        <ul class="mt-3" style="padding-left:1rem">
          <li class="muted small">Create itemized invoices and download as PDF</li>
          <li class="muted small">Save clients and projects for repeat billing</li>
          <li class="muted small">Record payments and reconcile outstanding balances</li>
          <li class="muted small">Simple, fast and built for freelancers</li>
        </ul>

        <div class="mt-4">
          <div class="card-soft p-3">
            <div class="d-flex justify-content-between">
              <div><strong>Start free</strong><div class="muted small">No credit card required</div></div>
              <div class="text-end"><span class="badge bg-light text-muted">v1.0</span></div>
            </div>
            <div class="mt-2 small-muted">By creating an account you agree to our <a href="<?= $BASE_PATH ?>/privacy.php">Privacy Policy</a>.</div>
          </div>
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

  // password toggle
  const pwToggle = document.getElementById('pwToggle');
  const pwInput = document.getElementById('password');
  const pwBar = document.getElementById('pwBar');
  const pwLabel = document.getElementById('pwLabel');
  const submitBtn = document.getElementById('submitBtn');
  const form = document.getElementById('registerForm');

  pwToggle?.addEventListener('click', () => {
    if (pwInput.type === 'password') {
      pwInput.type = 'text';
      pwToggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
      pwInput.type = 'password';
      pwToggle.innerHTML = '<i class="bi bi-eye"></i>';
    }
  });

  // password strength meter (simple rules)
  function evaluatePassword(pw) {
    let score = 0;
    if (!pw) return 0;
    if (pw.length >= 8) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return Math.min(score, 3);
  }

  pwInput?.addEventListener('input', () => {
    const val = pwInput.value;
    const score = evaluatePassword(val);
    pwBar.className = '';
    if (score <= 0) { pwBar.style.width = '0%'; pwLabel.textContent = 'Strength'; }
    else if (score === 1) { pwBar.classList.add('pw-weak'); pwBar.style.width = '33%'; pwLabel.textContent = 'Weak'; }
    else if (score === 2) { pwBar.classList.add('pw-medium'); pwBar.style.width = '66%'; pwLabel.textContent = 'Okay'; }
    else { pwBar.classList.add('pw-strong'); pwBar.style.width = '100%'; pwLabel.textContent = 'Strong'; }
  });

  // prevent double submit, simple client-side validation
  form?.addEventListener('submit', function(e){
    // simple client-side checks to improve UX
    const name = form.querySelector('[name="name"]').value.trim();
    const email = form.querySelector('[name="email"]').value.trim();
    const pw = pwInput.value;

    if (!name || !email || !pw) {
      e.preventDefault();
      alert('Please fill all fields.');
      return;
    }
    if (pw.length < 8) {
      e.preventDefault();
      alert('Password must be at least 8 characters.');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Creating account...';
  });
</script>
</body>
</html>
