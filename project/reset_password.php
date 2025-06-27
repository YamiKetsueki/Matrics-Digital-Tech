<?php
// Simulate user lookup by username or email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['username_or_email'];

    // Simulate user data (replace this with actual DB lookup)
    $user = ['email' => 'user@example.com', 'username' => 'user123'];

    if ($user) {
        // Generate a random token (this would usually be unique for each user)
        $token = bin2hex(random_bytes(32));  // Generate a random token (64 chars)
        $resetLink = "http://localhost/project/set_new_password.php?token=$token"; // Simulate reset link

        // Instead of showing the link, redirect the user to a confirmation page
        header("Location: reset_confirmation.php?email=" . urlencode($user['email']));
        exit();
    } else {
        echo "No account found for $input.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .message-box {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            outline: none;
        }

        .input-group input:focus {
            border-color: #007bff;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .footer {
            margin-top: 20px;
            font-size: 14px;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="message-box">
        <h2>Password Reset Request</h2>

        <form action="reset_password.php" method="POST">
            <div class="input-group">
                <input type="text" name="username_or_email" placeholder="Enter your username or email" required>
            </div>
            <button type="submit">Send Reset Link</button>
        </form>

        <p class="footer">Back to <a href="login.php">Login</a></p>
    </div>

</body>
</html>
