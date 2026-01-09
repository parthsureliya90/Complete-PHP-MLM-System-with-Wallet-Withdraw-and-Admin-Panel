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

// Get wallet statistics
$total_credits = "SELECT SUM(amount) as total FROM transactions 
                  WHERE user_id = $user_id AND amount > 0";
$total_credits_result = mysqli_query($conn, $total_credits);
$total_credit_amount = mysqli_fetch_assoc($total_credits_result)['total'] ?? 0;

$total_debits = "SELECT SUM(amount) as total FROM transactions 
                 WHERE user_id = $user_id AND amount < 0";
$total_debits_result = mysqli_query($conn, $total_debits);
$total_debit_amount = abs(mysqli_fetch_assoc($total_debits_result)['total'] ?? 0);

$joining_bonus = "SELECT amount FROM transactions 
                  WHERE user_id = $user_id AND transaction_type = 'joining_bonus'";
$joining_bonus_result = mysqli_query($conn, $joining_bonus);
$joining_bonus_amount = 0;
if (mysqli_num_rows($joining_bonus_result) > 0) {
    $joining_bonus_amount = mysqli_fetch_assoc($joining_bonus_result)['amount'];
}

$referral_earnings = "SELECT SUM(amount) as total FROM transactions 
                      WHERE user_id = $user_id AND transaction_type = 'referral_bonus'";
$referral_earnings_result = mysqli_query($conn, $referral_earnings);
$referral_earnings_amount = mysqli_fetch_assoc($referral_earnings_result)['total'] ?? 0;

// Get monthly earnings
$monthly_query = "SELECT SUM(amount) as total FROM transactions 
                  WHERE user_id = $user_id 
                  AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())
                  AND amount > 0";
$monthly_result = mysqli_query($conn, $monthly_query);
$monthly_earnings = mysqli_fetch_assoc($monthly_result)['total'] ?? 0;

// Get all transactions
$trans_query = "SELECT * FROM transactions WHERE user_id = $user_id 
                ORDER BY created_at DESC";
$trans_result = mysqli_query($conn, $trans_query);

// Get pending withdrawal requests
$pending_withdrawals = "SELECT SUM(amount) as total FROM withdrawal_requests 
                        WHERE user_id = $user_id AND status = 'pending'";
$pending_withdrawals_result = mysqli_query($conn, $pending_withdrawals);
$pending_withdrawal_amount = mysqli_fetch_assoc($pending_withdrawals_result)['total'] ?? 0;

// Get approved withdrawals
$approved_withdrawals = "SELECT SUM(amount) as total FROM withdrawal_requests 
                         WHERE user_id = $user_id AND status = 'approved'";
$approved_withdrawals_result = mysqli_query($conn, $approved_withdrawals);
$approved_withdrawal_amount = mysqli_fetch_assoc($approved_withdrawals_result)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet - MLM System</title>
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
        .wallet-balance { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; }
        .income-card { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 20px; border-radius: 10px; }
        .expense-card { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; padding: 20px; border-radius: 10px; }
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
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link active" href="wallet.php"><i class="fas fa-wallet"></i> My Wallet</a>
                    <a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
                    <a class="nav-link" href="my_team.php"><i class="fas fa-users"></i> My Team</a>
                    <a class="nav-link" href="withdraw.php"><i class="fas fa-money-check-alt"></i> Withdraw</a>
                    <a class="nav-link" href="profile.php"><i class="fas fa-user-cog"></i> Profile</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">My Wallet</span>
                    </div>
                </nav>

                <!-- Wallet Balance Card -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="wallet-balance shadow-lg">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="mb-2"><i class="fas fa-wallet"></i> Available Balance</h4>
                                    <h1 class="display-3 mb-0">₹<?php echo number_format($user['wallet_balance'], 2); ?></h1>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="withdraw.php" class="btn btn-light btn-lg">
                                        <i class="fas fa-money-check-alt"></i> Withdraw Money
                                    </a>
                                    <?php if($pending_withdrawal_amount > 0): ?>
                                        <div class="alert alert-warning mt-3 mb-0">
                                            <small><strong>Pending Withdrawal:</strong> ₹<?php echo number_format($pending_withdrawal_amount, 2); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Income/Expense Summary -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="income-card shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Total Income</h6>
                                    <h2 class="mb-0">₹<?php echo number_format($total_credit_amount, 2); ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-arrow-up fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="expense-card shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Total Withdrawals</h6>
                                    <h2 class="mb-0">₹<?php echo number_format($total_debit_amount, 2); ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-arrow-down fa-3x opacity-50"></i>
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
                                <h6 class="text-muted mb-1">Joining Bonus</h6>
                                <h4 class="mb-0 text-primary">₹<?php echo number_format($joining_bonus_amount, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #27ae60 !important;">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Referral Earnings</h6>
                                <h4 class="mb-0 text-success">₹<?php echo number_format($referral_earnings_amount, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #f39c12 !important;">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">This Month</h6>
                                <h4 class="mb-0 text-warning">₹<?php echo number_format($monthly_earnings, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm" style="border-left-color: #e74c3c !important;">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Withdrawn</h6>
                                <h4 class="mb-0 text-danger">₹<?php echo number_format($approved_withdrawal_amount, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Transaction History</h5>
                            <a href="transactions.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Balance After</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 0;
                                    $running_balance = $user['wallet_balance'];
                                    mysqli_data_seek($trans_result, 0);
                                    
                                    if(mysqli_num_rows($trans_result) > 0): 
                                        while($trans = mysqli_fetch_assoc($trans_result)): 
                                            if($count >= 10) break;
                                            $count++;
                                    ?>
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
                                        <td>₹<?php echo number_format($running_balance, 2); ?></td>
                                    </tr>
                                    <?php 
                                        $running_balance -= $trans['amount'];
                                        endwhile; 
                                    else: 
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-info-circle"></i> No transactions yet
                                            </td>
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
</body>
</html>