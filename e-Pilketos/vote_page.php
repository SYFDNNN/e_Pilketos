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

$candidates = [];
$sql = "SELECT id, nama_ketua, nama_wakil, visi, misi, foto FROM candidates ORDER BY id ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
}

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
    <title>Pilih Kandidat - e-Pilketos</title>
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
                <li><a href="dashboard.php">Beranda</a></li>
                <li><a href="vote_page.php" class="active">Kandidat</a></li>
                <li><a href="results_public.php">Hasil</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Pilih Ketua & Wakil Ketua OSIS Pilihanmu!</h1>
        <p>Silakan teliti visi dan misi setiap kandidat sebelum memberikan suara.</p>

        <?php echo $message; ?>

        <?php if ($has_voted): ?>
            <div class="message success">Anda sudah memberikan suara. Terima kasih atas partisipasinya!</div>
            <p style="margin-top: 20px;"><a href="dashboard.php">Kembali ke Dashboard</a></p>
        <?php else: ?>
            <?php if (empty($candidates)): ?>
                <div class="message error">Belum ada kandidat yang terdaftar. Silakan hubungi admin.</div>
            <?php else: ?>
                <div class="candidate-grid">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="candidate-card">
                            <img src="<?php echo htmlspecialchars($candidate['foto'] ?: 'https://placehold.co/120x120/A2B3D8/ffffff?text=KANDIDAT'); ?>" alt="Foto Kandidat <?php echo htmlspecialchars($candidate['nama_ketua']); ?>">
                            <h3><?php echo htmlspecialchars($candidate['nama_ketua'] . ' & ' . $candidate['nama_wakil']); ?></h3>
                            <p><strong>Visi:</strong> <?php echo nl2br(htmlspecialchars($candidate['visi'])); ?></p>
                            <p><strong>Misi:</strong> <?php echo nl2br(htmlspecialchars($candidate['misi'])); ?></p>
                            <form action="process_vote.php" method="POST">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                <button type="submit" name="vote" class="vote-button" onclick="return confirm('Anda yakin ingin memilih kandidat ini? Pilihan tidak dapat diubah!');">Pilih Ini</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
