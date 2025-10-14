<?php
// home.php - standalone public landing / hero page (Bootstrap 5, responsive, animated)
session_start();

// Compute a robust base path for building links (auto-detect where this script lives).
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$BASE_PATH = $base_dir === '/' ? '' : $base_dir;

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Freelance Invoice & Clients Manager — Home</title>

  <!-- Fonts & icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg: #f4f7fb;
      --card: #fff;
      --muted: #6c757d;
      --brand: #0d6efd;
      --accent1: #0d6efd;
      --accent2: #0b5ed7;
      --radius: 12px;
      --glass: rgba(255,255,255,0.85);
    }
    *{box-sizing:border-box}
    body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial; background:var(--bg); color:#0b1a2b; margin:0; -webkit-font-smoothing:antialiased;}
    a {text-decoration:none;}
    /* NAVBAR */
    .navbar-brand {font-weight:700; color:var(--brand); letter-spacing:0.2px}
    .nav-link {color:#3b4a5a}
    .btn-ghost {border-radius:10px;padding:.55rem .9rem}
    /* HERO */
    .hero {
      background: linear-gradient(180deg, rgba(13,110,253,0.06), rgba(11,94,215,0.02));
      border-radius:16px;
      padding:34px;
      box-shadow: 0 12px 40px rgba(11,78,200,0.06);
      position:relative;
      overflow:hidden;
    }
    .hero .title {font-size:clamp(1.6rem, 3.6vw, 2.6rem); font-weight:800; line-height:1.02;}
    .hero .sub {color:var(--muted); font-size:1.02rem; margin-top:.5rem}
    .feature-icon { width:56px; height:56px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.2rem; background:linear-gradient(135deg,var(--accent1),var(--accent2)); box-shadow: 0 8px 24px rgba(11,78,200,0.12) }
    .screenshot { background:linear-gradient(180deg,#fff,#fbfdff); border-radius:12px; border:1px solid rgba(11,78,200,0.06); padding:14px; }
    .muted {color:var(--muted)}

    /* cards/tables */
    .card-soft {background:var(--card); border-radius:12px; padding:16px; box-shadow: 0 10px 30px rgba(11,24,40,0.04);}

    /* animations (simple CSS + JS toggle) */
    .fade-up {opacity:0; transform: translateY(18px); transition: all 520ms cubic-bezier(.2,.9,.3,1);}
    .fade-up.in {opacity:1; transform:none;}
    .pop {transform: scale(.98); transition: transform 160ms ease;}
    .pop:active {transform: scale(.96);}

    /* responsive tweaks */
    @media (max-width: 767px) {
      .hero { padding:22px; }
      .feature-icon { width:48px; height:48px; }
    }

    footer {font-size:.9rem; color:var(--muted); padding:28px 0;}
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $BASE_PATH ?>/home.php">
      <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--accent1),var(--accent2));display:flex;align-items:center;justify-content:center;color:#fff">
        <i class="bi bi-briefcase-fill"></i>
      </div>
      <div style="line-height:1">
        <div style="font-weight:700">Freelance<span style="color:var(--accent2)">Manager</span></div>
        <small class="text-muted" style="font-size:.75rem">Invoices & Clients</small>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
        <li class="nav-item"><a class="nav-link" href="#resources">Resources</a></li>
        <li class="nav-item ms-lg-3">
          <?php if ($user_id): ?>
            <a class="btn btn-outline-secondary btn-ghost me-2" href="<?= $BASE_PATH ?>/index.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
            <a class="btn btn-danger" href="<?= $BASE_PATH ?>/logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
          <?php else: ?>
            <a class="btn btn-outline-primary btn-ghost me-2" href="<?= $BASE_PATH ?>/login.php"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
            <a class="btn btn-primary" href="<?= $BASE_PATH ?>/register.php"><i class="bi bi-person-plus me-1"></i> Get Started</a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>
<a href="../../works.html">
  <button class="btn btn-warning" id="start-button">Back</button>
</a>

<!-- HERO -->
<section class="py-5">
  <div class="container">
    <div class="row gx-4 align-items-center">
      <div class="col-lg-7">
        <div class="hero fade-up" id="heroCard">
          <div class="d-flex gap-3 align-items-start">
            <div>
              <div style="width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,var(--accent1),var(--accent2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem">
                <i class="bi bi-file-earmark-text-fill"></i>
              </div>
            </div>
            <div class="flex-grow-1">
              <div class="title">Freelance Invoice & Clients Manager</div>
              <p class="sub">Create professional invoices, manage clients & projects, record payments and stay paid — all from a clean dashboard tailored for freelancers & small businesses.</p>

              <div class="mt-3 d-flex flex-wrap gap-2">
                <?php if ($user_id): ?>
                  <a class="btn btn-lg btn-primary pop" href="<?= $BASE_PATH ?>/index.php"><i class="bi bi-speedometer2 me-2"></i> Go to Dashboard</a>
                  <a class="btn btn-outline-secondary btn-ghost pop" href="<?= $BASE_PATH ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                <?php else: ?>
                  <a class="btn btn-lg btn-primary pop" href="<?= $BASE_PATH ?>/register.php"><i class="bi bi-person-plus me-2"></i> Get started — Free</a>
                  <a class="btn btn-outline-secondary btn-ghost pop" href="<?= $BASE_PATH ?>/login.php"><i class="bi bi-box-arrow-in-right me-2"></i> Login</a>
                <?php endif; ?>
              </div>

              <div class="mt-3 small muted">No credit card required • Quick setup • GDPR friendly</div>
            </div>
          </div>
        </div>

        <!-- Features grid -->
        <div class="row mt-4 g-3" id="features">
          <div class="col-md-6">
            <div class="card-soft d-flex gap-3 align-items-start fade-up" data-delay="60">
              <div class="feature-icon"><i class="bi bi-receipt"></i></div>
              <div>
                <div style="font-weight:700">Professional invoices</div>
                <div class="muted small">Itemised invoices, PDF download, and email with attachments.</div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card-soft d-flex gap-3 align-items-start fade-up" data-delay="140">
              <div class="feature-icon"><i class="bi bi-people"></i></div>
              <div>
                <div style="font-weight:700">Client management</div>
                <div class="muted small">Store client details & company info for repeat billing.</div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card-soft d-flex gap-3 align-items-start fade-up" data-delay="220">
              <div class="feature-icon"><i class="bi bi-cash-stack"></i></div>
              <div>
                <div style="font-weight:700">Payments & records</div>
                <div class="muted small">Record payments, receipts, and reconcile outstanding balances.</div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card-soft d-flex gap-3 align-items-start fade-up" data-delay="300">
              <div class="feature-icon"><i class="bi bi-graph-up"></i></div>
              <div>
                <div style="font-weight:700">Simple reporting</div>
                <div class="muted small">At-a-glance totals, exportable ledgers and per-currency views.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right column mock preview -->
      <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="screenshot fade-up" data-delay="120">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div><strong>Overview</strong><div class="muted small">Invoices & recent payments</div></div>
            <div class="muted small"><?php echo date('M j, Y'); ?></div>
          </div>

          <div style="background:#fff;padding:10px;border-radius:8px;border:1px solid rgba(11,78,200,0.04)">
            <table class="table table-sm mb-0">
              <thead class="small text-muted">
                <tr><th>Invoice</th><th class="text-end">Amount</th></tr>
              </thead>
              <tbody>
                <tr><td>#1009 — Acme Co.</td><td class="text-end">$1,200.00</td></tr>
                <tr><td>#1008 — Beta Ltd.</td><td class="text-end">₦350,000.00</td></tr>
                <tr><td>#1007 — Gamma LLC</td><td class="text-end">€780.00</td></tr>
              </tbody>
            </table>
          </div>

          <div class="mt-3 d-flex justify-content-between">
            <div class="small muted">Tip: Create your first invoice</div>
            <a class="btn btn-sm btn-primary" href="<?= $BASE_PATH ?>/invoices/add.php"><i class="bi bi-plus-lg"></i> New Invoice</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Logos / trusted -->
    <div class="row mt-5 align-items-center">
      <div class="col-md-8">
        <div class="muted small">Trusted by freelancers & small teams</div>
        <div class="d-flex gap-3 flex-wrap mt-3">
          <div class="card-soft" style="min-width:96px;text-align:center">Acme</div>
          <div class="card-soft" style="min-width:96px;text-align:center">Beta</div>
          <div class="card-soft" style="min-width:96px;text-align:center">Gamma</div>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a class="btn btn-outline-primary btn-sm me-2" href="<?= $BASE_PATH ?>/register.php">Start free</a>
        <a class="btn btn-link btn-sm" href="<?= $BASE_PATH ?>/login.php">Or login</a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-white border-top">
  <div class="container">
    <div class="row py-3 align-items-center">
      <div class="col-md-6">
        <div>© <?php echo date('Y'); ?> Freelance Manager</div>
        <div class="muted small">Built for freelancers & small businesses</div>
      </div>
      <div class="col-md-6 text-md-end">
        <a class="muted small me-3" href="<?= $BASE_PATH ?>/docs/getting-started.pdf">Docs</a>
        <a class="muted small me-3" href="<?= $BASE_PATH ?>/privacy.php">Privacy</a>
        <a class="muted small" href="<?= $BASE_PATH ?>/contact.php">Contact</a>
      </div>
    </div>
  </div>
</footer>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // expose base path to JS (safely encoded)
  const BASE_PATH = <?= json_encode($BASE_PATH); ?>;

  // Simple fade-up entrance
  document.addEventListener('DOMContentLoaded', function(){
    const els = document.querySelectorAll('.fade-up');
    els.forEach((el, i) => {
      const delay = parseInt(el.dataset.delay || 60) + (i * 40);
      setTimeout(() => el.classList.add('in'), delay);
    });

    // make hero slightly hover-tilt on pointer move (subtle)
    const hero = document.getElementById('heroCard');
    if (hero) {
      hero.addEventListener('mousemove', function(e){
        const rect = hero.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        hero.style.transform = `perspective(600px) rotateX(${ -y * 2 }deg) rotateY(${ x * 3 }deg)`;
      });
      hero.addEventListener('mouseleave', function(){ hero.style.transform = 'none'; });
    }

    // smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(a=>{
      a.addEventListener('click', function(e){
        e.preventDefault();
        const t = document.querySelector(this.getAttribute('href'));
        if (t) t.scrollIntoView({behavior:'smooth', block:'start'});
      });
    });
  });
</script>
</body>
</html>
