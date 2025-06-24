<?php
session_start();
include 'includes/api_helper.php'; // Sertakan API helper

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Panggil API untuk otentikasi
    $auth_response = callApi('auth', 'POST', ['username' => $username, 'password' => $password]);

    if ($auth_response && $auth_response['status'] === 'success') {
        $_SESSION['user_id'] = $auth_response['user']['id'];
        $_SESSION['username'] = $auth_response['user']['username'];
        $_SESSION['user_role'] = $auth_response['user']['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = $auth_response['message'] ?? "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login KBGM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="mb-4 text-center">Login Admin KBGM</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="usernameInput" class="form-label">Username</label>
                    <input type="text" name="username" id="usernameInput" class="form-control" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="passwordInput" class="form-label">Password</label>
                    <input type="password" name="password" id="passwordInput" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>