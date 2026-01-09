<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Get all transactions
$trans_query = "SELECT * FROM transactions WHERE user_id = $user_id 
                ORDER BY created_at DESC";
$trans_result = mysqli_query($conn, $trans_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - MLM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .sidebar .nav-link { color: #ecf0f1; padding: 12px 20px; margin: 5px 0; border-radius: 5px; }
        .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4><i class="fas fa-user"></i> User Panel</h4>
                    <hr class="bg-white">
                </div>
                <nav class="nav flex-column px-2">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link" href="wallet.php"><i class="fas fa-wallet"></i> My Wallet</a>
                    <a class="nav-link active" href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
                    <a class="nav-link" href="my_team.php"><i class="fas fa-users"></i> My Team</a>
                    <a class="nav-link" href="withdraw.php"><i class="fas fa-money-check-alt"></i> Withdraw</a>
                    <a class="nav-link" href="profile.php"><i class="fas fa-user-cog"></i> Profile</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <div class="col-md-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">Transaction History</span>
                    </div>
                </nav>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> All Transactions</h5>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($trans_result) > 0): ?>
                                        <?php while($trans = mysqli_fetch_assoc($trans_result)): ?>
                                        <tr>
                                            <td><?php echo date('d M Y H:i:s', strtotime($trans['created_at'])); ?></td>
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
                                            <td colspan="4" class="text-center py-4">
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