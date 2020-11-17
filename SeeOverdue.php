<?php
include "Database.php";

// got help from https://www.siteground.com/tutorials/php-mysql/display-table-data/

$result = getOverdue();

echo '<a href="http://localhost/home.html">Go Home</a>
      <br>';

echo '<table border="0" cellspacing="2" cellpadding="2">
    <tr>
        <td> <font face = "Arial">Student name</font></td>
        <td> <font face = "Arial">ID number</font></td>
        <td> <font face = "Arial">ISBN</font></td>
        <td> <font face = "Arial">Due date</font></td>
    </tr>';

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $name = $row["name"];
        $ID_number = $row["ID_number"];
        $ISBN = $row["ISBN"];
        $due_date = $row["due_date"];

        echo
        '<tr>
            <td>'.$name.'</td>
            <td>'.$ID_number.'</td>
            <td>'.$ISBN.'</td>
            <td>'.$due_date.'</td>
        </tr>';
    }
    $result->free();
}