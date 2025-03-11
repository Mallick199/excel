<?php
session_start();
include 'db.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $fileName = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExt = ['xls', 'xlsx', 'csv'];

    if (!in_array($fileExt, $allowedExt)) {
        echo json_encode(["status" => "error", "message" => "Invalid file format!"]);
        exit();
    }

    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $filePath = 'uploads/' . uniqid() . '-' . $fileName;
    
    if (!move_uploaded_file($fileTmp, $filePath)) {
        echo json_encode(["status" => "error", "message" => "Failed to move uploaded file!"]);
        exit();
    }

    try {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        $totalRows = count($data) - 1; 
        $_SESSION['upload_progress'] = 0; 

        $stmt = $conn->prepare("INSERT INTO product_sales (segment, country, product, discount_band, units_sold) VALUES (?, ?, ?, ?, ?)");

        foreach ($data as $index => $row) {
            if ($index == 0) continue; 

            list($segment, $country, $product, $discount_band, $units_sold) = $row;
            $stmt->bind_param("sssss", $segment, $country, $product, $discount_band, $units_sold);
            $stmt->execute();

          
            $_SESSION['upload_progress'] = round(($index / $totalRows) * 100);

         
            file_put_contents('upload_progress.txt', $_SESSION['upload_progress']);
        }

        echo json_encode(["status" => "success", "message" => "File uploaded & data inserted successfully!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error processing file: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No file uploaded."]);
}
?>
