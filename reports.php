<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// ====== All queries PRESERVED EXACTLY from original ======
$inventory = $conn->query("
    SELECT i.name, i.unit, c.name as category, i.min_stock,
           COALESCE((SELECT SUM(quantity) FROM stock_in WHERE item_id = i.id), 0) as total_in,
           COALESCE((SELECT SUM(quantity) FROM stock_out WHERE item_id = i.id), 0) as total_out,
           COALESCE((SELECT SUM(quantity) FROM stock_in WHERE item_id = i.id), 0) - 
           COALESCE((SELECT SUM(quantity) FROM stock_out WHERE item_id = i.id), 0) as current_stock
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    ORDER BY i.name
");

$low_stock = $conn->query("
    SELECT i.name, i.unit, c.name as category, i.min_stock,
           COALESCE((SELECT SUM(quantity) FROM stock_in WHERE item_id = i.id), 0) - 
           COALESCE((SELECT SUM(quantity) FROM stock_out WHERE item_id = i.id), 0) as current_stock
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    HAVING current_stock < min_stock
    ORDER BY current_stock ASC
");

$expiring = $conn->query("
    SELECT i.name, i.unit, si.batch_no, si.expiry_date, si.quantity,
           s.name as supplier
    FROM stock_in si
    JOIN items i ON si.item_id = i.id
    LEFT JOIN suppliers s ON si.supplier_id = s.id
    WHERE si.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY si.expiry_date ASC
");

// Additional counts for summary cards
$total_items = $conn->query("SELECT COUNT(*) as c FROM items")->fetch_assoc()['c'];
$low_count   = $conn->query("
    SELECT COUNT(*) as c FROM (
        SELECT i.id,
               COALESCE((SELECT SUM(quantity) FROM stock_in WHERE item_id = i.id), 0) -
               COALESCE((SELECT SUM(quantity) FROM stock_out WHERE item_id = i.id), 0) as cs,
               i.min_stock
        FROM items i
    ) t WHERE cs < min_stock
")->fetch_assoc()['c'];
$exp_count = $conn->query("
    SELECT COUNT(DISTINCT item_id) as c FROM stock_in
    WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['c'];

// User initials
$initials = '';
foreach (explode(' ', trim($_SESSION['full_name'])) as $w) {
    if ($w) { $initials .= strtoupper($w[0]); if (strlen($initials) >= 2) break; }
}
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — Disanayaka Ayurveda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Print-specific styles PRESERVED from original + enhanced */
        @media print {
            .sidebar, .topbar, .no-print, .sidebar-overlay { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            .page-content { padding: 0 !important; }
            body { background: white !important; font-size: 12px !important; }
            .print-header { display: block !important; }
            .report-section { border: 1px solid #ccc !important; box-shadow: none !important; margin-bottom: 16px !important; }
            .report-section-header { print-color-adjust: exact !important; -webkit-print-color-adjust: exact !important; }
            .data-table tbody tr:hover { background: none !important; }
            .badge-pill { border: 1px solid currentColor !important; }
            @page { margin: 1.5cm; size: A4; }
        }

        .print-header {
            display: none;
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #1a4731;
            margin-bottom: 20px;
        }
        .print-header h2 { color: #1a4731; font-size: 1.2rem; font-weight: 800; }
        .print-header p  { color: #555; font-size: 0.8rem; margin: 4px 0 0; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
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
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
        </div>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Inventory</div>
        <div class="sidebar-nav-group">
            <a href="items.php" class="nav-link"><i class="bi bi-box-seam"></i><span>Items</span></a>
            <a href="stock-in.php" class="nav-link"><i class="bi bi-box-arrow-in-down"></i><span>Stock In</span></a>
            <a href="stock-out.php" class="nav-link"><i class="bi bi-box-arrow-up"></i><span>Stock Out</span></a>
            <a href="suppliers.php" class="nav-link"><i class="bi bi-truck"></i><span>Suppliers</span></a>
        </div>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Management</div>
        <div class="sidebar-nav-group">
            <?php if($_SESSION['role'] === 'admin'): ?>
            <a href="users.php" class="nav-link"><i class="bi bi-people"></i><span>Users &amp; Staff</span></a>
            <?php endif; ?>
            <a href="reports.php" class="nav-link active"><i class="bi bi-file-earmark-bar-graph"></i><span>Reports</span></a>
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
        <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- MAIN WRAPPER -->
<div class="main-wrapper">
    <header class="topbar no-print">
        <button class="sidebar-toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="topbar-breadcrumb">
            <div class="topbar-page-title">Inventory Reports</div>
            <div class="topbar-sub">Complete stock status, alerts &amp; expiry report</div>
        </div>
        <div class="topbar-right">
            <div class="topbar-datetime">
                <span class="topbar-date-text"><?= date('D, d M Y') ?></span>
                <span class="topbar-time-text"><?= date('h:i A') ?></span>
            </div>
            <div class="topbar-divider"></div>
            <button class="btn-ayur no-print" onclick="window.print()">
                <i class="bi bi-printer-fill"></i> Print Report
            </button>
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

    <main class="page-content">

        <!-- Print header (only shows on print) -->
        <div class="print-header">
            <h2>Disanayaka Ayurveda Hospital</h2>
            <p>Inventory Management Report &mdash; Generated: <?= date('d F Y, h:i A') ?></p>
            <p>Prepared by: <?= htmlspecialchars($_SESSION['full_name']) ?></p>
        </div>

        <!-- Summary Cards (screen only) -->
        <div class="row g-3 mb-4 no-print">
            <div class="col-sm-4">
                <div class="stat-card green">
                    <div class="stat-icon-wrap green"><i class="bi bi-box-seam"></i></div>
                    <div class="stat-body">
                        <div class="stat-label">Total Items</div>
                        <div class="stat-value"><?= $total_items ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card red">
                    <div class="stat-icon-wrap red"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-body">
                        <div class="stat-label">Low / Out of Stock</div>
                        <div class="stat-value"><?= $low_count ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card amber">
                    <div class="stat-icon-wrap amber"><i class="bi bi-clock-history"></i></div>
                    <div class="stat-body">
                        <div class="stat-label">Expiring (30d)</div>
                        <div class="stat-value"><?= $exp_count ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== 1. COMPLETE INVENTORY ===== -->
        <div class="report-section">
            <div class="report-section-header success">
                <i class="bi bi-clipboard2-data-fill"></i>
                Complete Inventory Status
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Total In</th>
                            <th>Total Out</th>
                            <th>Current Stock</th>
                            <th>Min Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while($row = $inventory->fetch_assoc()): ?>
                        <tr>
                            <td class="col-num"><?= $i++ ?></td>
                            <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="badge-pill badge-teal"><?= htmlspecialchars($row['category'] ?? '—') ?></span></td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($row['unit']) ?></td>
                            <td style="color:var(--green-text); font-weight:700;"><?= number_format($row['total_in'], 1) ?></td>
                            <td style="color:var(--red-text); font-weight:700;"><?= number_format($row['total_out'], 1) ?></td>
                            <td style="font-weight:800; color:<?= $row['current_stock'] <= 0 ? 'var(--red-text)' : ($row['current_stock'] < $row['min_stock'] ? 'var(--amber-text)' : 'var(--green-text)') ?>;">
                                <?= number_format($row['current_stock'], 1) ?>
                            </td>
                            <td style="color:var(--text-muted);"><?= $row['min_stock'] ?></td>
                            <td>
                                <?php if($row['current_stock'] <= 0): ?>
                                    <span class="badge-pill badge-out-stock"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
                                <?php elseif($row['current_stock'] < $row['min_stock']): ?>
                                    <span class="badge-pill badge-low-stock"><i class="bi bi-dash-circle-fill"></i> Low Stock</span>
                                <?php else: ?>
                                    <span class="badge-pill badge-in-stock"><i class="bi bi-check-circle-fill"></i> In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== 2. LOW STOCK / OUT OF STOCK ===== -->
        <div class="report-section">
            <div class="report-section-header danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Low Stock &amp; Out of Stock Items
                <span class="badge-pill badge-red ms-2" style="font-size:0.68rem;"><?= $low_count ?> items</span>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Min Required</th>
                            <th>Deficit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; $has = false; while($row = $low_stock->fetch_assoc()): $has = true; ?>
                        <tr>
                            <td class="col-num"><?= $i++ ?></td>
                            <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="badge-pill badge-teal"><?= htmlspecialchars($row['category'] ?? '—') ?></span></td>
                            <td style="font-weight:700; color:var(--red-text);"><?= number_format($row['current_stock'], 1) ?> <?= htmlspecialchars($row['unit']) ?></td>
                            <td style="color:var(--text-muted);"><?= $row['min_stock'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                            <td style="font-weight:700; color:var(--amber-text);">
                                <?= number_format(max(0, $row['min_stock'] - $row['current_stock']), 1) ?>
                            </td>
                            <td>
                                <?php if($row['current_stock'] <= 0): ?>
                                    <span class="badge-pill badge-out-stock"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
                                <?php else: ?>
                                    <span class="badge-pill badge-low-stock"><i class="bi bi-dash-circle-fill"></i> Low Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(!$has): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state" style="padding:28px 16px;">
                                    <i class="bi bi-check-circle-fill empty-state-icon" style="color:var(--accent);"></i>
                                    <div class="empty-state-title" style="color:var(--green-text);">All items are adequately stocked!</div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== 3. EXPIRING SOON ===== -->
        <div class="report-section">
            <div class="report-section-header warning">
                <i class="bi bi-clock-fill"></i>
                Items Expiring Within 30 Days
                <span class="badge-pill badge-amber ms-2" style="font-size:0.68rem;"><?= $exp_count ?> items</span>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Batch No</th>
                            <th>Quantity</th>
                            <th>Supplier</th>
                            <th>Expiry Date</th>
                            <th>Days Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; $has2 = false; while($row = $expiring->fetch_assoc()): $has2 = true; ?>
                        <?php $days = (int)((strtotime($row['expiry_date']) - strtotime($today)) / 86400); ?>
                        <tr>
                            <td class="col-num"><?= $i++ ?></td>
                            <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <?php if($row['batch_no']): ?>
                                    <span class="badge-pill badge-gray"><i class="bi bi-tag"></i> <?= htmlspecialchars($row['batch_no']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--text-light);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:700;"><?= number_format($row['quantity'], 1) ?> <?= htmlspecialchars($row['unit']) ?></td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($row['supplier'] ?? '—') ?></td>
                            <td style="font-weight:600; color:var(--amber-text);"><?= $row['expiry_date'] ?></td>
                            <td>
                                <?php if($days <= 7): ?>
                                    <span class="badge-pill badge-red"><i class="bi bi-alarm-fill"></i> <?= $days ?>d left</span>
                                <?php else: ?>
                                    <span class="badge-pill badge-amber"><i class="bi bi-clock-fill"></i> <?= $days ?>d left</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(!$has2): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state" style="padding:28px 16px;">
                                    <i class="bi bi-check-circle-fill empty-state-icon" style="color:var(--accent);"></i>
                                    <div class="empty-state-title" style="color:var(--green-text);">No items expiring in the next 30 days!</div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Print footer note (screen) -->
        <div class="no-print" style="text-align:center; color:var(--text-light); font-size:0.76rem; padding:8px 0 16px;">
            <i class="bi bi-info-circle me-1"></i>
            Click <strong>Print Report</strong> to generate a print-ready A4 copy of all tables above.
        </div>

    </main>
</div><!-- /.main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
</body>
</html>