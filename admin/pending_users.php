<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Handle payment received and activation
if (isset($_POST['activate_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Get user details
    $user_query = "SELECT * FROM users WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);
    
    if ($user) {
        $joining_amount = 1000;
        $company_share = $joining_amount * 0.50; // 50% to company
        $user_share = $joining_amount * 0.50; // 50% to user
        $referrer_bonus = 0;
        
        // Check if user was referred
        if ($user['referred_by']) {
            $user_share = $joining_amount * 0.40; // 40% to user
            $referrer_bonus = $joining_amount * 0.10; // 10% to referrer
        }
        
        // Update user status and wallet
        $update_user = "UPDATE users SET status = 'active', amount_received = 'yes', 
                       wallet_balance = $user_share WHERE id = $user_id";
        mysqli_query($conn, $update_user);
        
        // Add user transaction
        $user_trans = "INSERT INTO transactions (user_id, transaction_type, amount, description) 
                      VALUES ($user_id, 'joining_bonus', $user_share, 'Joining bonus credited')";
        mysqli_query($conn, $user_trans);
        
        // Update company wallet
        $update_company = "UPDATE company_wallet SET total_balance = total_balance + $company_share WHERE id = 1";
        mysqli_query($conn, $update_company);
        
        // Add company transaction
        $company_trans = "INSERT INTO company_transactions (transaction_type, amount, description, user_id) 
                         VALUES ('joining_share', $company_share, 'Company share from user joining', $user_id)";
        mysqli_query($conn, $company_trans);
        
        // If referred, give bonus to referrer
        if ($user['referred_by']) {
            $referrer_id = $user['referred_by'];
            $update_referrer = "UPDATE users SET wallet_balance = wallet_balance + $referrer_bonus 
                               WHERE id = $referrer_id";
            mysqli_query($conn, $update_referrer);
            
            $referrer_trans = "INSERT INTO transactions (user_id, transaction_type, amount, description) 
                              VALUES ($referrer_id, 'referral_bonus', $referrer_bonus, 
                              'Referral bonus from user ID: $user_id')";
            mysqli_query($conn, $referrer_trans);
        }
        
        $success = "User activated successfully and amounts distributed!";
    }
}

// Get pending users
$pending_query = "SELECT u.*, 
                  (SELECT username FROM users WHERE id = u.referred_by) as referrer_name
                  FROM users u 
                  WHERE u.status = 'inactive' 
                  ORDER BY u.created_at DESC";
$pending_result = mysqli_query($conn, $pending_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Users - MLM System</title>
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
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a class="nav-link active" href="pending_users.php">
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
                        <span class="navbar-brand">Pending Users</span>
                    </div>
                </nav>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-clock"></i> Users Waiting for Activation</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Referred By</th>
                                        <th>Referral Code</th>
                                        <th>Registered</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($pending_result) > 0): ?>
                                        <?php while($user = mysqli_fetch_assoc($pending_result)): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo $user['username']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo $user['mobile']; ?></td>
                                            <td>
                                                <?php if($user['referrer_name']): ?>
                                                    <span class="badge bg-info"><?php echo $user['referrer_name']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Direct</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-secondary"><?php echo $user['referral_code']; ?></span></td>
                                            <td><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="activate_user" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('Confirm that payment of ₹1000 is received and activate this user?')">
                                                        <i class="fas fa-check"></i> Activate
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-info-circle"></i> No pending users
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