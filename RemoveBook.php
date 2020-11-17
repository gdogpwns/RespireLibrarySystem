<?php
include "Database.php";

$ISBN = $_POST["ISBN"];
$book = getJSON($ISBN);
$result = addBookToDB($book);

header("Refresh: 2; URL=http://localhost/RemoveBook.html");
die();
?>