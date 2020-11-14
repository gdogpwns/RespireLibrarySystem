<?php
include "Database.php";

$ISBN = $_POST["ISBN"];
$book = getJSON($ISBN);
$result = addBookToDB($book);
echo "Book $book->title_long added to database!"

?>

