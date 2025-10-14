<?php
// index.php - Dashboard (standalone, Bootstrap5, responsive, animated, interactive)
session_start();

// Compute a robust base path for building links.
// This will be the directory of this script (e.g. "/my_works/freelance_manager") or '' if at web root.
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$BASE_PATH = $base_dir === '/' ? '' : $base_dir; // will be '' for root, or '/my_works/freelance_manager'

// If user not logged in, redirect to login using the computed base path.
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $BASE_PATH . '/login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$uid = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

function get_currency_symbol($code) {
    $map = ['NGN'=>'₦','USD'=>'$','EUR'=>'€','GBP'=>'£'];
    return $map[$code] ?? $code;
}

// Helpers: safe fetch single int
function fetch_count($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['cnt'] ?? 0);
}

// Counts
$clients_count = fetch_count($conn,
    "SELECT COUNT(*) AS cnt FROM clients WHERE user_id = ?",
    'i', [$uid]);

$projects_count = fetch_count($conn,
    "SELECT COUNT(*) AS cnt FROM projects p JOIN clients c ON p.client_id = c.id WHERE c.user_id = ?",
    'i', [$uid]);

$invoices_count = fetch_count($conn,
    "SELECT COUNT(*) AS cnt FROM invoices i JOIN projects p ON i.project_id = p.id JOIN clients c ON p.client_id = c.id WHERE c.user_id = ?",
    'i', [$uid]);

// Totals: total invoiced and total paid (aggregate by this user's invoices)
$stmt = $conn->prepare("
    SELECT IFNULL(SUM(i.amount),0) AS total_invoiced
    FROM invoices i
    JOIN projects p ON i.project_id = p.id
    JOIN clients c ON p.client_id = c.id
    WHERE c.user_id = ?
");
$stmt->bind_param('i', $uid);
$stmt->execute();
$total_invoiced = (float) $stmt->get_result()->fetch_assoc()['total_invoiced'] ?? 0.0;
$stmt->close();

$stmt = $conn->prepare("
    SELECT IFNULL(SUM(pay.amount),0) AS total_paid
    FROM payments pay
    JOIN invoices i ON pay.invoice_id = i.id
    JOIN projects p ON i.project_id = p.id
    JOIN clients c ON p.client_id = c.id
    WHERE c.user_id = ?
");
$stmt->bind_param('i', $uid);
$stmt->execute();
$total_paid = (float) $stmt->get_result()->fetch_assoc()['total_paid'] ?? 0.0;
$stmt->close();

// Recent invoices (limit 6)
$stmt = $conn->prepare("
  SELECT i.id, i.invoice_number, i.amount, i.currency, i.status, i.created_at, p.title AS project_title, c.name AS client_name
  FROM invoices i
  JOIN projects p ON i.project_id = p.id
  JOIN clients c ON p.client_id = c.id
  WHERE c.user_id = ?
  ORDER BY i.created_at DESC
  LIMIT 6
");
$stmt->bind_param('i', $uid);
$stmt->execute();
$recent_invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recent payments (limit 6)
$stmt = $conn->prepare("
  SELECT pay.id, pay.amount, pay.currency, pay.method, pay.transaction_ref, pay.paid_at,
         i.id AS invoice_id, p.title AS project_title, c.name AS client_name
  FROM payments pay
  JOIN invoices i ON pay.invoice_id = i.id
  JOIN projects p ON i.project_id = p.id
  JOIN clients c ON p.client_id = c.id
  WHERE c.user_id = ?
  ORDER BY pay.paid_at DESC
  LIMIT 6
");
$stmt->bind_param('i', $uid);
$stmt->execute();
$recent_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard — Freelance Manager</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{--bg:#f4f7fb;--card:#fff;--muted:#6c757d;--brand:#0d6efd;--accent:#0b5ed7;--radius:12px}
    body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial;background:var(--bg);margin:0;color:#0b1a2b}
    .topbar{background:#fff;box-shadow:0 6px 18px rgba(11,24,40,0.04)}
    .brand {font-weight:700;color:var(--brand)}
    .panel{background:var(--card);border-radius:12px;padding:18px;box-shadow:0 10px 30px rgba(11,24,40,0.04)}
    .stat {font-weight:700;font-size:1.6rem}
    .muted{color:var(--muted)}
    .small-muted{font-size:.9rem;color:var(--muted)}
    .fade-up{opacity:0;transform:translateY(10px);transition:all .48s cubic-bezier(.2,.9,.3,1)}
    .fade-up.in{opacity:1;transform:none}
    .action-btn{border-radius:10px}
    .table-modern th{background:transparent;border-bottom:1px solid rgba(0,0,0,0.06)}
    @media (max-width:900px){ .sidebar { display:none } .main {padding:16px} }
  </style>
</head>
<body>

<!-- Topbar -->
<nav class="topbar navbar navbar-expand-lg navbar-light sticky-top">
  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $BASE_PATH ?>/index.php">
      <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(135deg,var(--brand),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff">
        <i class="bi bi-briefcase-fill"></i>
      </div>
      <div class="brand">FreelanceManager</div>
    </a>

    <div class="ms-auto d-flex align-items-center gap-2">
      <div class="input-group d-none d-md-flex" style="min-width:280px;">
        <input id="globalSearch" class="form-control" placeholder="Search invoices, clients, projects...">
        <button id="searchBtn" class="btn btn-primary action-btn"><i class="bi bi-search"></i></button>
      </div>

      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown"><i class="bi bi-plus-lg"></i> New</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= $BASE_PATH ?>/invoices/add.php">New Invoice</a></li>
          <li><a class="dropdown-item" href="<?= $BASE_PATH ?>/projects/add.php">New Project</a></li>
          <li><a class="dropdown-item" href="<?= $BASE_PATH ?>/clients/add.php">New Client</a></li>
        </ul>
      </div>

      <div class="ms-2">
        <a class="btn btn-light btn-sm" href="<?= $BASE_PATH ?>/logout.php"><i class="bi bi-box-arrow-right"></i></a>
      </div>
    </div>
  </div>
</nav>

<!-- Page body -->
<div class="container-fluid" style="padding:22px;">
  <div class="row gx-4">
    <!-- left column -->
    <div class="col-xl-9">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <h4 style="margin:0">Welcome back, <?php echo htmlspecialchars($user_name); ?></h4>
          <div class="small-muted">Overview of your account</div>
        </div>
        <div>
          <a class="btn btn-outline-secondary action-btn me-2" href="<?= $BASE_PATH ?>/clients/list.php">Clients</a>
          <a class="btn btn-primary action-btn" href="<?= $BASE_PATH ?>/invoices/add.php"><i class="bi bi-plus-lg"></i> New Invoice</a>
        </div>
      </div>

      <!-- stats & chart -->
      <div class="row g-3">
        <div class="col-md-3 fade-up" data-delay="60">
          <div class="panel text-center">
            <div class="small-muted">Clients</div>
            <div class="stat"><?php echo (int)$clients_count; ?></div>
            <div class="small-muted mt-1"><a href="<?= $BASE_PATH ?>/clients/list.php">View clients</a></div>
          </div>
        </div>

        <div class="col-md-3 fade-up" data-delay="120">
          <div class="panel text-center">
            <div class="small-muted">Projects</div>
            <div class="stat"><?php echo (int)$projects_count; ?></div>
            <div class="small-muted mt-1"><a href="<?= $BASE_PATH ?>/projects/list.php">View projects</a></div>
          </div>
        </div>

        <div class="col-md-3 fade-up" data-delay="180">
          <div class="panel text-center">
            <div class="small-muted">Invoices</div>
            <div class="stat"><?php echo (int)$invoices_count; ?></div>
            <div class="small-muted mt-1"><a href="<?= $BASE_PATH ?>/invoices/list.php">View invoices</a></div>
          </div>
        </div>

        <div class="col-md-3 fade-up" data-delay="240">
          <div class="panel text-center">
            <div class="small-muted">Finance</div>
            <div class="stat"><?php echo '$' . number_format($total_invoiced,2); // default display in USD-like; you can change ?></div>
            <div class="small-muted mt-1">Paid: <?php echo '$' . number_format($total_paid,2); ?></div>
          </div>
        </div>

        <div class="col-12 mt-3 fade-up" data-delay="300">
          <div class="panel d-flex gap-3 align-items-center">
            <div style="flex:1">
              <div class="small-muted">Quick insights</div>
              <div class="fw-bold">Recent activity and outstanding items</div>
            </div>
            <div style="width:260px">
              <canvas id="countsChart" width="260" height="100" aria-label="Counts chart" role="img"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- recent invoices -->
      <div class="mt-4 fade-up" data-delay="360">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Recent invoices</h5>
          <div>
            <select id="invoiceStatusFilter" class="form-select form-select-sm">
              <option value="">All statuses</option>
              <option value="unpaid">Unpaid</option>
              <option value="partially_paid">Partially paid</option>
              <option value="paid">Paid</option>
            </select>
          </div>
        </div>

        <div class="panel">
          <?php if (empty($recent_invoices)): ?>
            <div class="small-muted">No invoices yet. <a href="<?= $BASE_PATH ?>/invoices/add.php">Create your first invoice</a>.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table id="invoicesTable" class="table table-modern table-sm mb-0">
                <thead>
                  <tr><th>#</th><th>Client</th><th>Project</th><th class="text-end">Amount</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recent_invoices as $inv): ?>
                  <tr data-status="<?php echo htmlspecialchars($inv['status']); ?>">
                    <td><?php echo htmlspecialchars($inv['invoice_number'] ?: ('#' . (int)$inv['id'])); ?></td>
                    <td><?php echo htmlspecialchars($inv['client_name']); ?></td>
                    <td><?php echo htmlspecialchars($inv['project_title']); ?></td>
                    <td class="text-end"><?php echo htmlspecialchars(get_currency_symbol($inv['currency'])) . ' ' . number_format($inv['amount'],2); ?></td>
                    <td><span class="badge <?php echo $inv['status']==='paid' ? 'bg-success':'bg-warning text-dark'; ?>"><?php echo htmlspecialchars(ucwords(str_replace('_',' ',$inv['status']))); ?></span></td>
                    <td><?php echo htmlspecialchars($inv['created_at']); ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= $BASE_PATH ?>/invoices/view.php?id=<?php echo (int)$inv['id']; ?>">View</a></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- recent payments -->
      <div class="mt-4 fade-up" data-delay="420">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Recent payments</h5>
          <div class="small-muted">Latest recorded payments</div>
        </div>

        <div class="panel">
          <?php if (empty($recent_payments)): ?>
            <div class="small-muted">No payments recorded yet.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-sm mb-0">
                <thead><tr><th>ID</th><th>Invoice</th><th>Client</th><th class="text-end">Amount</th><th>Method</th><th>Paid at</th></tr></thead>
                <tbody>
                <?php foreach ($recent_payments as $p): ?>
                  <tr>
                    <td><?php echo (int)$p['id']; ?></td>
                    <td><a href="<?= $BASE_PATH ?>/invoices/view.php?id=<?php echo (int)$p['invoice_id']; ?>">#<?php echo (int)$p['invoice_id']; ?></a></td>
                    <td><?php echo htmlspecialchars($p['client_name']); ?></td>
                    <td class="text-end"><?php echo htmlspecialchars(get_currency_symbol($p['currency'])) . ' ' . number_format($p['amount'],2); ?></td>
                    <td><?php echo htmlspecialchars($p['method']); ?></td>
                    <td><?php echo htmlspecialchars($p['paid_at']); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- right column (quick actions & shortcuts) -->
    <div class="col-xl-3">
      <div class="panel fade-up" data-delay="480">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Quick actions</strong>
        </div>
        <div class="d-grid gap-2">
          <a class="btn btn-primary" href="<?= $BASE_PATH ?>/invoices/add.php"><i class="bi bi-receipt me-2"></i> New Invoice</a>
          <a class="btn btn-outline-secondary" href="<?= $BASE_PATH ?>/clients/add.php"><i class="bi bi-people me-2"></i> New Client</a>
          <a class="btn btn-outline-secondary" href="<?= $BASE_PATH ?>/projects/add.php"><i class="bi bi-kanban me-2"></i> New Project</a>
          <a class="btn btn-light" href="<?= $BASE_PATH ?>/payments/list.php"><i class="bi bi-cash-stack me-2"></i> Transactions</a>
        </div>
      </div>

      <div class="panel mt-3 fade-up" data-delay="540">
        <div class="small-muted">Tips</div>
        <ul class="small mt-2">
          <li>Use itemized invoices for clear billing.</li>
          <li>Record payments immediately to avoid double entries.</li>
          <li>Attach the generated PDF when emailing invoices.</li>
        </ul>
      </div>
    </div>
  </div>

  <footer class="mt-4 small-muted text-center">© <?php echo date('Y'); ?> Freelance Manager • Built for freelancers</footer>
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // expose base path to JS (safely encoded)
  const BASE_PATH = <?php echo json_encode($BASE_PATH); ?>;

  // entrance animation
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.fade-up').forEach((el, i) => {
      const delay = parseInt(el.dataset.delay || 60) + i*40;
      setTimeout(()=> el.classList.add('in'), delay);
    });
  });

  // Search button -> go to invoices list with query
  document.getElementById('searchBtn')?.addEventListener('click', function(){
    const q = document.getElementById('globalSearch').value.trim();
    if (!q) return;
    window.location.href = BASE_PATH + '/invoices/list.php?search=' + encodeURIComponent(q);
  });

  // Invoice status filter
  document.getElementById('invoiceStatusFilter')?.addEventListener('change', function(){
    const val = this.value;
    document.querySelectorAll('#invoicesTable tbody tr').forEach(tr=>{
      if (!val) { tr.style.display = ''; return; }
      tr.style.display = (tr.dataset.status === val ? '' : 'none');
    });
  });

  // Small canvas chart (counts)
  (function(){
    const canvas = document.getElementById('countsChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const clients = <?php echo (int)$clients_count; ?>;
    const projects = <?php echo (int)$projects_count; ?>;
    const invoices = <?php echo (int)$invoices_count; ?>;
    const total = Math.max(clients, projects, invoices, 1);
    const w = canvas.width, h = canvas.height;
    ctx.clearRect(0,0,w,h);
    const labels = ['Clients','Projects','Invoices'];
    const values = [clients, projects, invoices];
    const colors = ['#0d6efd','#0b5ed7','#6f42c1'];
    const barW = Math.floor(w / (values.length*2));
    values.forEach((v,i)=>{
      const x = 20 + i * (barW*2);
      const vh = Math.round((v/total) * (h - 20));
      ctx.fillStyle = colors[i];
      // rounded bar
      const barX = x;
      const barY = h - vh - 12;
      const radius = 6;
      ctx.beginPath();
      ctx.moveTo(barX + radius, barY);
      ctx.arcTo(barX + barW, barY, barX + barW, barY + radius, radius);
      ctx.arcTo(barX + barW, barY + vh, barX + barW - radius, barY + vh, radius);
      ctx.arcTo(barX, barY + vh, barX, barY + vh - radius, radius);
      ctx.arcTo(barX, barY, barX + radius, barY, radius);
      ctx.closePath();
      ctx.fill();

      // label
      ctx.fillStyle = '#223';
      ctx.font = '12px Inter, system-ui, Arial';
      ctx.fillText(labels[i], barX, h - 2);
      ctx.fillStyle = '#444';
      ctx.font = 'bold 13px Inter, system-ui, Arial';
      ctx.fillText(v, barX, barY - 6);
    });
  })();
</script>
</body>
</html>
