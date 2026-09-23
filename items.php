<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'includes/db.php';

// Add Item (PRESERVED EXACTLY)
if (isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $unit = trim($_POST['unit']);
    $min_stock = (int)$_POST['min_stock'];
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO items (name, category_id, unit, min_stock, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisis", $name, $category_id, $unit, $min_stock, $description);
    $stmt->execute();
    header("Location: items.php?success=1");
    exit();
}

// Delete Item (PRESERVED EXACTLY)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM items WHERE id = $id");
    header("Location: items.php?deleted=1");
    exit();
}

// Search (PRESERVED EXACTLY)
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$sql = "
    SELECT i.*, c.name as category_name,
           COALESCE((SELECT SUM(quantity) FROM stock_in WHERE item_id = i.id), 0) - 
           COALESCE((SELECT SUM(quantity) FROM stock_out WHERE item_id = i.id), 0) as current_stock
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
";

if ($search != "") {
    $sql .= " WHERE i.name LIKE '%" . $conn->real_escape_string($search) . "%' 
              OR c.name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
$sql .= " ORDER BY i.name";
$items = $conn->query($sql);

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$total_items = $items->num_rows;

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
    <title>Items Management — Disanayaka Ayurveda</title>
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
            <a href="items.php" class="nav-link active"><i class="bi bi-box-seam"></i><span>Items</span></a>
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
            <div class="topbar-page-title">Items Management</div>
            <div class="topbar-sub">Manage herbal medicines &amp; inventory items</div>
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

        <!-- Alerts -->
        <?php if(isset($_GET['success'])): ?>
        <div class="alert-ayur alert-ayur-success">
            <i class="bi bi-check-circle-fill"></i> Item added successfully!
        </div>
        <?php endif; ?>
        <?php if(isset($_GET['deleted'])): ?>
        <div class="alert-ayur alert-ayur-warning">
            <i class="bi bi-trash3-fill"></i> Item has been deleted.
        </div>
        <?php endif; ?>

        <!-- Section header -->
        <div class="section-header">
            <div class="section-title-group">
                <div class="section-title">All Items</div>
                <div class="section-sub">
                    <?= $total_items ?> item<?= $total_items != 1 ? 's' : '' ?> found
                    <?= $search ? ' for "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
                </div>
            </div>
            <div class="section-actions">
                <!-- Search -->
                <form method="GET" class="d-flex align-items-center gap-2">
                    <div class="search-wrap">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="search" placeholder="Search items or category…" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <button type="submit" class="btn-ayur" style="padding:9px 14px;"><i class="bi bi-search"></i></button>
                    <?php if($search): ?>
                    <a href="items.php" class="btn-ayur-outline" style="padding:8px 12px;" title="Clear search">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <?php endif; ?>
                </form>
                <!-- Add Item -->
                <button class="btn-ayur-accent" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-lg"></i> Add Item
                </button>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card-panel">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Current Stock</th>
                            <th>Min Stock</th>
                            <th>Status</th>
                            <th style="width:80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while($row = $items->fetch_assoc()): ?>
                        <tr>
                            <td class="col-num"><?= $i++ ?></td>
                            <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <span class="badge-pill badge-teal">
                                    <?= htmlspecialchars($row['category_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($row['unit']) ?></td>
                            <td>
                                <span style="font-weight:700; color:<?= $row['current_stock'] <= 0 ? 'var(--red-text)' : ($row['current_stock'] < $row['min_stock'] ? 'var(--amber-text)' : 'var(--green-text)') ?>;">
                                    <?= number_format($row['current_stock'], 1) ?>
                                </span>
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
                            <td>
                                <a href="items.php?delete=<?= $row['id'] ?>"
                                   class="btn-icon btn-icon-del"
                                   title="Delete item"
                                   onclick="return confirm('Delete \'<?= addslashes(htmlspecialchars($row['name'])) ?>\'? This cannot be undone.')">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($total_items == 0): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-box-seam empty-state-icon"></i>
                                    <div class="empty-state-title">
                                        <?= $search ? 'No items found' : 'No items yet' ?>
                                    </div>
                                    <div class="empty-state-desc">
                                        <?= $search ? 'Try a different search term.' : 'Click <strong>Add Item</strong> to register your first inventory item.' ?>
                                    </div>
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

<!-- ======== ADD ITEM MODAL ======== -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">
                    <i class="bi bi-plus-circle-fill me-2" style="color:var(--accent);"></i>
                    Add New Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Item Name <span style="color:var(--red-text)">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Triphala Churna" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category <span style="color:var(--red-text)">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">— Select a category —</option>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Unit <span style="color:var(--red-text)">*</span></label>
                        <input type="text" name="unit" class="form-control" placeholder="Packet / Bottle / kg" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Min Stock <span style="color:var(--red-text)">*</span></label>
                        <input type="number" name="min_stock" class="form-control" value="10" min="0" required>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Optional notes about this item…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px; font-size:0.85rem; font-weight:600;">Cancel</button>
                <button type="submit" name="add_item" class="btn-ayur-accent">
                    <i class="bi bi-check2"></i> Save Item
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