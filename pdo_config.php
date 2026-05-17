<?php

$host = "localhost";
$dbname = "yic_todo_db";
$user = "root";
$pass = "";
$port = 3307;

try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Connection failed: " . $e->getMessage());

}
?>