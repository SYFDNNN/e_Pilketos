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
