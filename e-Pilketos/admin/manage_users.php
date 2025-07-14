<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$message = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['id'])) {
    $user_id_to_delete = intval($_GET['id']);
    $stmt_check_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_check_role->bind_param("i", $user_id_to_delete);
    $stmt_check_role->execute();
    $result_check_role = $stmt_check_role->get_result();
    if ($result_check_role->num_rows > 0 && $result_check_role->fetch_assoc()['role'] == 'admin') {
        $message = "<div class='message error'>Tidak bisa menghapus akun admin!</div>";
    } else {
        $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_delete->bind_param("i", $user_id_to_delete);
        if ($stmt_delete->execute()) {
            $message = "<div class='message success'>Pengguna berhasil dihapus!</div>";
        } else {
            $message = "<div class='message error'>Gagal menghapus pengguna: " . $conn->error . "</div>";
        }
        $stmt_delete->close();
    }
    $stmt_check_role->close();
}

$users = [];
$sql_users = "SELECT id, username, nama_lengkap, role, has_voted FROM users WHERE id != ?";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("i", $_SESSION['user_id']);
$stmt_users->execute();
$result_users = $stmt_users->get_result();

if ($result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt_users->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin e-Pilketos</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'></path>
            </svg>
            Admin Panel
        </a>
        <nav>
            <ul>
                <li><a href="manage_users.php" class="active">Kelola Pengguna</a></li>
                <li><a href="manage_candidates.php">Kelola Kandidat</a></li>
                <li><a href="results.php">Lihat Hasil</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container admin-panel">
        <h1>Kelola Pengguna</h1>
        
        <?php echo $message; ?>

        <h2 style="margin-top: 20px;">Daftar Pengguna</h2>
        <?php if (empty($users)): ?>
            <p style="color: #666;">Tidak ada pengguna lain terdaftar.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username (NISN)</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Sudah Memilih</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['has_voted'] ? 'Ya' : 'Tidak'; ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <button onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" class="button-danger">Hapus</button>
                                <?php else: ?>
                                    <button disabled style="opacity: 0.5; cursor: not-allowed;">Admin</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete(id, username) {
            if (confirm(`Anda yakin ingin menghapus pengguna '${username}'?`)) {
                window.location.href = `manage_users.php?action=delete_user&id=${id}`;
            }
        }
    </script>
    <script src="../js/script.js"></script>
</body>
</html>
