<?php
include "Database.php";

$ID_number = $_POST["ID_number"];
$name = $_POST["name"];

addStudentToDB($ID_number, $name);

header("Refresh: 2; URL=http://localhost/AddStudent.html");
die();
