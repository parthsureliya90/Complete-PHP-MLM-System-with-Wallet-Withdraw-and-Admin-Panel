<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Get company wallet balance
$wallet_query = "SELECT total_balance FROM company_wallet WHERE id = 1";
$wallet_result = mysqli_query($conn, $wallet_query);
$company_balance = mysqli_fetch_assoc($wallet_result)['total_balance'];

// Get company transactions
$trans_query = "SELECT ct.*, u.username 
                FROM company_transactions ct
                LEFT JOIN users u ON ct.user_id = u.id
                ORDER BY ct.created_at DESC";
$trans_result = mysqli_query($conn, $trans_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Transactions - MLM System</title>
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
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
                    <hr class="bg-white">
                </div>
                <nav class="nav flex-column px-2">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link" href="users.php"><i class="fas fa-users"></i> Manage Users</a>
                    <a class="nav-link" href="pending_users.php"><i class="fas fa-user-clock"></i> Pending Users</a>
                    <a class="nav-link" href="wallet.php"><i class="fas fa-wallet"></i> Company Wallet</a>
                    <a class="nav-link" href="withdrawal_requests.php"><i class="fas fa-money-check-alt"></i> Withdrawal Requests</a>
                    <a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
                    <a class="nav-link active" href="company_transactions.php"><i class="fas fa-building"></i> Company Transactions</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <div class="col-md-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">Company Transactions</span>
                    </div>
                </nav>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="text-white mb-2">Company Wallet Balance</h5>
                        <h1 class="display-4 text-white mb-0">₹<?php echo number_format($company_balance, 2); ?></h1>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> All Company Transactions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date & Time</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Related User</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($trans = mysqli_fetch_assoc($trans_result)): ?>
                                    <tr>
                                        <td><?php echo $trans['id']; ?></td>
                                        <td><?php echo date('d M Y H:i:s', strtotime($trans['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo ucfirst(str_replace('_', ' ', $trans['transaction_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $trans['description']; ?></td>
                                        <td>
                                            <?php if($trans['username']): ?>
                                                <span class="badge bg-info"><?php echo $trans['username']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="text-success">+₹<?php echo number_format($trans['amount'], 2); ?></strong>
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