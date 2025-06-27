<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = getCurrentUserId();
$error = '';
$success = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);

    if (empty($new_username)) {
        $error = 'Username is required.';
    } else {
        $params = ['username' => $new_username, 'user_id' => $user_id];
        $sql = "UPDATE users SET username = :username";

        $updated = false;

        if ($new_username !== $user['username']) {
            $updated = true;
        }

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $params['password'] = $hashed_password;
            $updated = true;
        }

        $sql .= " WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);

        if ($updated && $stmt->execute($params)) {
            $_SESSION['username'] = $new_username;
            $success = 'Username or password has been updated.';
        } elseif (!$updated) {
            $error = 'No changes detected.';
        } else {
            $error = 'Failed to update profile.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message, .success-message {
            text-align: center;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
        .home-button {
            margin-top: 15px;
            text-align: center;
        }
        .home-button a {
            display: inline-block;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
        }
        .home-button a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1>Edit Profile</h1>

    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password">
        </div>

        <button type="submit">Update Profile</button>
    </form>

    <?php if ($success): ?>
        <div class="home-button">
            <a href="index.php">Back to Home Page</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
