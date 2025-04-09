<?php
require "include/conn.php";

if (isset($_POST['id_alternative'], $_POST['id_criteria'], $_POST['value'])) {
    $id_alternative = $_POST['id_alternative'];
    $id_criteria = $_POST['id_criteria'];
    $value = $_POST['value'];

    // Cek apakah data sudah ada dalam tabel
    $stmt = $db->prepare("SELECT * FROM saw_evaluations WHERE id_alternative = ? AND id_criteria = ?");
    $stmt->bind_param("ii", $id_alternative, $id_criteria);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Jika data sudah ada, lakukan UPDATE
        $stmt = $db->prepare("UPDATE saw_evaluations SET value = ? WHERE id_alternative = ? AND id_criteria = ?");
        $stmt->bind_param("dii", $value, $id_alternative, $id_criteria);
    } else {
        // Jika belum ada, lakukan INSERT
        $stmt = $db->prepare("INSERT INTO saw_evaluations (id_alternative, id_criteria, value) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $id_alternative, $id_criteria, $value);
    }

    if ($stmt->execute()) {
        header("Location: matrik.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: Data tidak lengkap!";
}

$db->close();
?>
