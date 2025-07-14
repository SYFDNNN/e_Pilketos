<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$has_voted = $_SESSION['has_voted']; 
if ($has_voted) {
    $_SESSION['vote_message'] = "<div class='message error'>Anda sudah memberikan suara sebelumnya!</div>";
    header('Location: dashboard.php'); 
    exit();
}

if (isset($_POST['vote']) && isset($_POST['candidate_id'])) {
    $candidate_id = intval($_POST['candidate_id']);

    $conn->begin_transaction();

    try {
        $stmt_check = $conn->prepare("SELECT has_voted FROM users WHERE id = ? FOR UPDATE"); 
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $user_status = $result_check->fetch_assoc();

        if ($user_status['has_voted'] == 1) {
            throw new Exception("Anda sudah memberikan suara.");
        }

        $stmt_candidate = $conn->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
        $stmt_candidate->bind_param("i", $candidate_id);
        if (!$stmt_candidate->execute()) {
            throw new Exception("Gagal update suara kandidat: " . $stmt_candidate->error);
        }

        $stmt_user = $conn->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        if (!$stmt_user->execute()) {
            throw new Exception("Gagal update status voting user: " . $stmt_user->error);
        }

        $conn->commit();
        $_SESSION['has_voted'] = 1; // Update session
        $_SESSION['vote_message'] = "<div class='message success'>Terima kasih, suara Anda berhasil dicatat!</div>";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['vote_message'] = "<div class='message error'>Gagal mencatat suara: " . $e->getMessage() . "</div>";
    } finally {
        if (isset($stmt_check) && $stmt_check) $stmt_check->close();
        if (isset($stmt_candidate) && $stmt_candidate) $stmt_candidate->close();
        if (isset($stmt_user) && $stmt_user) $stmt_user->close();
    }
} else {
    $_SESSION['vote_message'] = "<div class='message error'>Pilihan tidak valid.</div>";
}

header('Location: dashboard.php'); 
exit();
