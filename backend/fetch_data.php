<?php
session_start();
include 'db.php';

$query = "SELECT * FROM product_sales";
$result = $conn->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
