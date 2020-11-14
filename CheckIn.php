<?php
include "Database.php";

$ISBN = $_POST["ISBN"];
$ID_number = $_POST["ID_number"];


checkIn($ISBN, $ID_number);