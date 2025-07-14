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
        if ($_FILES["foto"]["size"] > 5000000) { // 5MB
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

    if ($message == '') { // Hanya insert jika tidak ada error upload
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
        $file_to_delete = '../' . $row['foto'];
        if (file_exists($file_to_delete) && is_file($file_to_delete)) {
            unlink($file_to_delete);
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
