<?php
require_once 'config.php';

$error = '';
$success = '';
$referred_by_code = isset($_GET['ref']) ? sanitize($conn, $_GET['ref']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($conn, $_POST['username']);
    $email = sanitize($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = sanitize($conn, $_POST['address']);
    $mobile = sanitize($conn, $_POST['mobile']);
    $bank_account_name = sanitize($conn, $_POST['bank_account_name']);
    $bank_account_no = sanitize($conn, $_POST['bank_account_no']);
    $ifsc_code = sanitize($conn, $_POST['ifsc_code']);
    $bank_name = sanitize($conn, $_POST['bank_name']);
    $upi_id = sanitize($conn, $_POST['upi_id']);
    $referral_code = generateReferralCode();
    $referred_by_code = sanitize($conn, $_POST['referred_by']);
    
    // Check if username or email exists
    $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Username or email already exists!";
    } else {
        // Get referrer ID if referral code exists
        $referred_by_id = NULL;
        if (!empty($referred_by_code)) {
            $ref_query = "SELECT id FROM users WHERE referral_code = '$referred_by_code' AND status = 'active'";
            $ref_result = mysqli_query($conn, $ref_query);
            if (mysqli_num_rows($ref_result) > 0) {
                $ref_row = mysqli_fetch_assoc($ref_result);
                $referred_by_id = $ref_row['id'];
            }
        }
        
        // Insert user
        $insert_query = "INSERT INTO users (username, email, password, address, mobile, 
                        bank_account_name, bank_account_no, ifsc_code, bank_name, upi_id, 
                        referral_code, referred_by) 
                        VALUES ('$username', '$email', '$password', '$address', '$mobile', 
                        '$bank_account_name', '$bank_account_no', '$ifsc_code', '$bank_name', 
                        '$upi_id', '$referral_code', " . ($referred_by_id ? $referred_by_id : "NULL") . ")";
        
        if (mysqli_query($conn, $insert_query)) {
            $success = "Registration successful! Please wait for admin approval.";
            $_SESSION['registration_success'] = true;
            redirect('payment.php');
        } else {
            $error = "Registration failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - MLM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .register-card { margin-top: 50px; margin-bottom: 50px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card register-card">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3><i class="fas fa-user-plus"></i> User Registration</h3>
                        <p class="mb-0">Join our MLM network - Joining Amount: ₹1000</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile Number *</label>
                                    <input type="text" class="form-control" name="mobile" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Address *</label>
                                    <textarea class="form-control" name="address" rows="2" required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Referral Code (Optional)</label>
                                    <input type="text" class="form-control" name="referred_by" value="<?php echo $referred_by_code; ?>">
                                </div>
                            </div>
                            
                            <hr>
                            <h5 class="mb-3">Bank Details (Optional)</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Account Name</label>
                                    <input type="text" class="form-control" name="bank_account_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Account Number</label>
                                    <input type="text" class="form-control" name="bank_account_no">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">IFSC Code</label>
                                    <input type="text" class="form-control" name="ifsc_code">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" name="bank_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">UPI ID</label>
                                    <input type="text" class="form-control" name="upi_id">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle"></i> Register Now
                                </button>
                                <a href="login.php" class="btn btn-outline-secondary">
                                    Already have an account? Login
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>