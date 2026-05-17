<?php
session_start();
include 'pdo_config.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM tasks
    WHERE user_id = ?
    AND status = 'pending'
");

$stmt->execute([$user_id]);

echo $stmt->fetchColumn();
?>