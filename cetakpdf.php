<?php
require('fpdf.php'); //
require 'include/conn.php'; // 
ob_start();
require "preferensi.php";

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(190, 10, 'Laporan SAW - CV. Anugerah Daya Sejahteratama', 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);

// ==============================
// Mengambil Data Alternatif
// ==============================
$pdf->Cell(190, 10, 'Data Alternatif', 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(50, 7, 'ID Alternatif', 1);
$pdf->Cell(140, 7, 'Nama Alternatif', 1);
$pdf->Ln();

$alternatives = [];
$query = $db->query("SELECT * FROM saw_alternatives");
while ($row = $query->fetch_assoc()) {
    $alternatives[$row['id_alternative']] = $row['name'];
    $pdf->Cell(50, 7, $row['id_alternative'], 1);
    $pdf->Cell(140, 7, $row['name'], 1);
    $pdf->Ln();
}
$pdf->Ln(10);

// ==============================
// Mengambil Data Kriteria
// ==============================
$pdf->Cell(190, 10, 'Data Kriteria', 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(40, 7, 'ID Kriteria', 1);
$pdf->Cell(70, 7, 'Nama Kriteria', 1);
$pdf->Cell(40, 7, 'Bobot', 1);
$pdf->Cell(40, 7, 'Atribut', 1);
$pdf->Ln();

$criterias = [];
$query = $db->query("SELECT * FROM saw_criterias");
while ($row = $query->fetch_assoc()) {
    $criterias[$row['id_criteria']] = [
        'criteria' => $row['criteria'],
        'weight' => $row['weight'],
        'attribute' => $row['attribute']
    ];
    $pdf->Cell(40, 7, $row['id_criteria'], 1);
    $pdf->Cell(70, 7, $row['criteria'], 1);
    $pdf->Cell(40, 7, $row['weight'], 1);
    $pdf->Cell(40, 7, $row['attribute'], 1);
    $pdf->Ln();
}
$pdf->Ln(10);

// ==============================
// Mengambil Data Evaluasi
// ==============================
$evaluations = [];
$query = $db->query("SELECT * FROM saw_evaluations");
while ($row = $query->fetch_assoc()) {
    $evaluations[$row['id_alternative']][$row['id_criteria']] = $row['value'];
}

// ==============================
// Normalisasi Matriks Keputusan
// ==============================
$normalized = [];
$max_value = [];
$min_value = [];

// Cari nilai max & min untuk setiap kriteria
foreach ($criterias as $id_criteria => $c) {
    $value_list = array_column($evaluations, $id_criteria);

    if (!empty($value_list)) {
        $max_value[$id_criteria] = max($value_list);
        $min_value[$id_criteria] = min($value_list);
    } else {
        die("Error: Tidak ada nilai yang ditemukan untuk kriteria $id_criteria");
    }
}

// Normalisasi nilai
foreach ($evaluations as $id_alternative => $value) {
    foreach ($criterias as $id_criteria => $c) {
        if (isset($value[$id_criteria])) {
            if ($c['attribute'] == 'Benefit') {
                $normalized[$id_alternative][$id_criteria] = ($max_value[$id_criteria] > 0) 
                    ? ($value[$id_criteria] / $max_value[$id_criteria]) 
                    : 0;
            } else {
                $normalized[$id_alternative][$id_criteria] = ($value[$id_criteria] > 0) 
                    ? ($min_value[$id_criteria] / $value[$id_criteria]) 
                    : 0;
            }
        } else {
            $normalized[$id_alternative][$id_criteria] = 0;
        }
    }
}

// ==============================
// Menampilkan Normalisasi 
// ==============================
ksort($normalized);
$pdf->Cell(190, 8, 'Normalisasi Matriks Keputusan', 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);

// Header Tabel
$pdf->Cell(20, 7, 'ID Alternatif', 1, 0, 'C');
$pdf->Cell(50, 7, 'Nama Alternatif', 1, 0, 'C');
foreach ($criterias as $id_criteria => $c) {
    $pdf->Cell(30, 7, $c['criteria'], 1, 0, 'C');
}
$pdf->Ln();

// Isi Tabel
foreach ($normalized as $id_alternative => $values) {
    $pdf->Cell(20, 7, $id_alternative, 1, 0, 'C');
    $pdf->Cell(50, 7, $alternatives[$id_alternative], 1, 0, 'L');
    foreach ($values as $value) {
        $pdf->Cell(30, 7, number_format($value, 4), 1, 0, 'C');
    }
    $pdf->Ln();
}
$pdf->Ln(10);

$pdf->Cell(190, 10, 'Perangkingan Alternatif', 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 7, 'Rank', 1);
$pdf->Cell(50, 7, 'ID Alternatif', 1);
$pdf->Cell(80, 7, 'Nama Alternatif', 1);
$pdf->Cell(40, 7, 'Hasil', 1);
$pdf->Ln();

// Menghitung Nilai Preferensi (P)
$P = array();
$m = count($W);
foreach ($R as $i => $r) {
    for ($j = 0; $j < $m; $j++) {
        $P[$i] = (isset($P[$i]) ? $P[$i] : 0) + $r[$j] * $W[$j];
    }
}

arsort($P);

$rank = 1;
foreach ($P as $id_alternative => $score) {
    $pdf->Cell(20, 7, $rank++, 1);
    $pdf->Cell(50, 7, $id_alternative, 1);
    $pdf->Cell(80, 7, $alternatives[$id_alternative], 1);
    $pdf->Cell(40, 7, number_format($score, 4), 1);
    $pdf->Ln();
}

ob_end_clean(); 
$pdf->Output('D', 'Laporan_SAW_Normalisasi.pdf');
?>
