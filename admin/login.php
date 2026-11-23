<?php
/**
 * 3D Apparel Marketplace — Standalone Admin Authentication
 * Clean, secure, self-contained admin login with CSRF and rate-limiting.
 */

require_once __DIR__ . '/../config.php';

// If already logged in, redirect directly to admin dashboard
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success = 'Anda telah berhasil keluar dari sistem admin.';
}

// Rate limiting using session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Reset attempts after 15 minutes
if (time() - ($_SESSION['last_attempt_time'] ?? 0) > 900) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check brute force limit (max 5 failed attempts in 15 min)
    if ($_SESSION['login_attempts'] >= 5) {
        $waitMinutes = ceil((900 - (time() - $_SESSION['last_attempt_time'])) / 60);
        $error = "Terlalu banyak percobaan gagal. Silakan coba lagi dalam {$waitMinutes} menit.";
    } else {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verifyMarketplaceCsrfToken($csrf)) {
            $error = 'Sesi formulir keamanan kedaluwarsa. Silakan muat ulang halaman.';
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = 'Username dan password wajib diisi.';
            } else {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['password_hash'])) {
                        // Success: Reset rate limiting and establish session
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['tnm_admin_user'] = [
                            'id'       => $user['id'],
                            'username' => $user['username'],
                            'name'     => $user['name']
                        ];

                        // Safe redirect parameter validation (must be relative path to prevent open redirects)
                        $redirect = $_GET['redirect'] ?? 'index.php';
                        if (preg_match('~^(https?:|//)~i', $redirect) || strpos($redirect, '\\') !== false) {
                            $redirect = 'index.php';
                        }

                        header('Location: ' . $redirect);
                        exit;
                    } else {
                        $_SESSION['login_attempts']++;
                        $_SESSION['last_attempt_time'] = time();
                        $remaining = 5 - $_SESSION['login_attempts'];
                        $error = $remaining > 0 
                            ? "Kredensial tidak valid. Sisa percobaan: {$remaining} kali."
                            : "Kredensial tidak valid. Akun terkunci sementara selama 15 menit.";
                    }
                } catch (Throwable $e) {
                    $error = 'Terjadi kesalahan sistem database: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Login Admin — 3D Apparel Marketplace</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="../assets/css/marketplace.css">
  
  <style>
    body {
      background: var(--bg);
      color: var(--ink);
      min-height: 100vh;
      display: grid;
      place-items: center;
      margin: 0;
      padding: 24px;
      box-sizing: border-box;
      font-family: var(--font-body, 'Plus Jakarta Sans', sans-serif);
    }

    .login-card {
      width: 100%;
      max-width: 420px;
      background: var(--surface);
      border: 1px solid var(--ring);
      border-radius: var(--radius-lg, 20px);
      padding: 40px 32px;
      box-shadow: 0 24px 48px rgba(0, 0, 0, 0.45);
      position: relative;
      overflow: hidden;
    }

    .login-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--red, #d63b29), #f07361);
    }

    .login-header {
      text-align: center;
      margin-bottom: 28px;
    }

    .login-header .mark {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-family: var(--font-display, 'Archivo Black', sans-serif);
      font-size: 26px;
      color: var(--ink);
      text-decoration: none;
      margin-bottom: 8px;
    }

    .login-header .mark i {
      display: inline-block;
      width: 8px;
      height: 8px;
      background: var(--red, #d63b29);
      border-radius: 50%;
    }

    .login-header .mark-sub {
      font-size: 11px;
      font-weight: 800;
      background: rgba(214, 59, 41, 0.15);
      color: var(--red, #d63b29);
      padding: 2px 8px;
      border-radius: 999px;
      margin-left: 6px;
      letter-spacing: 0.08em;
    }

    .login-header p {
      color: var(--muted);
      font-size: 13.5px;
      margin: 6px 0 0;
    }

    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }

    .form-label {
      display: block;
      font-size: 12.5px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .form-input {
      width: 100%;
      background: var(--bg);
      border: 1px solid var(--ring);
      border-radius: var(--radius-sm, 10px);
      padding: 13px 16px;
      color: var(--ink);
      font-size: 14.5px;
      box-sizing: border-box;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input:focus {
      border-color: var(--red, #d63b29);
      box-shadow: 0 0 0 3px rgba(214, 59, 41, 0.2);
    }

    .alert {
      padding: 12px 16px;
      border-radius: var(--radius-sm, 10px);
      font-size: 13.5px;
      line-height: 1.5;
      margin-bottom: 20px;
    }

    .alert-error {
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.25);
      color: #f87171;
    }

    .alert-success {
      background: rgba(34, 197, 94, 0.12);
      border: 1px solid rgba(34, 197, 94, 0.25);
      color: #4ade80;
    }

    .btn-submit {
      width: 100%;
      background: var(--red, #d63b29);
      color: #ffffff;
      border: none;
      border-radius: 999px;
      padding: 14px 24px;
      font-size: 14.5px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: transform 0.15s, background-color 0.2s;
    }

    .btn-submit:hover {
      background: #c0301f;
      transform: translateY(-1px);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    .login-footer {
      text-align: center;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--ring);
      font-size: 12.5px;
      color: var(--muted);
    }

    .login-footer a {
      color: var(--red, #d63b29);
      text-decoration: none;
      font-weight: 700;
    }

    .login-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <div class="login-header">
      <a href="../index.php" class="mark">
        TNM<i></i>
        <span class="mark-sub">ADMIN</span>
      </a>
      <p>Masuk ke Panel Administrasi Toko & Studio 3D</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success">
        <?= e($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= e(getMarketplaceCsrfToken()) ?>">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username" required autocomplete="username" autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn-submit">
        <span>Masuk ke Dashboard</span>
      </button>
    </form>

    <div class="login-footer">
      <a href="../index.php">&larr; Kembali ke Toko Depan</a>
    </div>
  </div>

</body>
</html>
