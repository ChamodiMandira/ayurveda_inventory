<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Admin only (PRESERVED EXACTLY)
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Add User (PRESERVED EXACTLY)
if (isset($_POST['add_user'])) {
    $username  = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password  = md5(trim($_POST['password']));
    $role      = trim($_POST['role']);

    $stmt = $conn->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $full_name, $password, $role);
    $stmt->execute();
    header("Location: users.php?success=1");
    exit();
}

// Delete User (PRESERVED EXACTLY)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $id");
    }
    header("Location: users.php?deleted=1");
    exit();
}

$users = $conn->query("SELECT * FROM users ORDER BY full_name");
$total = $users->num_rows;

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
    <title>Users &amp; Staff — Disanayaka Ayurveda</title>
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
            <a href="suppliers.php" class="nav-link"><i class="bi bi-truck"></i><span>Suppliers</span></a>
        </div>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Management</div>
        <div class="sidebar-nav-group">
            <a href="users.php" class="nav-link active"><i class="bi bi-people"></i><span>Users &amp; Staff</span></a>
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
            <div class="topbar-page-title">Users &amp; Staff</div>
            <div class="topbar-sub">Manage system users and role permissions</div>
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
            <i class="bi bi-check-circle-fill"></i> User created successfully!
        </div>
        <?php endif; ?>
        <?php if(isset($_GET['deleted'])): ?>
        <div class="alert-ayur alert-ayur-warning">
            <i class="bi bi-trash3-fill"></i> User has been deleted.
        </div>
        <?php endif; ?>

        <div class="section-header">
            <div class="section-title-group">
                <div class="section-title">System Users</div>
                <div class="section-sub"><?= $total ?> user<?= $total != 1 ? 's' : '' ?> registered</div>
            </div>
            <div class="section-actions">
                <button class="btn-ayur-accent" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-lg"></i> Add User
                </button>
            </div>
        </div>

        <div class="card-panel">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>User</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th style="width:80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while($row = $users->fetch_assoc()): ?>
                        <?php
                            // Initials for each user
                            $u_init = '';
                            foreach (explode(' ', trim($row['full_name'])) as $w) {
                                if ($w) { $u_init .= strtoupper($w[0]); if (strlen($u_init) >= 2) break; }
                            }
                        ?>
                        <tr>
                            <td class="col-num"><?= $i++ ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg, var(--primary), var(--primary-light)); display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:800; color:white; flex-shrink:0;">
                                        <?= $u_init ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:700; font-size:0.88rem; color:var(--text-dark);">
                                            <?= htmlspecialchars($row['full_name']) ?>
                                            <?php if($row['id'] == $_SESSION['user_id']): ?>
                                            <span style="font-size:0.68rem; font-weight:600; color:var(--accent); margin-left:6px;">(You)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-family: 'Courier New', monospace; font-size:0.84rem; font-weight:600; background:var(--bg-muted); padding:3px 9px; border-radius:6px; color:var(--text-muted);">
                                    <?= htmlspecialchars($row['username']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['role'] === 'admin'): ?>
                                    <span class="badge-pill badge-admin">
                                        <i class="bi bi-shield-fill-check"></i> Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge-pill badge-staff">
                                        <i class="bi bi-person-check-fill"></i> Staff
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?= $row['id'] ?>"
                                   class="btn-icon btn-icon-del"
                                   title="Delete user"
                                   onclick="return confirm('Delete user \'<?= addslashes(htmlspecialchars($row['full_name'])) ?>\'?')">
                                    <i class="bi bi-trash3"></i>
                                </a>
                                <?php else: ?>
                                <span title="Cannot delete yourself" style="color:var(--text-xlight); padding:5px;">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Security notice -->
        <div style="margin-top:16px; padding:13px 18px; background:var(--gold-light); border:1px solid var(--gold-border); border-radius:var(--r-md); font-size:0.8rem; color:var(--gold-dark); display:flex; align-items:center; gap:10px;">
            <i class="bi bi-shield-lock-fill" style="font-size:1rem;"></i>
            <span><strong>Security Note:</strong> Passwords are hashed (MD5). Admin users can access all features. Staff can add stock but cannot manage users.</span>
        </div>

    </main>
</div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserLabel">
                    <i class="bi bi-person-plus-fill me-2" style="color:var(--accent);"></i>
                    Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Full Name <span style="color:var(--red-text)">*</span></label>
                    <input type="text" name="full_name" class="form-control" placeholder="e.g. Kanchana Perera" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username <span style="color:var(--red-text)">*</span></label>
                    <input type="text" name="username" class="form-control" placeholder="e.g. kanchana.p" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password <span style="color:var(--red-text)">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 4 characters" required autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role <span style="color:var(--red-text)">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="">— Select Role —</option>
                        <option value="admin">Admin (Full Access)</option>
                        <option value="staff">Staff (Limited Access)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px; font-size:0.85rem; font-weight:600;">Cancel</button>
                <button type="submit" name="add_user" class="btn-ayur-accent">
                    <i class="bi bi-check2"></i> Create User
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