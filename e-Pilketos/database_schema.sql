CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','siswa') NOT NULL DEFAULT 'siswa',
  `has_voted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_ketua` varchar(100) NOT NULL,
  `nama_wakil` varchar(100) NOT NULL,
  `visi` text,
  `misi` text,
  `foto` varchar(255) DEFAULT NULL,
  `votes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('admin', '$2y$10$IHA8Y2v9wLSgOs885vT1d.w/CSaA88VIp2eTVz2s49k8iMldK2f0i', 'Administrator', 'admin');

INSERT INTO `candidates` (`id`, `nama_ketua`, `nama_wakil`, `visi`, `misi`, `foto`, `votes`) VALUES
(1, 'Budi Santoso', 'Siti Aminah', 'Mewujudkan sekolah yang unggul dan berprestasi.', '1. Meningkatkan kualitas akademik.\r\n2. Mengembangkan bakat non-akademik.', NULL, 0),
(2, 'Ahmad Fauzi', 'Rina Wati', 'Menjadikan OSIS sebagai wadah aspirasi siswa yang aktif dan kreatif.', '1. Mengadakan acara rutin.\r\n2. Menjalin komunikasi yang baik dengan siswa.', NULL, 0);