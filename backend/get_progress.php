<?php
session_start();
header('Content-Type: application/json');

$progress = file_exists('upload_progress.txt') ? file_get_contents('upload_progress.txt') : 0;
echo json_encode(["progress" => (int)$progress]);
?>
