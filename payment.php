<?php
require_once 'config.php';

if (!isset($_SESSION['registration_success'])) {
    redirect('register.php');
}

unset($_SESSION['registration_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - MLM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .payment-card { border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .upi-qr { max-width: 300px; margin: 20px auto; }
        .amount-box { background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card payment-card">
                    <div class="card-header bg-success text-white text-center py-3">
                        <h3><i class="fas fa-check-circle"></i> Registration Successful!</h3>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Please complete the payment to activate your account
                        </div>
                        
                        <div class="amount-box">
                            <h4 class="text-primary mb-0">Joining Amount</h4>
                            <h1 class="display-4 text-success">₹1000</h1>
                        </div>
                        
                        <div class="upi-qr">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=upi://pay?pa=merchant@upi&pn=MLM%20System&am=1000&cu=INR" 
                                 alt="UPI QR Code" class="img-fluid border rounded">
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-mobile-alt"></i> Scan this QR code using any UPI app
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-muted">UPI ID: <strong>merchant@upi</strong></p>
                            <p class="text-muted">After payment, admin will verify and activate your account</p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Proceed to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>