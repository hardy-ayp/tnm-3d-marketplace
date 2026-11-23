<?php
/**
 * 3D Apparel Marketplace — Standalone Admin Change Password
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$adminTitle = 'Ganti Password Admin';
$admin = currentAdmin();
$db = getDB();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyMarketplaceCsrfToken($csrf)) {
        $error = 'Sesi keamanan formulir CSRF telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $oldPassword     = $_POST['old_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Semua kolom password wajib diisi.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password baru harus memiliki panjang minimal 8 karakter.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Konfirmasi password baru tidak cocok.';
        } else {
            try {
                // Fetch current user record
                $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ? LIMIT 1");
                $stmt->execute([$admin['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = 'Data pengguna admin tidak ditemukan dalam database.';
                } elseif (!password_verify($oldPassword, $user['password_hash'])) {
                    $error = 'Password saat ini yang Anda masukkan salah.';
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $updateStmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                    $updateStmt->execute([$newHash, $admin['id']]);
                    $success = 'Password admin berhasil diperbarui dengan aman!';
                }
            } catch (Throwable $e) {
                $error = 'Gagal memperbarui password: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 560px; margin: 0 auto; padding-top: 10px;">
  <div style="margin-bottom: 24px;">
    <h1 style="font-family: var(--font-display); font-size: 24px; font-weight: 800; color: var(--ink); margin: 0 0 6px;">
      Ganti Password Administrator
    </h1>
    <p style="color: var(--muted); font-size: 13.5px; margin: 0;">
      Perbarui kata sandi akun administrator untuk menjaga keamanan toko online dan studio 3D.
    </p>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171; padding: 14px 18px; border-radius: var(--radius-sm); font-size: 13.5px; margin-bottom: 20px;">
      <?= e($error) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.25); color: #4ade80; padding: 14px 18px; border-radius: var(--radius-sm); font-size: 13.5px; margin-bottom: 20px;">
      <?= e($success) ?>
    </div>
  <?php endif; ?>

  <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 28px;">
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= e(getMarketplaceCsrfToken()) ?>">

      <div style="margin-bottom: 18px;">
        <label style="display:block; font-size: 12px; font-weight: 700; color: var(--ink); text-transform:uppercase; letter-spacing:0.04em; margin-bottom: 6px;">
          Username
        </label>
        <input type="text" value="<?= e($admin['username'] ?? 'admin') ?>" disabled style="width:100%; background: var(--bg); border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 11px 14px; color: var(--muted); font-size: 14px; box-sizing:border-box;">
      </div>

      <div style="margin-bottom: 18px;">
        <label for="old_password" style="display:block; font-size: 12px; font-weight: 700; color: var(--ink); text-transform:uppercase; letter-spacing:0.04em; margin-bottom: 6px;">
          Password Saat Ini
        </label>
        <input type="password" id="old_password" name="old_password" required placeholder="Masukkan password saat ini" style="width:100%; background: var(--bg); border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 11px 14px; color: var(--ink); font-size: 14px; box-sizing:border-box;">
      </div>

      <div style="margin-bottom: 18px;">
        <label for="new_password" style="display:block; font-size: 12px; font-weight: 700; color: var(--ink); text-transform:uppercase; letter-spacing:0.04em; margin-bottom: 6px;">
          Password Baru (Minimal 8 Karakter)
        </label>
        <input type="password" id="new_password" name="new_password" minlength="8" required placeholder="Masukkan password baru" style="width:100%; background: var(--bg); border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 11px 14px; color: var(--ink); font-size: 14px; box-sizing:border-box;">
      </div>

      <div style="margin-bottom: 24px;">
        <label for="confirm_password" style="display:block; font-size: 12px; font-weight: 700; color: var(--ink); text-transform:uppercase; letter-spacing:0.04em; margin-bottom: 6px;">
          Konfirmasi Password Baru
        </label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required placeholder="Ulangi password baru" style="width:100%; background: var(--bg); border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 11px 14px; color: var(--ink); font-size: 14px; box-sizing:border-box;">
      </div>

      <div style="display:flex; gap:12px; justify-content:flex-end;">
        <a href="index.php" class="btn ghost sm">Batal</a>
        <button type="submit" class="btn red sm">
          <span>Simpan Password Baru</span>
        </button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
