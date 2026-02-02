<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "280902", "dbomahne_gedhang");

if (isset($_POST['type']) && $_POST['type'] == 'feedback') {
    // Simpan Feedback
    $nama = $_POST['name'];
    $pesan = $_POST['message'];
    $sql = "INSERT INTO reviews (name, message) VALUES ('$nama', '$pesan')";
    if($conn->query($sql)) echo json_encode(["status" => "success"]);
} else {
    // Simpan Pesanan
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $detail = $_POST['detail'];
    $total = $_POST['total'];
    $sql = "INSERT INTO pesanan (nama_pembeli, alamat, detail_pesanan, total_harga) VALUES ('$nama', '$alamat', '$detail', '$total')";
    if($conn->query($sql)) echo json_encode(["status" => "success"]);
}
?>