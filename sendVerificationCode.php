<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

$conn = require "database.php";

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $code = rand(100000, 999999);

    $insertCodeQuery = "INSERT INTO email_verification (email, code, created_at) VALUES ('$email', '$code', NOW()) 
                        ON DUPLICATE KEY UPDATE code='$code', created_at=NOW()";

    if (mysqli_query($conn, $insertCodeQuery)) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ddetkursi@gmail.com';
            $mail->Password = 'nhfw ewab xzts amcf';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('ddetkursi@gmail.com', 'Crystal Dental');
            $mail->addAddress($email);
            $mail->Subject = 'Your Verification Code';

            $header = '<div style="background-color: #4a3b7c; color: white; padding: 30px; text-align: center; font-family: Arial, sans-serif; border-radius: 10px;">
                    <h2 style="margin: 0; font-size: 2rem;">Crystal Dental Newsletter</h2>
                    <p style="margin: 0; font-size: 1.2rem; font-weight: bold;">Smile bright, live right!</p>
                </div>';

            $footer = '<div style="background-color: #e3daf5; color: #4a3b7c; padding: 20px; text-align: center; font-family: Arial, sans-serif; border-top: 2px solid #4a3b7c;">
                    <p style="margin: 0; font-size: 1rem;">&copy; ' . date('Y') . ' Crystal Dental. All rights reserved.</p>
                </div>';

            $mail->isHTML(true);
            $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #f6f0fc; margin: 0; padding: 0;'>
                $header
                <div style='padding: 20px; margin: 20px auto; border-radius: 10px; max-width: 600px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                    <h3 style='color: #424874; text-align: center;'>Your Verification Code</h3>
                    <p style='color: #424874; text-align: center; font-size: 1.2rem; margin-bottom: 20px;'>Your verification code is: <strong>$code</strong></p>
                    <div style='text-align: center; margin-top: 20px;'>
                        <img src='cid:logo_cid' alt='Logo' width='225' height='175' style='max-width: 100%; height: auto;' />
                    </div>
                </div>
                $footer
            </body>
            </html>
            ";

            $mail->addEmbeddedImage('C:\xampp\htdocs\DetyreKursi\images\crystal-dental-logo.png', 'logo_cid');
            $mail->send();

            echo json_encode(["success" => true, "message" => "Verification code sent to $email."]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Failed to send verification email. Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to generate verification code."]);
    }
}
