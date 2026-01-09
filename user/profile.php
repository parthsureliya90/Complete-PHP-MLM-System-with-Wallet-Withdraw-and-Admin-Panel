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

$success = '';
$error = '';

// Handle profile update
if (isset($_POST['update_profile'])) {
    $address = sanitize($conn, $_POST['address']);
    $mobile = sanitize($conn, $_POST['mobile']);
    $bank_account_name = sanitize($conn, $_POST['bank_account_name']);
    $bank_account_no = sanitize($conn, $_POST['bank_account_no']);
    $ifsc_code = sanitize($conn, $_POST['ifsc_code']);
    $bank_name = sanitize($conn, $_POST['bank_name']);
    $upi_id = sanitize($conn, $_POST['upi_id']);
    
    $update_query = "UPDATE users SET 
                    address = '$address',
                    mobile = '$mobile',
                    bank_account_name = '$bank_account_name',
                    bank_account_no = '$bank_account_no',
                    ifsc_code = '$ifsc_code',
                    bank_name = '$bank_name',
                    upi_id = '$upi_id'
                    WHERE id = $user_id";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Profile updated successfully!";
        // Refresh user data
        $user_result = mysqli_query($conn, $user_query);
        $user = mysqli_fetch_assoc($user_result);
    } else {
        $error = "Failed to update profile!";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $pass_query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            if (mysqli_query($conn, $pass_query)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password!";
            }
        } else {
            $error = "New passwords do not match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - MLM System</title>
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
                    <a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a>
                    <a class="nav-link" href="my_team.php"><i class="fas fa-users"></i> My Team</a>
                    <a class="nav-link" href="withdraw.php"><i class="fas fa-money-check-alt"></i> Withdraw</a>
                    <a class="nav-link active" href="profile.php"><i class="fas fa-user-cog"></i> Profile</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <div class="col-md-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">Profile Settings</span>
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

                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user-edit"></i> Update Profile</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mobile Number</label>
                                            <input type="text" class="form-control" name="mobile" value="<?php echo $user['mobile']; ?>" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" name="address" rows="2" required><?php echo $user['address']; ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    <h5 class="mb-3">Bank Details</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account Holder Name</label>
                                            <input type="text" class="form-control" name="bank_account_name" value="<?php echo $user['bank_account_name']; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account Number</label>
                                            <input type="text" class="form-control" name="bank_account_no" value="<?php echo $user['bank_account_no']; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">IFSC Code</label>
                                            <input type="text" class="form-control" name="ifsc_code" value="<?php echo $user['ifsc_code']; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Bank Name</label>
                                            <input type="text" class="form-control" name="bank_name" value="<?php echo $user['bank_name']; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">UPI ID</label>
                                            <input type="text" class="form-control" name="upi_id" value="<?php echo $user['upi_id']; ?>">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Account Info</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>User ID:</strong> <?php echo $user['id']; ?></p>
                                <p><strong>Referral Code:</strong> <span class="badge bg-primary"><?php echo $user['referral_code']; ?></span></p>
                                <p><strong>Status:</strong> 
                                    <?php if($user['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Inactive</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Member Since:</strong> <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0"><i class="fas fa-key"></i> Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-warning w-100">
                                        <i class="fas fa-lock"></i> Change Password
                                    </button>
                                </form>
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