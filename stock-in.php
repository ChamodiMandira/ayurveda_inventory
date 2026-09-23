<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Add Stock In (PRESERVED EXACTLY)
if (isset($_POST['add_stock'])) {
    $item_id     = (int)$_POST['item_id'];
    $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $quantity    = (float)$_POST['quantity'];
    $batch_no    = trim($_POST['batch_no']);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $unit_price  = (float)$_POST['unit_price'];
    $notes       = trim($_POST['notes']);
    $received_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO stock_in (item_id, supplier_id, quantity, batch_no, expiry_date, unit_price, notes, received_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidssdsi", $item_id, $supplier_id, $quantity, $batch_no, $expiry_date, $unit_price, $notes, $received_by);
    $stmt->execute();
    header("Location: stock-in.php?success=1");
    exit();
}

// Queries (PRESERVED EXACTLY)
$records = $conn->query("
    SELECT si.*, i.name as item_name, i.unit, s.name as supplier_name, u.full_name
    FROM stock_in si
    JOIN items i ON si.item_id = i.id
    LEFT JOIN suppliers s ON si.supplier_id = s.id
    LEFT JOIN users u ON si.received_by = u.id
    ORDER BY si.received_at DESC
");

$items     = $conn->query("SELECT id, name, unit FROM items ORDER BY name");
$suppliers = $conn->query("SELECT id, name FROM suppliers ORDER BY name");

// User initials
$initials = '';
foreach (explode(' ', trim($_SESSION['full_name'])) as $w) {
    if ($w) { $initials .= strtoupper($w[0]); if (strlen($initials) >= 2) break; }
}

$today = date('Y-m-d');
$soon  = date('Y-m-d', strtotime('+30 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In — Disanayaka Ayurveda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
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
            <a href="stock-in.php" class="nav-link active"><i class="bi bi-box-arrow-in-down"></i><span>Stock In</span></a>
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
            <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i><span>Reports</span></a>
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
    <header class="topbar">
        <button class="sidebar-toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="topbar-breadcrumb">
            <div class="topbar-page-title">Stock In</div>
            <div class="topbar-sub">Record received medicine &amp; herbal stock</div>
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

    <main class="page-content">

        <?php if(isset($_GET['success'])): ?>
        <div class="alert-ayur alert-ayur-success">
            <i class="bi bi-check-circle-fill"></i> Stock received and recorded successfully!
        </div>
        <?php endif; ?>

        <div class="section-header">
            <div class="section-title-group">
                <div class="section-title">Stock In Records</div>
                <div class="section-sub">All received medicine and herbal supply records</div>
            </div>
            <div class="section-actions">
                <button class="btn-ayur-accent" data-bs-toggle="modal" data-bs-target="#addStockModal">
                    <i class="bi bi-plus-lg"></i> Add Stock
                </button>
            </div>
        </div>

        <div class="card-panel">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date Received</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Batch No</th>
                            <th>Expiry Date</th>
                            <th>Supplier</th>
                            <th>Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $records->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size:0.82rem; white-space:nowrap; color:var(--text-muted);">
                                <i class="bi bi-calendar3 me-1" style="font-size:0.72rem;"></i>
                                <?= date('d M Y', strtotime($row['received_at'])) ?>
                                <br>
                                <span style="font-size:0.72rem; color:var(--text-light);"><?= date('h:i A', strtotime($row['received_at'])) ?></span>
                            </td>
                            <td class="col-name"><?= htmlspecialchars($row['item_name']) ?></td>
                            <td>
                                <span style="font-weight:700; color:var(--green-text); font-size:0.95rem;">
                                    <?= number_format($row['quantity'], 1) ?>
                                </span>
                                <span style="color:var(--text-muted); font-size:0.8rem;"> <?= htmlspecialchars($row['unit']) ?></span>
                            </td>
                            <td>
                                <?php if($row['batch_no']): ?>
                                    <span class="badge-pill badge-gray">
                                        <i class="bi bi-tag"></i>
                                        <?= htmlspecialchars($row['batch_no']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:var(--text-light);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['expiry_date']): ?>
                                    <?php
                                        $exp = $row['expiry_date'];
                                        $daysLeft = (int)((strtotime($exp) - strtotime($today)) / 86400);
                                    ?>
                                    <?php if($daysLeft <= 0): ?>
                                        <span class="badge-pill badge-red">
                                            <i class="bi bi-exclamation-circle-fill"></i> Expired
                                        </span>
                                    <?php elseif($daysLeft <= 30): ?>
                                        <span class="badge-pill badge-amber">
                                            <i class="bi bi-clock-fill"></i> <?= $exp ?> (<?= $daysLeft ?>d)
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-size:0.82rem;"><?= $exp ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:var(--text-light);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-muted);">
                                <?= htmlspecialchars($row['supplier_name'] ?? '—') ?>
                            </td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($row['full_name'] ?? '—') ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($records->num_rows == 0): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-box-arrow-in-down empty-state-icon"></i>
                                    <div class="empty-state-title">No stock in records yet</div>
                                    <div class="empty-state-desc">Click <strong>Add Stock</strong> to record your first stock receipt.</div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- ADD STOCK MODAL -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockLabel">
                    <i class="bi bi-box-arrow-in-down me-2" style="color:var(--accent);"></i>
                    Add Stock In
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Item <span style="color:var(--red-text)">*</span></label>
                    <select name="item_id" class="form-select" required>
                        <option value="">— Select Item —</option>
                        <?php while($item = $items->fetch_assoc()): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?> (<?= $item['unit'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">— Select Supplier (optional) —</option>
                        <?php while($sup = $suppliers->fetch_assoc()): ?>
                            <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Quantity <span style="color:var(--red-text)">*</span></label>
                        <input type="number" step="0.01" name="quantity" class="form-control" placeholder="0.00" required min="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Unit Price (Rs.)</label>
                        <input type="number" step="0.01" name="unit_price" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="row g-3 mt-0">
                    <div class="col-6">
                        <label class="form-label">Batch No</label>
                        <input type="text" name="batch_no" class="form-control" placeholder="e.g. BT-2024-01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional remarks…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px; font-size:0.85rem; font-weight:600;">Cancel</button>
                <button type="submit" name="add_stock" class="btn-ayur-accent">
                    <i class="bi bi-check2"></i> Save Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
</body>
</html>