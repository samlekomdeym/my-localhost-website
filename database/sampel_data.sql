-- Sample Data for Campus Management System
-- This file contains sample data for testing purposes

USE `kampus_db`;

-- Sample Mata Kuliah
INSERT INTO `mata_kuliah` (`kode_mata_kuliah`, `nama_mata_kuliah`, `sks`, `semester`, `program_studi`, `deskripsi`) VALUES
('TI101', 'Algoritma dan Pemrograman', 3, 1, 'Teknik Informatika', 'Mata kuliah dasar pemrograman dan algoritma'),
('TI102', 'Matematika Diskrit', 3, 1, 'Teknik Informatika', 'Matematika untuk ilmu komputer'),
('TI103', 'Pengantar Teknologi Informasi', 2, 1, 'Teknik Informatika', 'Pengenalan dasar teknologi informasi'),
('TI201', 'Struktur Data', 3, 2, 'Teknik Informatika', 'Struktur data dan implementasinya'),
('TI202', 'Basis Data', 3, 2, 'Teknik Informatika', 'Konsep dan implementasi basis data'),
('TI203', 'Pemrograman Web', 3, 2, 'Teknik Informatika', 'Pengembangan aplikasi web'),
('SI101', 'Pengantar Sistem Informasi', 3, 1, 'Sistem Informasi', 'Konsep dasar sistem informasi'),
('SI102', 'Analisis dan Perancangan Sistem', 3, 2, 'Sistem Informasi', 'Metodologi pengembangan sistem'),
('TK101', 'Elektronika Digital', 3, 1, 'Teknik Komputer', 'Dasar-dasar elektronika digital'),
('TK102', 'Arsitektur Komputer', 3, 2, 'Teknik Komputer', 'Arsitektur dan organisasi komputer');

-- Sample Dosen Users
-- Password for all sample users is 'password123'
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('dosen1', 'dosen1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'active'),
('dosen2', 'dosen2@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'active'),
('dosen3', 'dosen3@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'active'),
('dosen4', 'dosen4@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'active'),
('dosen5', 'dosen5@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'active');

-- Sample Dosen
INSERT INTO `dosen` (`user_id`, `nidn`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_telepon`, `bidang_keahlian`, `pendidikan_terakhir`, `jabatan_akademik`, `status_kepegawaian`) VALUES
(2, '0101010001', 'Dr. Ahmad Wijaya, M.Kom', 'Jakarta', '1980-05-15', 'L', 'Jl. Merdeka No. 123, Jakarta', '081234567890', 'Algoritma dan Pemrograman', 'S3 Ilmu Komputer', 'Lektor Kepala', 'tetap'),
(3, '0101010002', 'Dr. Siti Nurhaliza, M.T', 'Bandung', '1982-08-20', 'P', 'Jl. Sudirman No. 456, Bandung', '081234567891', 'Basis Data', 'S3 Teknik Informatika', 'Lektor', 'tetap'),
(4, '0101010003', 'Ir. Budi Santoso, M.Kom', 'Surabaya', '1978-12-10', 'L', 'Jl. Pahlawan No. 789, Surabaya', '081234567892', 'Jaringan Komputer', 'S2 Teknik Informatika', 'Asisten Ahli', 'tetap'),
(5, '0101010004', 'Dr. Maya Sari, M.Si', 'Yogyakarta', '1985-03-25', 'P', 'Jl. Malioboro No. 321, Yogyakarta', '081234567893', 'Sistem Informasi', 'S3 Sistem Informasi', 'Lektor', 'tetap'),
(6, '0101010005', 'Drs. Eko Prasetyo, M.T', 'Medan', '1975-11-30', 'L', 'Jl. Gatot Subroto No. 654, Medan', '081234567894', 'Elektronika', 'S2 Teknik Elektro', 'Lektor Kepala', 'tetap');

-- Sample Mahasiswa Users
-- Password for all sample users is 'password123'
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('mahasiswa1', 'mahasiswa1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active'),
('mahasiswa2', 'mahasiswa2@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active'),
('mahasiswa3', 'mahasiswa3@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active'),
('mahasiswa4', 'mahasiswa4@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active'),
('mahasiswa5', 'mahasiswa5@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active'),
('mahasiswa6', 'mahasiswa6@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active'),
('mahasiswa7', 'mahasiswa7@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active'),
('mahasiswa8', 'mahasiswa8@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'active');

-- Sample Mahasiswa
INSERT INTO `mahasiswa` (`user_id`, `nim`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_telepon`, `program_studi`, `tahun_masuk`, `status_akademik`) VALUES
(7, '20240001', 'Andi Pratama', 'Jakarta', '2002-01-15', 'L', 'Jl. Kebon Jeruk No. 123, Jakarta', '081234567895', 'Teknik Informatika', 2024, 'aktif'),
(8, '20240002', 'Sari Dewi', 'Bandung', '2002-03-20', 'P', 'Jl. Dago No. 456, Bandung', '081234567896', 'Teknik Informatika', 2024, 'aktif'),
(9, '20240003', 'Budi Hermawan', 'Surabaya', '2001-12-10', 'L', 'Jl. Pemuda No. 789, Surabaya', '081234567897', 'Sistem Informasi', 2024, 'aktif'),
(10, '20240004', 'Rina Sari', 'Yogyakarta', '2002-05-25', 'P', 'Jl. Kaliurang No. 321, Yogyakarta', '081234567898', 'Sistem Informasi', 2024, 'aktif'),
(11, '20240005', 'Dedi Kurniawan', 'Medan', '2001-11-30', 'L', 'Jl. Sisingamangaraja No. 654, Medan', '081234567899', 'Teknik Komputer', 2024, 'aktif'),
(12, '20230001', 'Lisa Permata', 'Jakarta', '2001-07-18', 'P', 'Jl. Menteng No. 111, Jakarta', '081234567800', 'Teknik Informatika', 2023, 'aktif'),
(13, '20230002', 'Rudi Hartono', 'Bandung', '2001-09-22', 'L', 'Jl. Cihampelas No. 222, Bandung', '081234567801', 'Sistem Informasi', 2023, 'aktif'),
(14, '20230003', 'Nina Anggraini', 'Surabaya', '2001-04-14', 'P', 'Jl. Gubeng No. 333, Surabaya', '081234567802', 'Teknik Komputer', 2023, 'aktif');

-- Sample Jadwal
-- Asumsi tahun_akademik_id akan merujuk ke tabel tahun_akademik.id
-- Untuk saat ini, kolom tahun_akademik dan semester di jadwal adalah string enum, jadi diisi langsung
INSERT INTO `jadwal` (`mata_kuliah_id`, `dosen_id`, `hari`, `jam_mulai`, `jam_selesai`, `ruangan`, `kapasitas`, `tahun_akademik`, `semester`) VALUES
(1, 1, 'Monday', '08:00:00', '10:30:00', 'Lab Komputer 1', 30, '2024/2025', 'Ganjil'),
(2, 2, 'Tuesday', '10:30:00', '12:00:00', 'Ruang 201', 40, '2024/2025', 'Ganjil'),
(3, 3, 'Wednesday', '13:00:00', '14:30:00', 'Ruang 202', 40, '2024/2025', 'Ganjil'),
(4, 1, 'Thursday', '08:00:00', '10:30:00', 'Lab Komputer 2', 30, '2024/2025', 'Ganjil'),
(5, 2, 'Friday', '10:30:00', '12:00:00', 'Lab Database', 25, '2024/2025', 'Ganjil'),
(6, 3, 'Monday', '13:00:00', '15:30:00', 'Lab Komputer 3', 30, '2024/2025', 'Ganjil'),
(7, 4, 'Tuesday', '08:00:00', '10:30:00', 'Ruang 301', 40, '2024/2025', 'Ganjil'),
(8, 4, 'Wednesday', '10:30:00', '12:00:00', 'Ruang 302', 40, '2024/2025', 'Ganjil'),
(9, 5, 'Thursday', '13:00:00', '15:30:00', 'Lab Elektronika', 20, '2024/2025', 'Ganjil'),
(10, 5, 'Friday', '08:00:00', '10:30:00', 'Lab Komputer 4', 25, '2024/2025', 'Ganjil');

-- Sample KRS
INSERT INTO `krs` (`mahasiswa_id`, `jadwal_id`, `tahun_akademik`, `semester`, `status`) VALUES
(1, 1, '2024/2025', 'Ganjil', 'diambil'),
(1, 2, '2024/2025', 'Ganjil', 'diambil'),
(1, 3, '2024/2025', 'Ganjil', 'diambil'),
(2, 1, '2024/2025', 'Ganjil', 'diambil'),
(2, 2, '2024/2025', 'Ganjil', 'diambil'),
(2, 6, '2024/2025', 'Ganjil', 'diambil'),
(3, 7, '2024/2025', 'Ganjil', 'diambil'),
(3, 8, '2024/2025', 'Ganjil', 'diambil'),
(4, 7, '2024/2025', 'Ganjil', 'diambil'),
(4, 8, '2024/2025', 'Ganjil', 'diambil'),
(5, 9, '2024/2025', 'Ganjil', 'diambil'),
(5, 10, '2024/2025', 'Ganjil', 'diambil'),
(6, 4, '2024/2025', 'Ganjil', 'diambil'),
(6, 5, '2024/2025', 'Ganjil', 'diambil'),
(7, 7, '2024/2025', 'Ganjil', 'diambil'),
(8, 9, '2024/2025', 'Ganjil', 'diambil');

-- Sample Nilai
INSERT INTO `nilai` (`krs_id`, `tugas`, `uts`, `uas`, `nilai_akhir`, `nilai_huruf`, `nilai_angka`) VALUES
(1, 85.00, 80.00, 88.00, 84.33, 'A-', 3.70),
(2, 78.00, 75.00, 82.00, 78.33, 'B+', 3.30),
(3, 90.00, 88.00, 92.00, 90.00, 'A', 4.00),
(4, 82.00, 79.00, 85.00, 82.00, 'A-', 3.70),
(5, 75.00, 72.00, 78.00, 75.00, 'B+', 3.30),
(6, 88.00, 85.00, 90.00, 87.67, 'A', 4.00),
(7, 80.00, 77.00, 83.00, 80.00, 'A-', 3.70),
(8, 85.00, 82.00, 88.00, 85.00, 'A', 4.00),
(9, 78.00, 75.00, 80.00, 77.67, 'B+', 3.30),
(10, 92.00, 90.00, 95.00, 92.33, 'A', 4.00);

-- Sample Info Kampus
INSERT INTO `info_kampus` (`judul`, `konten`, `kategori`, `status`, `created_by`) VALUES
('Penerimaan Mahasiswa Baru 2025', '<p>Universitas membuka pendaftaran mahasiswa baru untuk tahun akademik 2025/2026. Pendaftaran dibuka mulai tanggal 1 Februari 2025.</p><p>Program studi yang tersedia:</p><ul><li>Teknik Informatika</li><li>Sistem Informasi</li><li>Teknik Komputer</li></ul>', 'pengumuman', 'aktif', 1),
('Seminar Teknologi AI', '<p>Akan diadakan seminar tentang perkembangan teknologi Artificial Intelligence pada tanggal 15 Januari 2025 di Auditorium Utama.</p>', 'event', 'aktif', 1),
('Libur Semester Genap', '<p>Libur semester genap akan dimulai pada tanggal 20 Juni 2025 dan kuliah akan dimulai kembali pada tanggal 15 Agustus 2025.</p>', 'akademik', 'aktif', 1),
('Prestasi Mahasiswa dalam Kompetisi Programming', '<p>Tim mahasiswa Teknik Informatika berhasil meraih juara 2 dalam kompetisi programming tingkat nasional.</p>', 'berita', 'aktif', 1);

-- Sample Prestasi
INSERT INTO `prestasi` (`judul`, `deskripsi`, `tahun`, `kategori`, `tingkat`) VALUES
('Juara 1 Lomba Karya Tulis Ilmiah Nasional', 'Tim mahasiswa berhasil meraih juara 1 dalam lomba karya tulis ilmiah tingkat nasional dengan tema "Inovasi Teknologi untuk Pendidikan"', 2024, 'Akademik', 'Nasional'),
('Juara 2 Kompetisi Programming ACM-ICPC', 'Tim programming universitas berhasil meraih juara 2 dalam kompetisi ACM-ICPC regional', 2024, 'Teknologi', 'Regional'),
('Juara 3 Lomba Desain UI/UX', 'Mahasiswa Sistem Informasi meraih juara 3 dalam lomba desain antarmuka pengguna tingkat nasional', 2024, 'Teknologi', 'Nasional'),
('Penghargaan Kampus Terbaik', 'Universitas meraih penghargaan sebagai kampus terbaik dalam kategori teknologi informasi', 2023, 'Akademik', 'Nasional'),
('Juara 1 Hackathon Smart City', 'Tim mahasiswa multidisiplin berhasil menjadi juara dalam hackathon pengembangan aplikasi smart city', 2023, 'Teknologi', 'Nasional');

-- Sample Log Aktivitas
INSERT INTO `log_aktivitas` (`user_id`, `aktivitas`, `deskripsi`, `ip_address`) VALUES
(1, 'login', 'Admin login to system', '127.0.0.1'),
(2, 'login', 'Dosen login to system', '127.0.0.1'),
(7, 'login', 'Mahasiswa login to system', '127.0.0.1'),
(1, 'create_mahasiswa', 'Created new mahasiswa: Andi Pratama', '127.0.0.1'),
(2, 'input_nilai', 'Input nilai for mata kuliah Algoritma dan Pemrograman', '127.0.0.1');

-- Sample Notifications
INSERT INTO `notifikasi` (`user_id`, `title`, `message`, `type`) VALUES -- Nama tabel di SQL adalah 'notifikasi'
(7, 'Selamat Datang!', 'Selamat datang di sistem akademik. Silakan lengkapi profil Anda.', 'info'),
(8, 'Jadwal Kuliah', 'Jadwal kuliah semester ini sudah tersedia. Silakan cek di menu jadwal.', 'info'),
(9, 'Pembayaran SPP', 'Jangan lupa untuk melakukan pembayaran SPP sebelum tanggal 10.', 'warning'),
(2, 'Input Nilai', 'Silakan input nilai untuk mata kuliah yang Anda ampu.', 'info'),
(3, 'Rapat Dosen', 'Akan ada rapat dosen pada hari Jumat pukul 14.00 di ruang rapat.', 'info');
