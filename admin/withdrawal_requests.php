<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Handle approval/rejection
if (isset($_POST['process_withdrawal'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    $rejection_reason = isset($_POST['rejection_reason']) ? sanitize($conn, $_POST['rejection_reason']) : '';
    
    if ($action == 'approve') {
        // Get withdrawal details
        $wr_query = "SELECT * FROM withdrawal_requests WHERE id = $request_id";
        $wr_result = mysqli_query($conn, $wr_query);
        $withdrawal = mysqli_fetch_assoc($wr_result);
        
        if ($withdrawal) {
            $user_id = $withdrawal['user_id'];
            $amount = $withdrawal['amount'];
            
            // Deduct from user wallet
            $update_wallet = "UPDATE users SET wallet_balance = wallet_balance - $amount WHERE id = $user_id";
            mysqli_query($conn, $update_wallet);
            
            // Update withdrawal status
            $update_wr = "UPDATE withdrawal_requests SET status = 'approved', 
                         processed_at = NOW() WHERE id = $request_id";
            mysqli_query($conn, $update_wr);
            
            // Add transaction
            $trans = "INSERT INTO transactions (user_id, transaction_type, amount, description) 
                     VALUES ($user_id, 'withdrawal', -$amount, 'Withdrawal processed')";
            mysqli_query($conn, $trans);
            
            $success = "Withdrawal approved and amount deducted!";
        }
    } elseif ($action == 'reject') {
        $update_wr = "UPDATE withdrawal_requests SET status = 'rejected', 
                     rejection_reason = '$rejection_reason', processed_at = NOW() 
                     WHERE id = $request_id";
        mysqli_query($conn, $update_wr);
        $success = "Withdrawal request rejected!";
    }
}

// Get withdrawal requests
$requests_query = "SELECT wr.*, u.username, u.email, u.mobile, u.wallet_balance,
                   u.bank_account_name, u.bank_account_no, u.ifsc_code, u.bank_name, u.upi_id
                   FROM withdrawal_requests wr
                   JOIN users u ON wr.user_id = u.id
                   ORDER BY 
                   CASE WHEN wr.status = 'pending' THEN 1 ELSE 2 END,
                   wr.requested_at DESC";
$requests_result = mysqli_query($conn, $requests_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Requests - MLM System</title>
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
                    <a class="nav-link" href="pending_users.php">
                        <i class="fas fa-user-clock"></i> Pending Users
                    </a>
                    <a class="nav-link" href="wallet.php">
                        <i class="fas fa-wallet"></i> Company Wallet
                    </a>
                    <a class="nav-link active" href="withdrawal_requests.php">
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
                        <span class="navbar-brand">Withdrawal Requests</span>
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
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Current Balance</th>
                                        <th>Bank Details</th>
                                        <th>Requested</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($req = mysqli_fetch_assoc($requests_result)): ?>
                                    <tr>
                                        <td><?php echo $req['id']; ?></td>
                                        <td>
                                            <strong><?php echo $req['username']; ?></strong><br>
                                            <small class="text-muted"><?php echo $req['email']; ?></small><br>
                                            <small class="text-muted"><?php echo $req['mobile']; ?></small>
                                        </td>
                                        <td><strong class="text-danger">₹<?php echo number_format($req['amount'], 2); ?></strong></td>
                                        <td>₹<?php echo number_format($req['wallet_balance'], 2); ?></td>
                                        <td>
                                            <?php if($req['bank_account_no']): ?>
                                                <small>
                                                    <strong>A/C:</strong> <?php echo $req['bank_account_no']; ?><br>
                                                    <strong>Name:</strong> <?php echo $req['bank_account_name']; ?><br>
                                                    <strong>IFSC:</strong> <?php echo $req['ifsc_code']; ?><br>
                                                    <strong>Bank:</strong> <?php echo $req['bank_name']; ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if($req['upi_id']): ?>
                                                <br><small><strong>UPI:</strong> <?php echo $req['upi_id']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M Y H:i', strtotime($req['requested_at'])); ?></td>
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
                                        <td>
                                            <?php if($req['status'] == 'pending'): ?>
                                                <button type="button" class="btn btn-success btn-sm mb-1" 
                                                        onclick="processWithdrawal(<?php echo $req['id']; ?>, 'approve')">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm mb-1" 
                                                        data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $req['id']; ?>">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal<?php echo $req['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reject Withdrawal</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                                    <input type="hidden" name="action" value="reject">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Rejection Reason</label>
                                                                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="process_withdrawal" class="btn btn-danger">Reject Request</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <small class="text-muted">Processed</small>
                                            <?php endif; ?>
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
    <script>
        function processWithdrawal(requestId, action) {
            if (confirm('Are you sure you want to approve this withdrawal? Amount will be deducted from user wallet.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="request_id" value="${requestId}">
                    <input type="hidden" name="action" value="${action}">
                    <input type="hidden" name="process_withdrawal" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>