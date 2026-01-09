<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Get statistics
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

$active_users_query = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
$active_users_result = mysqli_query($conn, $active_users_query);
$active_users = mysqli_fetch_assoc($active_users_result)['total'];

$pending_users_query = "SELECT COUNT(*) as total FROM users WHERE status = 'inactive'";
$pending_users_result = mysqli_query($conn, $pending_users_query);
$pending_users = mysqli_fetch_assoc($pending_users_result)['total'];

$withdrawal_requests_query = "SELECT COUNT(*) as total FROM withdrawal_requests WHERE status = 'pending'";
$withdrawal_requests_result = mysqli_query($conn, $withdrawal_requests_query);
$pending_withdrawals = mysqli_fetch_assoc($withdrawal_requests_result)['total'];

$company_wallet_query = "SELECT total_balance FROM company_wallet WHERE id = 1";
$company_wallet_result = mysqli_query($conn, $company_wallet_query);
$company_balance = mysqli_fetch_assoc($company_wallet_result)['total_balance'];

// Get recent users
$recent_users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 10";
$recent_users_result = mysqli_query($conn, $recent_users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MLM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); }
        .sidebar .nav-link { color: #ecf0f1; padding: 12px 20px; margin: 5px 0; border-radius: 5px; }
        .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar .nav-link.active { background-color: #3498db; }
        .stat-card { border-left: 4px solid; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
                    <hr class="bg-white">
                </div>
                <nav class="nav flex-column px-2">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a class="nav-link" href="pending_users.php">
                        <i class="fas fa-user-clock"></i> Pending Users
                    </a>
                    <a class="nav-link" href="wallet.php">
                        <i class="fas fa-wallet"></i> Company Wallet
                    </a>
                    <a class="nav-link" href="withdrawal_requests.php">
                        <i class="fas fa-money-check-alt"></i> Withdrawal Requests
                        <?php if($pending_withdrawals > 0): ?>
                            <span class="badge bg-danger"><?php echo $pending_withdrawals; ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="nav-link" href="transactions.php">
                        <i class="fas fa-exchange-alt"></i> Transactions
                    </a>
                    <a class="nav-link" href="company_transactions.php">
                        <i class="fas fa-building"></i> Company Transactions
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">Dashboard</span>
                        <div class="d-flex">
                            <span class="navbar-text me-3">
                                Welcome, <strong><?php echo $_SESSION['admin_username']; ?></strong>
                            </span>
                        </div>
                    </div>
                </nav>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #3498db !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Users</h6>
                                        <h3 class="mb-0"><?php echo $total_users; ?></h3>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-users fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #27ae60 !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Active Users</h6>
                                        <h3 class="mb-0"><?php echo $active_users; ?></h3>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-user-check fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #f39c12 !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Pending Users</h6>
                                        <h3 class="mb-0"><?php echo $pending_users; ?></h3>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-user-clock fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #e74c3c !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Pending Withdrawals</h6>
                                        <h3 class="mb-0"><?php echo $pending_withdrawals; ?></h3>
                                    </div>
                                    <div class="text-danger">
                                        <i class="fas fa-exclamation-circle fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Wallet -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h5 class="text-white mb-2">Company Wallet Balance</h5>
                                <h1 class="display-4 text-white mb-0">₹<?php echo number_format($company_balance, 2); ?></h1>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Recent Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Referral Code</th>
                                        <th>Wallet Balance</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = mysqli_fetch_assoc($recent_users_result)): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo $user['username']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td><span class="badge bg-info"><?php echo $user['referral_code']; ?></span></td>
                                        <td>₹<?php echo number_format($user['wallet_balance'], 2); ?></td>
                                        <td>
                                            <?php if($user['status'] == 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>