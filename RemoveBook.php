<?php
include "Database.php";

$ISBN = $_POST["ISBN"];
$result = removeBook($ISBN);

header("Refresh: 2; URL=http://localhost/RemoveBook.html");
die();
?>