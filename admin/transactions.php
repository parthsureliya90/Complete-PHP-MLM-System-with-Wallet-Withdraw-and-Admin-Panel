<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Filter by date if provided
$date_filter = isset($_GET['date']) ? sanitize($conn, $_GET['date']) : '';
$where_clause = $date_filter ? "WHERE DATE(t.created_at) = '$date_filter'" : '';

// Get transactions
$trans_query = "SELECT t.*, u.username, u.email 
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                $where_clause
                ORDER BY t.created_at DESC
                LIMIT 100";
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
                    <a class="nav-link active" href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
                    <a class="nav-link" href="company_transactions.php"><i class="fas fa-building"></i> Company Transactions</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <div class="col-md-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">User Transactions</span>
                    </div>
                </nav>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filter by Date</label>
                                <input type="date" class="form-control" name="date" value="<?php echo $date_filter; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <a href="transactions.php" class="btn btn-secondary w-100"><i class="fas fa-redo"></i> Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date & Time</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($trans = mysqli_fetch_assoc($trans_result)): ?>
                                    <tr>
                                        <td><?php echo $trans['id']; ?></td>
                                        <td><?php echo date('d M Y H:i:s', strtotime($trans['created_at'])); ?></td>
                                        <td>
                                            <strong><?php echo $trans['username']; ?></strong><br>
                                            <small class="text-muted"><?php echo $trans['email']; ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_color = '';
                                            $type_label = '';
                                            switch($trans['transaction_type']) {
                                                case 'joining_bonus':
                                                    $badge_color = 'primary';
                                                    $type_label = 'Joining Bonus';
                                                    break;
                                                case 'referral_bonus':
                                                    $badge_color = 'success';
                                                    $type_label = 'Referral Bonus';
                                                    break;
                                                case 'withdrawal':
                                                    $badge_color = 'danger';
                                                    $type_label = 'Withdrawal';
                                                    break;
                                                default:
                                                    $badge_color = 'secondary';
                                                    $type_label = ucfirst(str_replace('_', ' ', $trans['transaction_type']));
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $badge_color; ?>"><?php echo $type_label; ?></span>
                                        </td>
                                        <td><?php echo $trans['description']; ?></td>
                                        <td>
                                            <strong class="<?php echo $trans['amount'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $trans['amount'] > 0 ? '+' : ''; ?>₹<?php echo number_format($trans['amount'], 2); ?>
                                            </strong>
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