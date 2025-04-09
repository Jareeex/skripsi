<?php
error_reporting(E_ALL); // Menampilkan semua error
ini_set('display_errors', 1);

// Konfigurasi database
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'db_dss';

// Koneksi ke database
$db = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Cek koneksi dan tampilkan pesan error jika gagal
if ($db->connect_error) {
    die('Koneksi Gagal: (' . $db->connect_errno . ') ' . $db->connect_error);
} else {
    echo "Koneksi Berhasil!";
}
?>
