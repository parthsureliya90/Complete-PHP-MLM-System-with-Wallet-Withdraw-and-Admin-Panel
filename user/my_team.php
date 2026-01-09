<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Get direct referrals
$referrals_query = "SELECT u.*, 
                    (SELECT COUNT(*) FROM users WHERE referred_by = u.id) as sub_referrals
                    FROM users u 
                    WHERE u.referred_by = $user_id 
                    ORDER BY u.created_at DESC";
$referrals_result = mysqli_query($conn, $referrals_query);

// Get user's own info
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Function to get all downline users recursively
function getDownline($conn, $user_id, $level = 1, $max_level = 5) {
    if ($level > $max_level) return [];
    
    $query = "SELECT u.*, 
              (SELECT COUNT(*) FROM users WHERE referred_by = u.id) as direct_referrals
              FROM users u 
              WHERE u.referred_by = $user_id 
              ORDER BY u.created_at DESC";
    $result = mysqli_query($conn, $query);
    
    $downline = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['level'] = $level;
        $row['children'] = getDownline($conn, $row['id'], $level + 1, $max_level);
        $downline[] = $row;
    }
    
    return $downline;
}

$tree_data = getDownline($conn, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Team - MLM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .sidebar .nav-link { color: #ecf0f1; padding: 12px 20px; margin: 5px 0; border-radius: 5px; }
        .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); }
        
        .tree { padding: 20px; }
        .tree ul { padding-left: 30px; list-style: none; position: relative; }
        .tree li { position: relative; padding: 10px 0; }
        .tree li::before { content: ''; position: absolute; top: 0; left: -20px; border-left: 2px solid #ddd; height: 100%; }
        .tree li::after { content: ''; position: absolute; top: 25px; left: -20px; border-top: 2px solid #ddd; width: 20px; }
        .tree li:last-child::before { height: 25px; }
        .user-node { padding: 15px; background: white; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 5px 0; }
        .user-node.level-1 { border-left: 4px solid #3498db; }
        .user-node.level-2 { border-left: 4px solid #27ae60; }
        .user-node.level-3 { border-left: 4px solid #f39c12; }
        .user-node.level-4 { border-left: 4px solid #e74c3c; }
        .user-node.level-5 { border-left: 4px solid #9b59b6; }
        .root-node { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
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
                    <a class="nav-link active" href="my_team.php">
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
                        <span class="navbar-brand">My Team Network</span>
                    </div>
                </nav>

                <!-- Root User -->
                <div class="root-node text-center">
                    <h4><i class="fas fa-user-circle"></i> <?php echo $user['username']; ?> (You)</h4>
                    <p class="mb-0">Referral Code: <strong><?php echo $user['referral_code']; ?></strong></p>
                </div>

                <!-- Direct Referrals Table -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Direct Referrals (Level 1)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Referral Code</th>
                                        <th>Wallet Balance</th>
                                        <th>Sub-Referrals</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($referrals_result) > 0): ?>
                                        <?php while($ref = mysqli_fetch_assoc($referrals_result)): ?>
                                        <tr>
                                            <td><strong><?php echo $ref['username']; ?></strong></td>
                                            <td><?php echo $ref['email']; ?></td>
                                            <td><span class="badge bg-info"><?php echo $ref['referral_code']; ?></span></td>
                                            <td>₹<?php echo number_format($ref['wallet_balance'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $ref['sub_referrals']; ?> members</span>
                                            </td>
                                            <td>
                                                <?php if($ref['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($ref['created_at'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-info-circle"></i> No direct referrals yet. Share your referral code to build your team!
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Network Tree -->
                <?php if(count($tree_data) > 0): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-sitemap"></i> Network Tree Structure</h5>
                    </div>
                    <div class="card-body">
                        <div class="tree">
                            <ul>
                                <?php
                                function displayTree($members) {
                                    foreach($members as $member) {
                                        echo '<li>';
                                        echo '<div class="user-node level-' . $member['level'] . '">';
                                        echo '<strong>' . $member['username'] . '</strong><br>';
                                        echo '<small class="text-muted">' . $member['email'] . '</small><br>';
                                        echo '<span class="badge bg-primary">Level ' . $member['level'] . '</span> ';
                                        echo '<span class="badge bg-' . ($member['status'] == 'active' ? 'success' : 'warning') . '">' . ucfirst($member['status']) . '</span>';
                                        if($member['direct_referrals'] > 0) {
                                            echo ' <span class="badge bg-secondary">' . $member['direct_referrals'] . ' refs</span>';
                                        }
                                        echo '</div>';
                                        
                                        if(!empty($member['children'])) {
                                            echo '<ul>';
                                            displayTree($member['children']);
                                            echo '</ul>';
                                        }
                                        echo '</li>';
                                    }
                                }
                                displayTree($tree_data);
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>