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

$error = '';
$success = '';

// Handle withdrawal request
if (isset($_POST['submit_withdrawal'])) {
    $amount = (float)$_POST['amount'];
    
    if ($amount < 1000) {
        $error = "Minimum withdrawal amount is ₹1000!";
    } elseif ($amount > $user['wallet_balance']) {
        $error = "Insufficient balance!";
    } else {
        // Insert withdrawal request
        $insert_query = "INSERT INTO withdrawal_requests (user_id, amount) 
                        VALUES ($user_id, $amount)";
        if (mysqli_query($conn, $insert_query)) {
            $success = "Withdrawal request submitted successfully! Please wait for admin approval.";
        } else {
            $error = "Failed to submit withdrawal request!";
        }
    }
}

// Get withdrawal history
$history_query = "SELECT * FROM withdrawal_requests WHERE user_id = $user_id 
                 ORDER BY requested_at DESC";
$history_result = mysqli_query($conn, $history_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw - MLM System</title>
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
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4><i class="fas fa-user"></i> User Panel</h4>
                    <hr class="bg-white">
                </div>
                <nav class="nav flex-column px-2">
                    <a class="nav-link" href="dashboard.php">
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
                    <a class="nav-link active" href="withdraw.php">
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
                        <span class="navbar-brand">Withdraw Funds</span>
                    </div>
                </nav>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-money-check-alt"></i> Request Withdrawal</h5>
                            </div>
                            <div class="card-body">
                                <?php if($error): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if($success): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="alert alert-info">
                                    <strong>Available Balance:</strong> ₹<?php echo number_format($user['wallet_balance'], 2); ?>
                                </div>

                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Withdrawal Amount</label>
                                        <input type="number" class="form-control" name="amount" 
                                               min="1000" step="0.01" required 
                                               placeholder="Minimum ₹1000">
                                        <small class="text-muted">Minimum withdrawal: ₹1000</small>
                                    </div>

                                    <?php if(!$user['bank_account_no'] && !$user['upi_id']): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Please update your bank details or UPI ID in profile before withdrawing.
                                        </div>
                                        <a href="profile.php" class="btn btn-warning">
                                            <i class="fas fa-user-edit"></i> Update Bank Details
                                        </a>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label class="form-label">Payment will be sent to:</label>
                                            <?php if($user['bank_account_no']): ?>
                                                <div class="alert alert-light">
                                                    <strong>Bank Account:</strong> <?php echo $user['bank_account_no']; ?><br>
                                                    <strong>Account Name:</strong> <?php echo $user['bank_account_name']; ?><br>
                                                    <strong>IFSC:</strong> <?php echo $user['ifsc_code']; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($user['upi_id']): ?>
                                                <div class="alert alert-light">
                                                    <strong>UPI ID:</strong> <?php echo $user['upi_id']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <button type="submit" name="submit_withdrawal" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-paper-plane"></i> Submit Withdrawal Request
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history"></i> Withdrawal History</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($history_result) > 0): ?>
                                                <?php while($req = mysqli_fetch_assoc($history_result)): ?>
                                                <tr>
                                                    <td><?php echo date('d M Y H:i', strtotime($req['requested_at'])); ?></td>
                                                    <td>₹<?php echo number_format($req['amount'], 2); ?></td>
                                                    <td>
                                                        <?php if($req['status'] == 'pending'): ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php elseif($req['status'] == 'approved'): ?>
                                                            <span class="badge bg-success">Approved</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Rejected</span>
                                                            <?php if($req['rejection_reason']): ?>
                                                                <br><small class="text-danger"><?php echo $req['rejection_reason']; ?></small>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-4">No withdrawal requests yet</td>
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