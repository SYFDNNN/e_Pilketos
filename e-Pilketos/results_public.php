<?php
session_start(); 
require_once 'includes/db_connect.php';

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
$base_colors = ['#4A55A2', '#7895CB', '#A2B3D8', '#6A82C7', '#B0C4DE']; 

foreach ($candidate_votes as $index => $candidate) {
    $chart_labels[] = $candidate['nama_ketua'] . ' & ' . $candidate['nama_wakil'];
    $chart_data[] = $candidate['votes'];
    $chart_colors[] = $base_colors[$index % count($base_colors)]; 
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemilihan - e-Pilketos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'%3E%3C/path%3E%3C/svg%3E" type="image/svg+xml">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="vote_page.php">Kandidat</a></li>
                <li><a href="results_public.php" class="active">Hasil</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="index.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
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
                            <div class="progress-bar" style="width: <?php echo $candidate['percentage']; ?>%; background-color: <?php echo $chart_colors[$candidate['id'] - 1]; ?>;"></div>
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
                                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo $chart_colors[$index]; ?>; margin-right: 8px;"></div>
                                <span><?php echo htmlspecialchars($candidate['nama_ketua']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
    <script>
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const chartData = <?php echo json_encode($chart_data); ?>;
        const chartColors = <?php echo json_encode($chart_colors); ?>;

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
