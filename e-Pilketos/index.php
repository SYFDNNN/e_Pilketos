<?php
session_start();
require_once 'includes/db_connect.php';

$message = '';

if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role, has_voted FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['has_voted'] = $user['has_voted'];

            if ($user['role'] == 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $message = "<div class='message error'>Username atau password salah!</div>";
        }
    } else {
        $message = "<div class='message error'>Username atau password salah!</div>";
    }
    $stmt->close();
}

if (isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $nama_lengkap = $conn->real_escape_string($_POST['reg_nama_lengkap']);

    $check_sql = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "<div class='message error'>Username sudah terdaftar!</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, 'siswa')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $username, $hashed_password, $nama_lengkap);

        if ($insert_stmt->execute()) {
            $message = "<div class='message success'>Registrasi berhasil! Silakan login.</div>";
        } else {
            $message = "<div class='message error'>Registrasi gagal: " . $conn->error . "</div>";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Registrasi e-Pilketos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'%3E%3C/path%3E%3C/svg%3E" type="image/svg+xml">
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">
            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style="height: 30px; margin-right: 10px;">
                <path d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'></path>
            </svg>
            Pemilihan OSIS
        </a>
        <nav>
            <ul>
                <li><a href="dashboard.php">Beranda</a></li>
                <li><a href="vote_page.php">Kandidat</a></li>
                <li><a href="results_public.php">Hasil</a></li>
                <li><a href="index.php" class="active">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Login Akun</h1>

        <?php echo $message; ?>

        <form id="loginForm" method="POST" action="">
            <label for="username" style="display: block; text-align: left; margin-bottom: 5px;">Username:</label>
            <input type="text" id="username" name="username" placeholder="NISN / Username" required>
            <label for="password" style="display: block; text-align: left; margin-bottom: 5px;">Password:</label>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>

        <p style="margin-top: 20px;">Belum punya akun? <a href="#" onclick="showRegisterForm()">Daftar di sini.</a></p>

        <div id="registerFormContainer" style="display: none; margin-top: 30px;">
            <hr style="border-color: var(--border-light); margin: 30px 0;">
            <h2>Registrasi Siswa Baru</h2>
            <form id="registerForm" method="POST" action="">
                <label for="reg_nama_lengkap" style="display: block; text-align: left; margin-bottom: 5px;">Nama Lengkap:</label>
                <input type="text" id="reg_nama_lengkap" name="reg_nama_lengkap" placeholder="Nama Lengkap" required>
                <label for="reg_username" style="display: block; text-align: left; margin-bottom: 5px;">NISN (Username):</label>
                <input type="text" id="reg_username" name="reg_username" placeholder="NISN (Username)" required>
                <label for="reg_password" style="display: block; text-align: left; margin-bottom: 5px;">Password:</label>
                <input type="password" id="reg_password" name="reg_password" placeholder="Password" required>
                <button type="submit" name="register">Registrasi</button>
            </form>
        </div>
    </div>
    <script>
        function showRegisterForm() {
            document.getElementById('registerFormContainer').style.display = 'block';
        }
    </script>
    <script src="js/script.js"></script>
</body>
</html>
