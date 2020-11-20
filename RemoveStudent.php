<?php
include "Database.php";

$ID_number = $_POST["ID_number"];
$result = removeStudent($ID_number);

header("Refresh: 2; URL=http://localhost/RemoveStudent.html");
die();
?>