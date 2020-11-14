<?php

// makeDBConn()
// Returns: mysqli object connected to DB
function makeDBConn() {
    /*
    BEGIN SQL CONNECTION
    */
    // Provide SQL info
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "library";

    // Create SQL connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// getJSON(String)
// Parameter: ISBN of book being searched
// Returns: json_decode object of book from ISBDdb
// NOTE: ISBNdb returns all notes under obj->book for example
// example: $title = getJSON($ISBN)->book->title
function getJSON($ISBN) {
    $url = "https://api2.isbndb.com/book/".$ISBN;
    $restKey = "44679_2faa2f0fb561a7508b9775e81b2f9c41";

    $headers = array(
        "Content-Type: application/json",
        "Authorization: " . $restKey
    );

    $rest = curl_init();
    curl_setopt($rest,CURLOPT_URL,$url);
    curl_setopt($rest,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($rest,CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($rest);
    $result = json_decode($response);

    //echo $response;
    //print_r($response);
    //echo("<pre>".print_r($response,true)."</pre>");
    curl_close($rest);
    return $result->book;
}

// addBookToDB(Book)
// Parameter: Book object from getJSON()
// Returns: Book info written to DB
function addBookToDB($book) {
    $conn = makeDBConn();

    $ISBN = $book->isbn13;
    $title = $book->title_long;
    $author = null;
    if (isset($book->authors)) {$author = $book->authors[0];} // Store only the first author of the book if multiple
    $genre = null;
    if (isset($book->subjects)) {$genre = $book->subjects[0];} // Store only the first genre of the book if multiple
    $publisher = $book->publisher;


    $amt_query = "SELECT amt_total, amt_available FROM book WHERE ISBN = $ISBN"; // get amts from database
    $amt_result = $conn->query($amt_query);
    $amt_arr = $amt_result->fetch_assoc(); // gets list of [amt_total, amt_available], null if non-existent
    if (!is_null($amt_arr) ) { // Checks if $amt_arr contains values rather than null (returns values if exists)
        $amt_total = $amt_arr["amt_total"] + 1;
        $amt_available = $amt_arr["amt_available"] + 1;
        $sql = "UPDATE book SET amt_total = $amt_total, amt_available = $amt_available WHERE ISBN = $ISBN";
    }
    else {$sql = "INSERT INTO book VALUES ('$ISBN', '$title', '$author', '$genre', '$publisher', 1, 1)";}

    $result = $conn->query($sql);

    if($result) //if the insert into database was successful
    {
        echo "Book inserted successfully";
    }
    return $result;

}

// addStudentToDB(ID_number, name)
// Parameter: Takes in the ID_number and student name
// Returns: Adds student to DB
function addStudentToDB($ID_number, $name){
    $conn = makeDBConn();

    $sql = "INSERT INTO student VALUES ('$ID_number', '$name')";
    $result = $conn->query($sql);

    if($result) // if the insert into the database was successful
    {
        echo "Student inserted successfully";
    }
    return $result;
}

// checkOut(ISBN, ID_number)
// Parameter: Takes in the ID_number and ISBN of book to be checked out
// Returns: Adds book and ID_number to checked_out in database
//          Reduces the number of amt_available in book by 1

function checkOut($ISBN, $ID_number) {
    $conn = makeDBConn();

    $sql1 = "INSERT INTO checked_out VALUES ('$ISBN', '$ID_number')";
    $result1 = $conn->query($sql1);

    $sql2 = "UPDATE book SET amt_available = amt_available - 1 where amt_available > 0 AND ISBN = $ISBN";
    $result2 = $conn->query($sql2);
}

// checkOut(ISBN, ID_number)
// Parameter: Takes in the ID_number and ISBN of book to be checked out
// Returns: Removes row from checked_out in database
//          Increments the number of amt_available in book by 1
function checkIn($ISBN, $ID_number) {
    $conn = makeDBConn();

    $sql1 = "DELETE FROM checked_out WHERE ISBN = $ISBN AND ID_number = $ID_number";
    $result1 = $conn->query($sql1);

    $sql2 = "UPDATE book SET amt_available = amt_available + 1 where amt_available < amt_total AND ISBN = $ISBN";
    $result2 = $conn->query($sql2);
}
?>