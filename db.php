<?php
$conn = new mysqli("localhost", "root", "", "foodfusion_db");

if ($conn->connect_error) {
    die("Database connection failed");
}
?>