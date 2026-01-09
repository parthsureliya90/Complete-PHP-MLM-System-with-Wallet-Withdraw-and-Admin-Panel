<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Handle user activation/deactivation
if (isset($_POST['toggle_status'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = $_POST['new_status'];
    
    $update = "UPDATE users SET status = '$new_status' WHERE id = $user_id";
    mysqli_query($conn, $update);
    $success = "User status updated successfully!";
}

// Get all users
$users_query = "SELECT u.*, 
                (SELECT username FROM users WHERE id = u.referred_by) as referrer_name,
                (SELECT COUNT(*) FROM users WHERE referred_by = u.id) as total_referrals
                FROM users u 
                ORDER BY u.created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - MLM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); }
        .sidebar .nav-link { color: #ecf0f1; padding: 12px 20px; margin: 5px 0; border-radius: 5px; }
        .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar .nav-link.active { background-color: #3498db; }
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
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="users.php">
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
                        <span class="navbar-brand">Manage Users</span>
                    </div>
                </nav>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>User Info</th>
                                        <th>Referred By</th>
                                        <th>Referrals</th>
                                        <th>Wallet</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <strong><?php echo $user['username']; ?></strong><br>
                                            <small class="text-muted"><?php echo $user['email']; ?></small><br>
                                            <small class="text-muted"><?php echo $user['mobile']; ?></small><br>
                                            <span class="badge bg-info"><?php echo $user['referral_code']; ?></span>
                                        </td>
                                        <td>
                                            <?php if($user['referrer_name']): ?>
                                                <span class="badge bg-primary"><?php echo $user['referrer_name']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Direct</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $user['total_referrals']; ?></span>
                                        </td>
                                        <td>
                                            <strong>₹<?php echo number_format($user['wallet_balance'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($user['status'] == 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $user['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" 
                                                        class="btn btn-sm <?php echo $user['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>">
                                                    <i class="fas fa-<?php echo $user['status'] == 'active' ? 'ban' : 'check'; ?>"></i>
                                                    <?php echo $user['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        </td>
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