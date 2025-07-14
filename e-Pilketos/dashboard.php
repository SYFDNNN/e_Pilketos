<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$stmt_user_status = $conn->prepare("SELECT has_voted FROM users WHERE id = ?");
$stmt_user_status->bind_param("i", $user_id);
$stmt_user_status->execute();
$result_user_status = $stmt_user_status->get_result();
$user_data = $result_user_status->fetch_assoc();
$_SESSION['has_voted'] = $user_data['has_voted'];
$has_voted = $user_data['has_voted'];
$stmt_user_status->close();

$message = '';
if (isset($_SESSION['vote_message'])) {
    $message = $_SESSION['vote_message'];
    unset($_SESSION['vote_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - e-Pilketos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'%3E%3C/path%3E%3C/svg%3E" type="image/svg+xml">
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="logo">
            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style="height: 30px; margin-right: 10px;">
                <path d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'></path>
            </svg>
            Pemilihan OSIS
        </a>
        <nav>
            <ul>
                <li><a href="dashboard.php" class="active">Beranda</a></li>
                <li><a href="vote_page.php">Kandidat</a></li>
                <li><a href="results_public.php">Hasil</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard-hero">
            <h1>Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Platform pemilihan ketua OSIS yang transparan, partisipatif, dan modern untuk masa depan sekolah yang lebih cerah.</p>
        </div>

        <?php echo $message; ?>

        <div class="dashboard-buttons">
            <?php if (!$has_voted): ?>
                <button onclick="window.location.href='vote_page.php'">Lihat Kandidat</button>
            <?php else: ?>
                <button disabled style="opacity: 0.6; cursor: not-allowed;">Anda Sudah Memilih</button>
            <?php endif; ?>
            <button class="button-secondary" onclick="window.location.href='results_public.php'">Cek Hasil Pemilihan</button>
        </div>

        <p style="margin-top: 30px; color: #666;">Status Pemilihan Anda:
            <?php if ($has_voted): ?>
                <span style="color: var(--success-color); font-weight: bold;">Sudah Memberikan Suara</span>
            <?php else: ?>
                <span style="color: var(--error-color); font-weight: bold;">Belum Memberikan Suara</span>
            <?php endif; ?>
        </p>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
