<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Add Supplier (PRESERVED EXACTLY)
if (isset($_POST['add_supplier'])) {
    $name    = trim($_POST['name']);
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $address);
    $stmt->execute();
    header("Location: suppliers.php?success=1");
    exit();
}

// Delete Supplier (PRESERVED EXACTLY)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM suppliers WHERE id = $id");
    header("Location: suppliers.php?deleted=1");
    exit();
}

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name");
$total = $suppliers->num_rows;

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
    <title>Suppliers — Disanayaka Ayurveda</title>
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
            <a href="stock-out.php" class="nav-link"><i class="bi bi-box-arrow-up"></i><span>Stock Out</span></a>
            <a href="suppliers.php" class="nav-link active"><i class="bi bi-truck"></i><span>Suppliers</span></a>
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
            <div class="topbar-page-title">Suppliers</div>
            <div class="topbar-sub">Manage herbal medicine suppliers &amp; contacts</div>
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
            <i class="bi bi-check-circle-fill"></i> Supplier added successfully!
        </div>
        <?php endif; ?>
        <?php if(isset($_GET['deleted'])): ?>
        <div class="alert-ayur alert-ayur-warning">
            <i class="bi bi-trash3-fill"></i> Supplier deleted successfully.
        </div>
        <?php endif; ?>

        <div class="section-header">
            <div class="section-title-group">
                <div class="section-title">All Suppliers</div>
                <div class="section-sub"><?= $total ?> supplier<?= $total != 1 ? 's' : '' ?> registered</div>
            </div>
            <div class="section-actions">
                <button class="btn-ayur-accent" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    <i class="bi bi-plus-lg"></i> Add Supplier
                </button>
            </div>
        </div>

        <!-- Supplier Cards Grid -->
        <?php if($total > 0): ?>
        <div class="row g-3">
            <?php while($row = $suppliers->fetch_assoc()): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card-panel h-100" style="position:relative; padding:22px; display:flex; flex-direction:column; gap:14px;">
                    <!-- Header row -->
                    <div class="d-flex align-items-start gap-12 justify-content-between">
                        <div class="d-flex align-items-center gap-12">
                            <div style="width:44px; height:44px; background:linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="bi bi-truck" style="color:white; font-size:1.1rem;"></i>
                            </div>
                            <div style="min-width:0;">
                                <div style="font-weight:800; font-size:0.92rem; color:var(--text-dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px;">
                                    <?= htmlspecialchars($row['name']) ?>
                                </div>
                            </div>
                        </div>
                        <a href="suppliers.php?delete=<?= $row['id'] ?>"
                           class="btn-icon btn-icon-del"
                           title="Delete supplier"
                           onclick="return confirm('Delete supplier \'<?= addslashes(htmlspecialchars($row['name'])) ?>\'?')"
                           style="flex-shrink:0;">
                            <i class="bi bi-trash3"></i>
                        </a>
                    </div>

                    <!-- Details -->
                    <div style="display:flex; flex-direction:column; gap:7px; font-size:0.82rem;">
                        <?php if($row['phone']): ?>
                        <div style="display:flex; align-items:center; gap:8px; color:var(--text-muted);">
                            <i class="bi bi-telephone-fill" style="color:var(--accent); flex-shrink:0;"></i>
                            <a href="tel:<?= htmlspecialchars($row['phone']) ?>" style="color:inherit; transition:color 0.15s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-muted)'">
                                <?= htmlspecialchars($row['phone']) ?>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if($row['address']): ?>
                        <div style="display:flex; align-items:flex-start; gap:8px; color:var(--text-muted);">
                            <i class="bi bi-geo-alt-fill" style="color:var(--accent); flex-shrink:0; margin-top:2px;"></i>
                            <span><?= htmlspecialchars($row['address']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php else: ?>
        <!-- Empty state -->
        <div class="card-panel">
            <div class="card-panel-body">
                <div class="empty-state">
                    <i class="bi bi-truck empty-state-icon"></i>
                    <div class="empty-state-title">No suppliers yet</div>
                    <div class="empty-state-desc">Click <strong>Add Supplier</strong> to register your first herbal medicine supplier.</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- ADD SUPPLIER MODAL -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierLabel">
                    <i class="bi bi-truck me-2" style="color:var(--accent);"></i>
                    Add New Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Supplier / Company Name <span style="color:var(--red-text)">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Nalanda Ayurveda Traders" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. 071-4523890">
                </div>
                <div class="mt-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Supplier address…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px; font-size:0.85rem; font-weight:600;">Cancel</button>
                <button type="submit" name="add_supplier" class="btn-ayur-accent">
                    <i class="bi bi-check2"></i> Save Supplier
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