<?php

// makeDBConn()
// Returns: mysqli object connected to DB
function makeDBConn() {
    /*
    BEGIN SQL CONNECTION
    */
    // Provide SQL info
    /*
    $servername = "localhost";
    $username = "librarian";
    $password = "Haiti!";
    $dbname = "library";
    */
    session_start();

    $servername = "localhost";
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];
    $dbname = "library";

    // Create SQL connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    else{
        echo "$username logged in<br>";
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
    $author = NULL;
    if (isset($book->authors)) {$author = $book->authors[0];} // Store only the first author of the book if multiple
    $genre = NULL;
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

    // Can't figure out how to put NULL variable into SQL query because it likes to convert it to
    // an empty string. This is my solution. Not proud of it, but PHP eludes me.
    else {
        if (is_null($genre)) { // there HAS to be a more elegant way to do this
            $sql = "INSERT INTO book VALUES ('$ISBN', '$title', '$author', NULL, '$publisher', 1, 1)";}
        else {
            $sql = "INSERT INTO book VALUES ('$ISBN', '$title', '$author', '$genre', '$publisher', 1, 1)";}
    }

    $result = $conn->query($sql);

    if($result) //if the insert into database was successful
    {
        echo "Book inserted successfully";
    }
    else{
        echo "Book insert FAILED";
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
// Returns: Adds book, ID_number, and 3 weeks from current date to checked_out in database
//          Reduces the number of amt_available in book by 1
function checkOut($ISBN, $ID_number) {
    $conn = makeDBConn();
    $ISBN = getJSON($ISBN)->isbn13; // converts ISBN10 to ISBN13 if need be
    // sql1 adds ISBN, ID_number, and three weeks from current date to checked_out
    $sql1 = "INSERT INTO checked_out VALUES ('$ISBN', '$ID_number', DATE_ADD(CURRENT_DATE, INTERVAL 3 WEEK))";
    $result1 = $conn->query($sql1);

    if($result1) // if the insert into the database was successful
    {
        echo "due_date updated in checked_out<br>";
    }

    $sql2 = "UPDATE book SET amt_available = amt_available - 1 where amt_available > 0 AND ISBN = $ISBN";
    $result2 = $conn->query($sql2);

    if($result2) // if the insert into the database was successful
    {
        echo "Book checked out successfully<br>";
    }
}

// checkOut(ISBN, ID_number)
// Parameter: Takes in the ID_number and ISBN of book to be checked out
// Returns: Removes row from checked_out in database
//          Increments the number of amt_available in book by 1
function checkIn($ISBN, $ID_number) {
    $conn = makeDBConn();
    $ISBN = getJSON($ISBN)->isbn13; // converts ISBN10 to ISBN13 if need be

    $sql1 = "DELETE FROM checked_out WHERE ISBN = $ISBN AND ID_number = $ID_number";
    $result1 = $conn->query($sql1);

    if($result1) {
        echo "Book removed from checked_out<br>";
    }

    $sql2 = "UPDATE book SET amt_available = amt_available + 1 where amt_available < amt_total AND ISBN = $ISBN";
    $result2 = $conn->query($sql2);

    if($result2) {
        echo "amt_available and amt_total restored<br>";
    }

}

// getOverdue()
// Returns: mysqli object with all ISBN, ID_number, and due_date from checked_out
// and name from joined student table if CURRENT_DATE > due_date
function getOverdue() {
    $conn = makeDBConn();

    $sql = "SELECT * 
            FROM checked_out 
            LEFT JOIN student s on s.ID_number = checked_out.ID_number
            WHERE CURRENT_DATE > due_date";

    return $conn->query($sql);
}

// getCheckedOut($ID_number)
// Parameter: Takes in the ID_number of the student whose records you want
// Returns: mysqli object with all ISBN and due_date from checked_out
function getCheckedOut($ID_number) {
    $conn = makeDBConn();

    $sql = "SELECT checked_out.ISBN, title, author, due_date
            FROM checked_out 
            LEFT JOIN book b on b.ISBN = checked_out.ISBN
            WHERE checked_out.ID_number = $ID_number";

    return $conn->query($sql);
}

// removeBook($ISBN)
// Parameter: Takes in the ISBN of book to be removed from DB
// Returns: nothing, but removes ALL instances of book from DB in both book and checked_out
// explanation for this in report
function removeBook($ISBN) {
    $conn = makeDBConn();
    $ISBN = getJSON($ISBN)->isbn13; // converts ISBN10 to ISBN13 if need be

    $sql1 = "DELETE FROM checked_out WHERE ISBN = $ISBN";
    $result1 = $conn->query($sql1);

    if($result1) {
        echo "Book successfully removed from checked_out <br>";
    }

    $sql2 = "DELETE FROM book WHERE ISBN = $ISBN";
    $result2 = $conn->query($sql2);

    if($result2) {
        echo "Book successfully removed from book <br>";
    }

}

// removeStudent($ID_number)
// Parameter: Takes in the ID_number of the student to be removed from the DB
// Returns: nothing, but removes ALL instances of student from DB in both student and checked_out
function removeStudent($ID_number) {
    $conn = makeDBConn();

    $sql1 = "DELETE FROM checked_out WHERE ID_number = $ID_number";
    $result1 = $conn->query($sql1);

    if($result1) {
        echo "Student successfully removed from checked_out <br>";
    }

    $sql2 = "DELETE FROM student WHERE ID_number = $ID_number";
    $result2 = $conn->query($sql2);

    if($result2) {
        echo "Student successfully removed from student <br>";
    }
}
?>