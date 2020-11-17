<?php
include "Database.php";

// got help from https://www.siteground.com/tutorials/php-mysql/display-table-data/
$ID_number = $_POST['ID_number'];

$result = getCheckedOut($ID_number);
echo '<div class="header">
        <h1>See Checked Out</h1>
    </div>';

echo '<a href="http://localhost/home.html">Go Home</a>
      <br>';

echo '<table border="1" cellspacing="2" cellpadding="2">
    <tr>
        <td> <font face = "Arial">ISBN</font></td>
        <td> <font face = "Arial">Title</font></td>
        <td> <font face = "Arial">Author</font></td>
        <td> <font face = "Arial">Due date</font></td>
    </tr>';

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ISBN = $row["ISBN"];
        $title = $row["title"];
        $author = $row["author"];
        $due_date = $row["due_date"];

        echo
            '<tr>
            <td>'.$ISBN.'</td>
            <td>'.$title.'</td>
            <td>'.$author.'</td>
            <td>'.$due_date.'</td>
        </tr>';
    }
    $result->free();
}