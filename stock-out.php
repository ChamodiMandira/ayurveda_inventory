<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Add Stock Out (PRESERVED EXACTLY)
if (isset($_POST['add_stock_out'])) {
    $item_id   = (int)$_POST['item_id'];
    $quantity  = (float)$_POST['quantity'];
    $reason    = trim($_POST['reason']);
    $notes     = trim($_POST['notes']);
    $issued_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO stock_out (item_id, quantity, reason, notes, issued_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idssi", $item_id, $quantity, $reason, $notes, $issued_by);
    $stmt->execute();
    header("Location: stock-out.php?success=1");
    exit();
}

// Queries (PRESERVED EXACTLY)
$records = $conn->query("
    SELECT so.*, i.name as item_name, i.unit, u.full_name
    FROM stock_out so
    JOIN items i ON so.item_id = i.id
    LEFT JOIN users u ON so.issued_by = u.id
    ORDER BY so.issued_at DESC
");

$items = $conn->query("SELECT id, name, unit FROM items ORDER BY name");

// User initials
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
    <title>Stock Out — Disanayaka Ayurveda</title>
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
            <a href="stock-in.php" class="nav-link"><i class="bi bi-box-arrow-in-down"></i><span>Stock In</span></a>
            <a href="stock-out.php" class="nav-link active"><i class="bi bi-box-arrow-up"></i><span>Stock Out</span></a>
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
            <div class="topbar-page-title">Stock Out</div>
            <div class="topbar-sub">Issue medicines to wards, OPD &amp; departments</div>
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
            <i class="bi bi-check-circle-fill"></i> Stock issued and recorded successfully!
        </div>
        <?php endif; ?>

        <div class="section-header">
            <div class="section-title-group">
                <div class="section-title">Stock Out Records</div>
                <div class="section-sub">All issued medicines and supplies</div>
            </div>
            <div class="section-actions">
                <button class="btn-ayur-red" data-bs-toggle="modal" data-bs-target="#issueStockModal">
                    <i class="bi bi-arrow-up-circle"></i> Issue Stock
                </button>
            </div>
        </div>

        <div class="card-panel">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date Issued</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Issued By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $records->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size:0.82rem; white-space:nowrap; color:var(--text-muted);">
                                <i class="bi bi-calendar3 me-1" style="font-size:0.72rem;"></i>
                                <?= date('d M Y', strtotime($row['issued_at'])) ?>
                                <br>
                                <span style="font-size:0.72rem; color:var(--text-light);"><?= date('h:i A', strtotime($row['issued_at'])) ?></span>
                            </td>
                            <td class="col-name"><?= htmlspecialchars($row['item_name']) ?></td>
                            <td>
                                <span style="font-weight:700; color:var(--red-text); font-size:0.95rem;">
                                    <?= number_format($row['quantity'], 1) ?>
                                </span>
                                <span style="color:var(--text-muted); font-size:0.8rem;"> <?= htmlspecialchars($row['unit']) ?></span>
                            </td>
                            <td>
                                <?php
                                $reason = $row['reason'] ?? '';
                                $reason_badges = [
                                    'Patient'    => ['class' => 'badge-blue',   'icon' => 'bi-person-heart'],
                                    'Department' => ['class' => 'badge-purple', 'icon' => 'bi-hospital'],
                                    'OPD'        => ['class' => 'badge-teal',   'icon' => 'bi-clipboard2-pulse'],
                                    'Damage'     => ['class' => 'badge-red',    'icon' => 'bi-exclamation-octagon'],
                                    'Other'      => ['class' => 'badge-gray',   'icon' => 'bi-three-dots'],
                                ];
                                $rb = $reason_badges[$reason] ?? ['class' => 'badge-gray', 'icon' => 'bi-tag'];
                                ?>
                                <span class="badge-pill <?= $rb['class'] ?>">
                                    <i class="bi <?= $rb['icon'] ?>"></i>
                                    <?= htmlspecialchars($reason ?: '—') ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($row['full_name'] ?? '—') ?></td>
                            <td style="color:var(--text-muted); font-size:0.82rem; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?= htmlspecialchars($row['notes'] ?? '—') ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($records->num_rows == 0): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-box-arrow-up empty-state-icon"></i>
                                    <div class="empty-state-title">No stock out records yet</div>
                                    <div class="empty-state-desc">Use <strong>Issue Stock</strong> to record medicine issues.</div>
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

<!-- ISSUE STOCK MODAL -->
<div class="modal fade" id="issueStockModal" tabindex="-1" aria-labelledby="issueStockLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="issueStockLabel">
                    <i class="bi bi-box-arrow-up me-2" style="color:#dc2626;"></i>
                    Issue Stock (Stock Out)
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
                    <label class="form-label">Quantity <span style="color:var(--red-text)">*</span></label>
                    <input type="number" step="0.01" name="quantity" class="form-control" placeholder="0.00" required min="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason <span style="color:var(--red-text)">*</span></label>
                    <select name="reason" class="form-select" required>
                        <option value="">— Select Reason —</option>
                        <option value="Patient">Patient / Prescription</option>
                        <option value="Department">Department / Ward</option>
                        <option value="OPD">OPD</option>
                        <option value="Damage">Damage / Expired</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional remarks or patient reference…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px; font-size:0.85rem; font-weight:600;">Cancel</button>
                <button type="submit" name="add_stock_out" class="btn-ayur-red">
                    <i class="bi bi-check2"></i> Issue Stock
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