<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// --- Existing queries (PRESERVED EXACTLY) ---
$total_items = $conn->query("SELECT COUNT(*) as total FROM items")->fetch_assoc()['total'];

$low_stock_q = $conn->query("
    SELECT i.name, i.unit, i.min_stock,
           COALESCE((SELECT SUM(quantity) FROM stock_in WHERE item_id = i.id), 0) - 
           COALESCE((SELECT SUM(quantity) FROM stock_out WHERE item_id = i.id), 0) as current_stock
    FROM items i
    HAVING current_stock < min_stock
    ORDER BY current_stock ASC
    LIMIT 8
");
$low_stock_count = $low_stock_q->num_rows;

$expiring = $conn->query("
    SELECT COUNT(DISTINCT item_id) as total 
    FROM stock_in 
    WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['total'];

$recent = $conn->query("
    (SELECT 'in' as type, si.received_at as date, i.name, si.quantity, u.full_name, si.notes
     FROM stock_in si 
     JOIN items i ON si.item_id = i.id 
     LEFT JOIN users u ON si.received_by = u.id
     ORDER BY si.received_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'out' as type, so.issued_at as date, i.name, so.quantity, u.full_name, so.notes
     FROM stock_out so 
     JOIN items i ON so.item_id = i.id 
     LEFT JOIN users u ON so.issued_by = u.id
     ORDER BY so.issued_at DESC LIMIT 5)
    ORDER BY date DESC LIMIT 8
");

$cat_data = $conn->query("
    SELECT c.name, COUNT(i.id) as total
    FROM categories c
    LEFT JOIN items i ON c.id = i.category_id
    GROUP BY c.id
");
$cat_labels = [];
$cat_values = [];
while($row = $cat_data->fetch_assoc()) {
    $cat_labels[] = $row['name'];
    $cat_values[] = $row['total'];
}

// --- Additional stats queries (additive, no change to existing logic) ---
$total_suppliers  = $conn->query("SELECT COUNT(*) as total FROM suppliers")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$stock_in_month   = $conn->query("SELECT COALESCE(SUM(quantity),0) as total FROM stock_in WHERE MONTH(received_at)=MONTH(CURDATE()) AND YEAR(received_at)=YEAR(CURDATE())")->fetch_assoc()['total'];
$stock_out_month  = $conn->query("SELECT COALESCE(SUM(quantity),0) as total FROM stock_out WHERE MONTH(issued_at)=MONTH(CURDATE()) AND YEAR(issued_at)=YEAR(CURDATE())")->fetch_assoc()['total'];

// --- User avatar initials ---
$initials = '';
foreach (explode(' ', trim($_SESSION['full_name'])) as $w) {
    if ($w) { $initials .= strtoupper($w[0]); if (strlen($initials) >= 2) break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Disanayaka Ayurveda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- ======= SIDEBAR ======= -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="white">
                <path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/>
            </svg>
        </div>
        <div class="brand-text-wrap">
            <span class="brand-name">Disanayaka</span>
            <span class="brand-sub">Ayurveda Hospital</span>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Main Menu</div>
        <div class="sidebar-nav-group">
            <a href="dashboard.php" class="nav-link active">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Inventory</div>
        <div class="sidebar-nav-group">
            <a href="items.php" class="nav-link">
                <i class="bi bi-box-seam"></i><span>Items</span>
            </a>
            <a href="stock-in.php" class="nav-link">
                <i class="bi bi-box-arrow-in-down"></i><span>Stock In</span>
            </a>
            <a href="stock-out.php" class="nav-link">
                <i class="bi bi-box-arrow-up"></i><span>Stock Out</span>
            </a>
            <a href="suppliers.php" class="nav-link">
                <i class="bi bi-truck"></i><span>Suppliers</span>
            </a>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Management</div>
        <div class="sidebar-nav-group">
            <?php if($_SESSION['role'] === 'admin'): ?>
            <a href="users.php" class="nav-link">
                <i class="bi bi-people"></i><span>Users &amp; Staff</span>
            </a>
            <?php endif; ?>
            <a href="reports.php" class="nav-link">
                <i class="bi bi-file-earmark-bar-graph"></i><span>Reports</span>
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-user-card">
            <div class="sidebar-user-avatar"><?= $initials ?></div>
            <div style="min-width:0">
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div class="sidebar-user-role"><?= htmlspecialchars($_SESSION['role']) ?></div>
            </div>
        </div>
        <a href="logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ======= MAIN WRAPPER ======= -->
<div class="main-wrapper">

    <!-- Topbar -->
    <header class="topbar">
        <button class="sidebar-toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="topbar-breadcrumb">
            <div class="topbar-page-title">Dashboard</div>
            <div class="topbar-sub">Inventory overview &amp; quick stats</div>
        </div>
        <div class="topbar-right">
            <div class="topbar-datetime">
                <span class="topbar-date-text"><?= date('D, d M Y') ?></span>
                <span class="topbar-time-text"><?= date('h:i A') ?></span>
            </div>
            <div class="topbar-divider"></div>
            <div class="topbar-user">
                <div class="topbar-avatar"><?= $initials ?></div>
                <div class="topbar-user-info">
                    <span class="topbar-user-name"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
                    <span class="topbar-user-role"><?= htmlspecialchars($_SESSION['role']) ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">

        <!-- ===== STAT CARDS ===== -->
        <div class="row g-3 mb-4">
            <!-- Total Items -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card green">
                    <div class="stat-icon-wrap green">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Total Items</div>
                        <div class="stat-value"><?= $total_items ?></div>
                        <div class="stat-note ok"><?= $total_categories ?> categories</div>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card red">
                    <div class="stat-icon-wrap red">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Low Stock</div>
                        <div class="stat-value"><?= $low_stock_count ?></div>
                        <div class="stat-note <?= $low_stock_count > 0 ? 'alert' : 'ok' ?>">
                            <?= $low_stock_count > 0 ? 'Items need reorder' : 'All well stocked' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card amber">
                    <div class="stat-icon-wrap amber">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Expiring Soon</div>
                        <div class="stat-value"><?= $expiring ?></div>
                        <div class="stat-note warn">Within 30 days</div>
                    </div>
                </div>
            </div>

            <!-- Suppliers -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card blue">
                    <div class="stat-icon-wrap blue">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Suppliers</div>
                        <div class="stat-value"><?= $total_suppliers ?></div>
                        <div class="stat-note">Active suppliers</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Month quick summary strip ===== -->
        <div class="card-panel mb-4 no-print" style="border-left: 4px solid var(--accent);">
            <div class="card-panel-body" style="padding: 14px 22px;">
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em;">
                        <i class="bi bi-calendar3 me-1"></i> <?= date('F Y') ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-pill badge-green"><i class="bi bi-arrow-down-circle-fill"></i> Stock In</span>
                        <span style="font-weight:800; color:var(--green-text); font-size:0.95rem;"><?= number_format($stock_in_month, 0) ?> units</span>
                    </div>
                    <div style="width:1px; height:20px; background:var(--border);"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-pill badge-red"><i class="bi bi-arrow-up-circle-fill"></i> Stock Out</span>
                        <span style="font-weight:800; color:var(--red-text); font-size:0.95rem;"><?= number_format($stock_out_month, 0) ?> units</span>
                    </div>
                    <div style="width:1px; height:20px; background:var(--border);"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:0.78rem; color:var(--text-muted);">Net this month:</span>
                        <?php $net = $stock_in_month - $stock_out_month; ?>
                        <span style="font-weight:800; color:<?= $net >= 0 ? 'var(--green-text)' : 'var(--red-text)' ?>; font-size:0.95rem;">
                            <?= ($net >= 0 ? '+' : '') . number_format($net, 0) ?> units
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Charts Row ===== -->
        <div class="row g-3 mb-4">
            <!-- Category Doughnut -->
            <div class="col-lg-5">
                <div class="card-panel h-100">
                    <div class="card-panel-header">
                        <div class="card-panel-title">
                            <i class="bi bi-pie-chart-fill"></i>
                            Items by Category
                        </div>
                    </div>
                    <div class="card-panel-body">
                        <div class="chart-wrap">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-lg-7">
                <div class="card-panel h-100">
                    <div class="card-panel-header">
                        <div class="card-panel-title" style="color:var(--red-text);">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Low Stock Alerts
                        </div>
                        <a href="items.php" class="btn-ayur-outline" style="font-size:0.75rem; padding:6px 12px;">
                            <i class="bi bi-eye"></i> View All
                        </a>
                    </div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Current</th>
                                    <th>Min Required</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($low_stock_count > 0): ?>
                                    <?php while($row = $low_stock_q->fetch_assoc()): ?>
                                    <?php
                                        $pct = ($row['min_stock'] > 0)
                                            ? min(100, round(($row['current_stock'] / $row['min_stock']) * 100))
                                            : 0;
                                        $pct = max(0, $pct);
                                        $bar_class = ($row['current_stock'] <= 0) ? 'out' : 'low';
                                    ?>
                                    <tr>
                                        <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
                                        <td>
                                            <span style="font-weight:700; color:var(--red-text);">
                                                <?= number_format($row['current_stock'], 1) ?> <?= htmlspecialchars($row['unit']) ?>
                                            </span>
                                            <div class="stock-progress">
                                                <div class="stock-progress-fill <?= $bar_class ?>" style="width:<?= $pct ?>%"></div>
                                            </div>
                                        </td>
                                        <td style="color:var(--text-muted);"><?= $row['min_stock'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                                        <td>
                                            <?php if($row['current_stock'] <= 0): ?>
                                                <span class="badge-pill badge-out-stock"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
                                            <?php else: ?>
                                                <span class="badge-pill badge-low-stock"><i class="bi bi-dash-circle-fill"></i> Low Stock</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state" style="padding:30px 20px;">
                                            <i class="bi bi-check-circle-fill empty-state-icon" style="color:var(--accent);"></i>
                                            <div class="empty-state-title" style="color:var(--green-text);">All items are well stocked!</div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Recent Activity ===== -->
        <div class="card-panel">
            <div class="card-panel-header">
                <div class="card-panel-title">
                    <i class="bi bi-activity"></i>
                    Recent Stock Movements
                </div>
                <div style="font-size:0.75rem; color:var(--text-light);">Last 8 transactions</div>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Handled By</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($recent && $recent->num_rows > 0): ?>
                            <?php while($row = $recent->fetch_assoc()): ?>
                            <tr>
                                <td style="color:var(--text-muted); font-size:0.82rem; white-space:nowrap;">
                                    <i class="bi bi-clock me-1" style="font-size:0.72rem;"></i>
                                    <?= date('d M Y', strtotime($row['date'])) ?>
                                    <br>
                                    <span style="font-size:0.72rem; color:var(--text-light);"><?= date('h:i A', strtotime($row['date'])) ?></span>
                                </td>
                                <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <?php if($row['type'] == 'in'): ?>
                                        <span class="badge-pill badge-stock-in">
                                            <i class="bi bi-arrow-down-circle-fill"></i> Stock In
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-pill badge-stock-out">
                                            <i class="bi bi-arrow-up-circle-fill"></i> Stock Out
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight:700;"><?= $row['quantity'] ?></td>
                                <td style="color:var(--text-muted);"><?= htmlspecialchars($row['full_name'] ?? '—') ?></td>
                                <td style="color:var(--text-muted); font-size:0.82rem; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?= htmlspecialchars($row['notes'] ?? '—') ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-inbox empty-state-icon"></i>
                                    <div class="empty-state-title">No stock movements yet</div>
                                    <div class="empty-state-desc">Stock in and stock out transactions will appear here.</div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div><!-- /.main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

// Category Doughnut Chart (Ayurveda palette)
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($cat_labels) ?>,
        datasets: [{
            data: <?= json_encode($cat_values) ?>,
            backgroundColor: [
                '#1a4731', '#52b788', '#c9a84c', '#40916c',
                '#2d6a4f', '#95d5b2', '#d4a017', '#74c69d'
            ],
            borderWidth: 2,
            borderColor: '#ffffff',
            hoverBorderColor: '#ffffff',
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 18,
                    usePointStyle: true,
                    pointStyleWidth: 10,
                    font: { family: 'Inter', size: 12, weight: '600' },
                    color: '#374151'
                }
            },
            tooltip: {
                backgroundColor: '#1a4731',
                titleFont: { family: 'Inter', weight: '700' },
                bodyFont:  { family: 'Inter' },
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: function(ctx) {
                        return ' ' + ctx.label + ': ' + ctx.raw + ' items';
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>