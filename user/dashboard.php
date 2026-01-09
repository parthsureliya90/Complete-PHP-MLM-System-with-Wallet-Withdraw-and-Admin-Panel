<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get total referrals
$referrals_query = "SELECT COUNT(*) as total FROM users WHERE referred_by = $user_id";
$referrals_result = mysqli_query($conn, $referrals_query);
$total_referrals = mysqli_fetch_assoc($referrals_result)['total'];

// Get total earnings
$earnings_query = "SELECT SUM(amount) as total FROM transactions 
                   WHERE user_id = $user_id AND transaction_type IN ('joining_bonus', 'referral_bonus')";
$earnings_result = mysqli_query($conn, $earnings_query);
$total_earnings = mysqli_fetch_assoc($earnings_result)['total'] ?? 0;

// Get total withdrawals
$withdrawals_query = "SELECT SUM(amount) as total FROM withdrawal_requests 
                      WHERE user_id = $user_id AND status = 'approved'";
$withdrawals_result = mysqli_query($conn, $withdrawals_query);
$total_withdrawals = mysqli_fetch_assoc($withdrawals_result)['total'] ?? 0;

// Get recent transactions
$recent_trans_query = "SELECT * FROM transactions WHERE user_id = $user_id 
                       ORDER BY created_at DESC LIMIT 10";
$recent_trans_result = mysqli_query($conn, $recent_trans_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MLM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .sidebar .nav-link { color: #ecf0f1; padding: 12px 20px; margin: 5px 0; border-radius: 5px; }
        .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); }
        .stat-card { border-left: 4px solid; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .referral-code-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4><i class="fas fa-user"></i> User Panel</h4>
                    <hr class="bg-white">
                </div>
                <nav class="nav flex-column px-2">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="wallet.php">
                        <i class="fas fa-wallet"></i> My Wallet
                    </a>
                    <a class="nav-link" href="transactions.php">
                        <i class="fas fa-exchange-alt"></i> Transactions
                    </a>
                    <a class="nav-link" href="my_team.php">
                        <i class="fas fa-users"></i> My Team
                    </a>
                    <a class="nav-link" href="withdraw.php">
                        <i class="fas fa-money-check-alt"></i> Withdraw
                    </a>
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user-cog"></i> Profile
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
                                Welcome, <strong><?php echo $user['username']; ?></strong>
                            </span>
                        </div>
                    </div>
                </nav>

                <!-- Referral Code Box -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="referral-code-box">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5>Your Referral Code</h5>
                                    <h2 class="mb-0"><?php echo $user['referral_code']; ?></h2>
                                </div>
                                <div class="col-md-6 text-end">
                                    <p class="mb-2">Share your referral link:</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="referralLink" 
                                               value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/register.php?ref=' . $user['referral_code']; ?>" readonly>
                                        <button class="btn btn-light" onclick="copyLink()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #3498db !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Wallet Balance</h6>
                                        <h3 class="mb-0">₹<?php echo number_format($user['wallet_balance'], 2); ?></h3>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-wallet fa-3x"></i>
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
                                        <h6 class="text-muted mb-1">Total Earnings</h6>
                                        <h3 class="mb-0">₹<?php echo number_format($total_earnings, 2); ?></h3>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-chart-line fa-3x"></i>
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
                                        <h6 class="text-muted mb-1">Total Referrals</h6>
                                        <h3 class="mb-0"><?php echo $total_referrals; ?></h3>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-users fa-3x"></i>
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
                                        <h6 class="text-muted mb-1">Total Withdrawals</h6>
                                        <h3 class="mb-0">₹<?php echo number_format($total_withdrawals, 2); ?></h3>
                                    </div>
                                    <div class="text-danger">
                                        <i class="fas fa-money-bill-wave fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Recent Transactions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($recent_trans_result) > 0): ?>
                                        <?php while($trans = mysqli_fetch_assoc($recent_trans_result)): ?>
                                        <tr>
                                            <td><?php echo date('d M Y H:i', strtotime($trans['created_at'])); ?></td>
                                            <td>
                                                <?php if($trans['transaction_type'] == 'joining_bonus'): ?>
                                                    <span class="badge bg-primary">Joining Bonus</span>
                                                <?php elseif($trans['transaction_type'] == 'referral_bonus'): ?>
                                                    <span class="badge bg-success">Referral Bonus</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Withdrawal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $trans['description']; ?></td>
                                            <td>
                                                <strong class="<?php echo $trans['amount'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $trans['amount'] > 0 ? '+' : ''; ?>₹<?php echo number_format($trans['amount'], 2); ?>
                                                </strong>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">No transactions yet</td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyLink() {
            const linkInput = document.getElementById('referralLink');
            linkInput.select();
            document.execCommand('copy');
            alert('Referral link copied to clipboard!');
        }
    </script>
</body>
</html>