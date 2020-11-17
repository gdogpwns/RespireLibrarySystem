<?php
include 'Database.php';
ob_start();
session_start();

$username = $_POST['username'];
$password = $_POST['password'];

$_SESSION['username'] = $username;
$_SESSION['password'] = $password;

makeDBConn();
header("Refresh: 2; URL=http://localhost/Home.html");