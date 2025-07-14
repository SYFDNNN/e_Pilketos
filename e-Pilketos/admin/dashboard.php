<?php

session_start();
require_once '../includes/db_connect.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$total_users = 0;
$total_voters = 0;
$candidate_votes = [];

$sql_total_users = "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa'";
$result_total_users = $conn->query($sql_total_users);
if ($result_total_users) {
    $total_users = $result_total_users->fetch_assoc()['total'];
}

$sql_total_voters = "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa' AND has_voted = 1";
$result_total_voters = $conn->query($sql_total_voters);
if ($result_total_voters) {
    $total_voters = $result_total_voters->fetch_assoc()['total'];
}

$sql_candidate_votes = "SELECT nama_ketua, nama_wakil, votes FROM candidates ORDER BY votes DESC";
$result_candidate_votes = $conn->query($sql_candidate_votes);
if ($result_candidate_votes->num_rows > 0) {
    while ($row = $result_candidate_votes->fetch_assoc()) {
        $candidate_votes[] = $row;
    }
}

$percentage_voted = ($total_users > 0) ? round(($total_voters / $total_users) * 100, 2) : 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - e-Pilketos</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'></path>
            </svg>
            Admin Panel
        </a>
        <nav>
            <ul>
                <li><a href="manage_users.php">Kelola Pengguna</a></li>
                <li><a href="manage_candidates.php">Kelola Kandidat</a></li>
                <li><a href="results.php" class="active">Lihat Hasil</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container admin-panel">
        <h1>Admin Panel - e-Pilketos</h1>
        <p>Selamat datang, Administrator <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>

        <hr style="border-color: var(--border-light); margin: 30px 0;">

        <h2>Statistik Pemilihan</h2>
        <p>Total Siswa Terdaftar: <strong><?php echo $total_users; ?></strong></p>
        <p>Siswa Sudah Memilih: <strong><?php echo $total_voters; ?></strong></p>
        <p>Persentase Partisipasi: <strong><?php echo $percentage_voted; ?>%</strong></p>

        <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?php echo $percentage_voted; ?>%;"><?php echo $percentage_voted; ?>%</div>
        </div>

        <h2 style="margin-top: 30px;">Ringkasan Suara Kandidat</h2>
        <?php if (empty($candidate_votes)): ?>
            <p style="color: #666;">Belum ada suara atau kandidat.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Kandidat</th>
                        <th>Jumlah Suara</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidate_votes as $candidate): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($candidate['nama_ketua'] . ' & ' . $candidate['nama_wakil']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['votes']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top: 20px;"><a href="results.php">Lihat Hasil Lengkap</a></p>
        <?php endif; ?>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>

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
                                <?php if ($user['role'] !== 'admin'):  ?>
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
```

<?php
session_start();
require_once '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$message = '';

if (isset($_POST['add_candidate'])) {
    $nama_ketua = $conn->real_escape_string($_POST['nama_ketua']);
    $nama_wakil = $conn->real_escape_string($_POST['nama_wakil']);
    $visi = $conn->real_escape_string($_POST['visi']);
    $misi = $conn->real_escape_string($_POST['misi']);
    $foto_path = '';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../uploads/"; 
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $new_file_name = uniqid('kandidat_') . '.' . $file_extension; 
        $target_file = $target_dir . $new_file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $uploadOk = 1;
        if ($_FILES["foto"]["size"] > 5000000) { 
            $message = "<div class='message error'>Maaf, ukuran foto terlalu besar. Maksimal 5MB.</div>";
            $uploadOk = 0;
        }
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message = "<div class='message error'>Maaf, hanya format JPG, JPEG, PNG & GIF yang diizinkan.</div>";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $foto_path = 'uploads/' . $new_file_name; 
            } else {
                $message = "<div class='message error'>Maaf, ada kesalahan saat mengupload foto Anda.</div>";
            }
        }
    }

    if ($message == '') { 
        $sql = "INSERT INTO candidates (nama_ketua, nama_wakil, visi, misi, foto) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nama_ketua, $nama_wakil, $visi, $misi, $foto_path);

        if ($stmt->execute()) {
            $message = "<div class='message success'>Kandidat berhasil ditambahkan!</div>";
        } else {
            $message = "<div class='message error'>Gagal menambahkan kandidat: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_candidate' && isset($_GET['id'])) {
    $candidate_id_to_delete = intval($_GET['id']);

    $sql_get_photo = "SELECT foto FROM candidates WHERE id = ?";
    $stmt_get_photo = $conn->prepare($sql_get_photo);
    $stmt_get_photo->bind_param("i", $candidate_id_to_delete);
    $stmt_get_photo->execute();
    $result_get_photo = $stmt_get_photo->get_result();
    if ($result_get_photo->num_rows > 0) {
        $row = $result_get_photo->fetch_assoc();
        $file_to_delete = '../' . $row['foto']; // Path relatif dari manage_candidates.php ke file foto
        if (file_exists($file_to_delete) && is_file($file_to_delete)) {
            unlink($file_to_delete); // Hapus file fisik
        }
    }
    $stmt_get_photo->close();

    $stmt_delete = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt_delete->bind_param("i", $candidate_id_to_delete);
    if ($stmt_delete->execute()) {
        $message = "<div class='message success'>Kandidat berhasil dihapus!</div>";
    } else {
        $message = "<div class='message error'>Gagal menghapus kandidat: " . $conn->error . "</div>";
    }
    $stmt_delete->close();
}

$candidates = [];
$sql_candidates = "SELECT id, nama_ketua, nama_wakil, visi, misi, foto, votes FROM candidates ORDER BY id ASC";
$result_candidates = $conn->query($sql_candidates);

if ($result_candidates->num_rows > 0) {
    while ($row = $result_candidates->fetch_assoc()) {
        $candidates[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kandidat - Admin e-Pilketos</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'></path>
            </svg>
            Admin Panel
        </a>
        <nav>
            <ul>
                <li><a href="manage_users.php">Kelola Pengguna</a></li>
                <li><a href="manage_candidates.php" class="active">Kelola Kandidat</a></li>
                <li><a href="results.php">Lihat Hasil</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container admin-panel">
        <h1>Kelola Kandidat OSIS</h1>
        
        <?php echo $message; ?>

        <h2 style="margin-top: 20px;">Tambah Kandidat Baru</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="nama_ketua" style="display: block; text-align: left; margin-bottom: 5px;">Nama Ketua:</label>
            <input type="text" id="nama_ketua" name="nama_ketua" placeholder="Nama Ketua" required>
            <label for="nama_wakil" style="display: block; text-align: left; margin-bottom: 5px;">Nama Wakil:</label>
            <input type="text" id="nama_wakil" name="nama_wakil" placeholder="Nama Wakil" required>
            <label for="visi" style="display: block; text-align: left; margin-bottom: 5px;">Visi:</label>
            <textarea id="visi" name="visi" placeholder="Visi" rows="3" required></textarea>
            <label for="misi" style="display: block; text-align: left; margin-bottom: 5px;">Misi:</label>
            <textarea id="misi" name="misi" placeholder="Misi" rows="5" required></textarea>
            <label for="foto" style="display: block; text-align: left; margin-bottom: 5px;">Foto Kandidat (.jpg, .png, .gif, maks 5MB):</label>
            <input type="file" id="foto" name="foto" accept="image/*">
            <button type="submit" name="add_candidate" style="margin-top: 15px;">Tambah Kandidat</button>
        </form>

        <hr style="border-color: var(--border-light); margin: 30px 0;">

        <h2>Daftar Kandidat</h2>
        <?php if (empty($candidates)): ?>
            <p style="color: #666;">Belum ada kandidat yang terdaftar.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ketua & Wakil</th>
                        <th>Visi</th>
                        <th>Misi</th>
                        <th>Foto</th>
                        <th>Suara</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidates as $candidate): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($candidate['id']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['nama_ketua'] . ' & ' . $candidate['nama_wakil']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars(substr($candidate['visi'], 0, 50))) . (strlen($candidate['visi']) > 50 ? '...' : ''); ?></td>
                            <td><?php echo nl2br(htmlspecialchars(substr($candidate['misi'], 0, 50))) . (strlen($candidate['misi']) > 50 ? '...' : ''); ?></td>
                            <td>
                                <?php if ($candidate['foto'] && file_exists('../' . $candidate['foto'])): ?>
                                    <img src="../<?php echo htmlspecialchars($candidate['foto']); ?>" alt="Foto" width="50" style="border-radius: 4px; border: 1px solid var(--border-light);">
                                <?php else: ?>
                                    Tidak ada
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($candidate['votes']); ?></td>
                            <td>
                                <button onclick="confirmDeleteCandidate(<?php echo $candidate['id']; ?>, '<?php echo htmlspecialchars($candidate['nama_ketua']); ?>')" class="button-danger">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function confirmDeleteCandidate(id, nama) {
            if (confirm(`Anda yakin ingin menghapus kandidat '${nama}'? Ini juga akan mereset suaranya.`)) {
                window.location.href = `manage_candidates.php?action=delete_candidate&id=${id}`;
            }
        }
    </script>
    <script src="../js/script.js"></script>
</body>
</html>

<?php
session_start();
require_once '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$total_users = 0;
$total_voters = 0;
$candidate_votes = [];

$sql_total_users = "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa'";
$result_total_users = $conn->query($sql_total_users);
if ($result_total_users) {
    $total_users = $result_total_users->fetch_assoc()['total'];
}

$sql_total_voters = "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa' AND has_voted = 1";
$result_total_voters = $conn->query($sql_total_voters);
if ($result_total_voters) {
    $total_voters = $result_total_voters->fetch_assoc()['total'];
}

$sql_candidate_votes = "SELECT id, nama_ketua, nama_wakil, votes FROM candidates ORDER BY votes DESC";
$result_candidate_votes = $conn->query($sql_candidate_votes);
if ($result_candidate_votes->num_rows > 0) {
    while ($row = $result_candidate_votes->fetch_assoc()) {
        $candidate_votes[] = $row;
    }
}

$total_valid_votes = array_sum(array_column($candidate_votes, 'votes'));
foreach ($candidate_votes as &$candidate) {
    $candidate['percentage'] = ($total_valid_votes > 0) ? round(($candidate['votes'] / $total_valid_votes) * 100, 2) : 0;
}
unset($candidate);

$percentage_voted = ($total_users > 0) ? round(($total_voters / $total_users) * 100, 2) : 0;


$chart_labels = [];
$chart_data = [];
$chart_colors = [];
$base_colors = ['#4A55A2', '#4CAF50', '#2196F3', '#8BC34A', '#607D8B'];

foreach ($candidate_votes as $index => $candidate) {
    $chart_labels[] = $candidate['nama_ketua'] . ' & ' . $candidate['nama_wakil'];
    $chart_data[] = $candidate['votes'];
    $chart_colors[] = $base_colors[$index % count($base_colors)]; // Pilih warna bergantian
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemilihan - Admin e-Pilketos</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'></path>
            </svg>
            Admin Panel
        </a>
        <nav>
            <ul>
                <li><a href="manage_users.php">Kelola Pengguna</a></li>
                <li><a href="manage_candidates.php">Kelola Kandidat</a></li>
                <li><a href="results.php" class="active">Lihat Hasil</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container admin-panel">
        <h1>Hasil Pemilihan OSIS Terbaru</h1>

        <div class="results-grid">
            <div class="results-card">
                <h3>Ringkasan Suara</h3>
                <?php if (empty($candidate_votes)): ?>
                    <p>Belum ada suara yang tercatat.</p>
                <?php else: ?>
                    <?php foreach ($candidate_votes as $candidate): ?>
                        <div class="candidate-result-item">
                            <span><?php echo htmlspecialchars($candidate['nama_ketua'] . ' (Calon Ketua ' . $candidate['id'] . ')'); ?></span>
                            <span class="vote-percentage"><?php echo $candidate['percentage']; ?>%</span>
                        </div>
                        <div class="progress-bar-container" style="height: 15px; margin-bottom: 15px;">
                            <div class="progress-bar" style="width: <?php echo $candidate['percentage']; ?>%; background-color: <?php echo $base_colors[$candidate['id'] - 1]; ?>;"></div>
                        </div>
                        <p style="text-align: right; font-size: 0.8em; color: #777; margin-top: -10px; margin-bottom: 15px;"><?php echo htmlspecialchars($candidate['votes']); ?> suara</p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="results-card">
                <h3>Visualisasi Hasil</h3>
                <div style="width: 100%; max-width: 400px; margin: 0 auto;">
                    <canvas id="voteChart"></canvas>
                </div>
                <div style="margin-top: 20px; text-align: left;">
                    <?php if (!empty($candidate_votes)): ?>
                        <?php foreach ($candidate_votes as $index => $candidate): ?>
                            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo $base_colors[$index]; ?>; margin-right: 8px;"></div>
                                <span><?php echo htmlspecialchars($candidate['nama_ketua']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
    </div>
    <script src="../js/script.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data dari PHP untuk Chart.js
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const chartData = <?php echo json_encode($chart_data); ?>;
        const chartColors = <?php echo json_encode($chart_colors); ?>;

        // Inisialisasi Chart.js
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('voteChart');

            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Jumlah Suara',
                            data: chartData,
                            backgroundColor: chartColors,
                            borderColor: chartColors.map(color => color.replace(')', ', 0.8)')), 
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, 
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Jumlah Suara'
                                },
                                ticks: {
                                    precision: 0 
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Kandidat'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false 
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.raw + ' suara';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$total_users = 0;
$total_voters = 0;
$candidate_votes = [];

$sql_total_users = "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa'";
$result_total_users = $conn->query($sql_total_users);
if ($result_total_users) {
    $total_users = $result_total_users->fetch_assoc()['total'];
}

$sql_total_voters = "SELECT COUNT(*) AS total FROM users WHERE role = 'siswa' AND has_voted = 1";
$result_total_voters = $conn->query($sql_total_voters);
if ($result_total_voters) {
    $total_voters = $result_total_voters->fetch_assoc()['total'];
}

$sql_candidate_votes = "SELECT nama_ketua, nama_wakil, votes FROM candidates ORDER BY votes DESC";
$result_candidate_votes = $conn->query($sql_candidate_votes);
if ($result_candidate_votes->num_rows > 0) {
    while ($row = $result_candidate_votes->fetch_assoc()) {
        $candidate_votes[] = $row;
    }
}

$percentage_voted = ($total_users > 0) ? round(($total_voters / $total_users) * 100, 2) : 0;

$data = [
    'total_users' => $total_users,
    'total_voters' => $total_voters,
    'percentage_voted' => $percentage_voted,
    'candidate_votes' => $candidate_votes
];

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
