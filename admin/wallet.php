<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Get company wallet balance
$wallet_query = "SELECT * FROM company_wallet WHERE id = 1";
$wallet_result = mysqli_query($conn, $wallet_query);
$company_wallet = mysqli_fetch_assoc($wallet_result);

// Handle manual credit/debit
$success = '';
$error = '';

if (isset($_POST['add_transaction'])) {
    $type = sanitize($conn, $_POST['type']);
    $amount = (float)$_POST['amount'];
    $description = sanitize($conn, $_POST['description']);
    
    if ($amount <= 0) {
        $error = "Amount must be greater than 0!";
    } else {
        if ($type == 'credit') {
            // Add to company wallet
            $update = "UPDATE company_wallet SET total_balance = total_balance + $amount WHERE id = 1";
            mysqli_query($conn, $update);
            
            $trans = "INSERT INTO company_transactions (transaction_type, amount, description) 
                     VALUES ('system_credit', $amount, '$description')";
            mysqli_query($conn, $trans);
            $success = "₹" . number_format($amount, 2) . " credited to company wallet!";
        } else {
            // Deduct from company wallet
            if ($company_wallet['total_balance'] < $amount) {
                $error = "Insufficient balance in company wallet!";
            } else {
                $update = "UPDATE company_wallet SET total_balance = total_balance - $amount WHERE id = 1";
                mysqli_query($conn, $update);
                
                $trans = "INSERT INTO company_transactions (transaction_type, amount, description) 
                         VALUES ('system_credit', -$amount, '$description')";
                mysqli_query($conn, $trans);
                $success = "₹" . number_format($amount, 2) . " debited from company wallet!";
            }
        }
        
        // Refresh wallet data
        $wallet_result = mysqli_query($conn, $wallet_query);
        $company_wallet = mysqli_fetch_assoc($wallet_result);
    }
}

// Get recent company transactions
$trans_query = "SELECT ct.*, u.username 
                FROM company_transactions ct
                LEFT JOIN users u ON ct.user_id = u.id
                ORDER BY ct.created_at DESC
                LIMIT 50";
$trans_result = mysqli_query($conn, $trans_query);

// Get wallet statistics
$total_joining = "SELECT SUM(amount) as total FROM company_transactions WHERE transaction_type = 'joining_share'";
$total_joining_result = mysqli_query($conn, $total_joining);
$total_joining_amount = mysqli_fetch_assoc($total_joining_result)['total'] ?? 0;

$total_credits = "SELECT SUM(amount) as total FROM company_transactions WHERE amount > 0";
$total_credits_result = mysqli_query($conn, $total_credits);
$total_credit_amount = mysqli_fetch_assoc($total_credits_result)['total'] ?? 0;

$total_debits = "SELECT SUM(amount) as total FROM company_transactions WHERE amount < 0";
$total_debits_result = mysqli_query($conn, $total_debits);
$total_debit_amount = abs(mysqli_fetch_assoc($total_debits_result)['total'] ?? 0);

// Monthly earnings
$monthly_query = "SELECT SUM(amount) as total FROM company_transactions 
                  WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())
                  AND amount > 0";
$monthly_result = mysqli_query($conn, $monthly_query);
$monthly_earnings = mysqli_fetch_assoc($monthly_result)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Wallet - MLM System</title>
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
        .wallet-balance { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; }
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
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link" href="users.php"><i class="fas fa-users"></i> Manage Users</a>
                    <a class="nav-link" href="pending_users.php"><i class="fas fa-user-clock"></i> Pending Users</a>
                    <a class="nav-link active" href="wallet.php"><i class="fas fa-wallet"></i> Company Wallet</a>
                    <a class="nav-link" href="withdrawal_requests.php"><i class="fas fa-money-check-alt"></i> Withdrawal Requests</a>
                    <a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
                    <a class="nav-link" href="company_transactions.php"><i class="fas fa-building"></i> Company Transactions</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">Company Wallet Management</span>
                    </div>
                </nav>

                <?php if($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Wallet Balance Card -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="wallet-balance shadow-lg">
                            <div class="text-center">
                                <h4 class="mb-2"><i class="fas fa-building"></i> Company Wallet Balance</h4>
                                <h1 class="display-3 mb-0">₹<?php echo number_format($company_wallet['total_balance'], 2); ?></h1>
                                <p class="mb-0">Last Updated: <?php echo date('d M Y H:i:s', strtotime($company_wallet['updated_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #27ae60 !important;">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Credits</h6>
                                <h3 class="mb-0 text-success">₹<?php echo number_format($total_credit_amount, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #e74c3c !important;">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Debits</h6>
                                <h3 class="mb-0 text-danger">₹<?php echo number_format($total_debit_amount, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #3498db !important;">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">From Joinings</h6>
                                <h3 class="mb-0 text-primary">₹<?php echo number_format($total_joining_amount, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #f39c12 !important;">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">This Month</h6>
                                <h3 class="mb-0 text-warning">₹<?php echo number_format($monthly_earnings, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Add Transaction Form -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add Transaction</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Transaction Type</label>
                                        <select class="form-select" name="type" required>
                                            <option value="credit">Credit (Add Money)</option>
                                            <option value="debit">Debit (Remove Money)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Amount</label>
                                        <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" name="add_transaction" class="btn btn-primary w-100">
                                        <i class="fas fa-save"></i> Add Transaction
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Transactions</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Related User</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($trans_result) > 0): ?>
                                                <?php while($trans = mysqli_fetch_assoc($trans_result)): ?>
                                                <tr>
                                                    <td><?php echo date('d M Y H:i', strtotime($trans['created_at'])); ?></td>
                                                    <td>
                                                        <?php if($trans['transaction_type'] == 'joining_share'): ?>
                                                            <span class="badge bg-primary">Joining Share</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info">System Credit</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $trans['description']; ?></td>
                                                    <td>
                                                        <?php if($trans['username']): ?>
                                                            <span class="badge bg-secondary"><?php echo $trans['username']; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong class="<?php echo $trans['amount'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo $trans['amount'] > 0 ? '+' : ''; ?>₹<?php echo number_format($trans['amount'], 2); ?>
                                                        </strong>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">No transactions yet</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>